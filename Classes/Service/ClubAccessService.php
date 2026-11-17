<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

/**
 * Checks, if the current user is allowed to manage a club in frontend.
 * Hidden clubs are respected, as new and updated clubs are hidden until
 * they get activated.
 *
 * The edit and activate links in the notification mails are signed with a
 * "moderationToken". It authorizes the administrator, who is never logged
 * in as the frontend user who owns the club. As the token signs the
 * "tstamp" of the club, too, only the links of the latest mail are valid.
 *
 * The club form based on EXT:form is not an Extbase action. Its finisher and
 * hooks read the club to edit and the moderation token from the query
 * parameters of the Management plugin, see isFormAccessAllowed().
 */
readonly class ClubAccessService
{
    private const TABLE_CLUB = 'tx_clubdirectory_domain_model_club';

    private const TABLE_CLUB_USER_MM = 'tx_clubdirectory_club_user_mm';

    private const PLUGIN_NAMESPACE = 'tx_clubdirectory_management';

    private const MODERATION_TOKEN_SECRET = 'clubdirectory/club-moderation-link';

    private const MODERATION_TOKEN_ARGUMENT = 'moderationToken';

    public function __construct(
        private ConnectionPool $connectionPool,
        private Context $context,
        private HashService $hashService,
    ) {}

    /**
     * Extbase accepts an object identity as scalar value or as "__identity" of an array
     */
    public function getIdentity(mixed $argument): int
    {
        if (is_array($argument)) {
            $argument = $argument['__identity'] ?? 0;
        }

        return is_numeric($argument) ? (int)$argument : 0;
    }

    /**
     * The token signs the club UID and the current "tstamp" of the club
     * record. So a token gets invalid as soon as the club was changed again.
     * Generate the token after the club was persisted.
     */
    public function generateModerationToken(int $clubUid): string
    {
        $payload = $this->getModerationTokenPayload($clubUid);

        return $payload === '' ? '' : $this->hashService->hmac($payload, self::MODERATION_TOKEN_SECRET);
    }

    /**
     * Returns the "moderationToken" argument of the request, if it was
     * generated for the given club. Else an empty string is returned.
     */
    public function getValidModerationToken(RequestInterface $request, int $clubUid): string
    {
        $moderationToken = $request->hasArgument(self::MODERATION_TOKEN_ARGUMENT)
            ? $request->getArgument(self::MODERATION_TOKEN_ARGUMENT)
            : '';

        return is_string($moderationToken) && $this->isValidModerationToken($clubUid, $moderationToken)
            ? $moderationToken
            : '';
    }

    /**
     * Returns the UID of the club to edit in the club form. 0 means: create
     * a new club. It is read from the query only, so a manipulated POST body
     * can not change the club, which was checked by isFormAccessAllowed().
     */
    public function getClubUidFromQuery(ServerRequestInterface $request): int
    {
        $clubUid = $request->getQueryParams()[self::PLUGIN_NAMESPACE]['club'] ?? 0;

        return MathUtility::canBeInterpretedAsInteger($clubUid) ? max(0, (int)$clubUid) : 0;
    }

    public function getModerationTokenFromQuery(ServerRequestInterface $request): string
    {
        $moderationToken = $request->getQueryParams()[self::PLUGIN_NAMESPACE][self::MODERATION_TOKEN_ARGUMENT] ?? '';

        return is_string($moderationToken) ? $moderationToken : '';
    }

    /**
     * Same rules as RestrictAccessEventListener applies to the Management
     * plugin: New clubs can be created by members of "settings.userGroup"
     * only. An existing club can be edited with a valid moderation token, by
     * a backend administrator or by an assigned member of the user group.
     */
    public function isFormAccessAllowed(ServerRequestInterface $request): bool
    {
        $clubUid = $this->getClubUidFromQuery($request);
        $isMember = $this->isMemberOfUserGroup($this->getUserGroup($request));
        if ($clubUid === 0) {
            return $isMember;
        }

        if ($this->isValidModerationToken($clubUid, $this->getModerationTokenFromQuery($request))) {
            return true;
        }

        return $this->clubExists($clubUid)
            && ($isMember || $this->isBackendAdmin())
            && $this->canManageClub($clubUid);
    }

    /**
     * Only frontend users assigned to the club (fe_users relation) and
     * backend administrators are allowed to manage a club.
     */
    public function canManageClub(int $clubUid): bool
    {
        if ($clubUid <= 0) {
            return false;
        }

        if ($this->isBackendAdmin()) {
            return true;
        }

        $frontendUserUid = (int)$this->context->getPropertyFromAspect('frontend.user', 'id', 0);

        return $frontendUserUid > 0 && $this->isClubAssignedToFrontendUser($clubUid, $frontendUserUid);
    }

    /**
     * Returns true, if the club exists and is hidden. Deleted clubs are
     * never returned.
     */
    public function isClubHidden(int $clubUid): bool
    {
        if ($clubUid <= 0) {
            return false;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_CLUB);
        $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());

        $hidden = $queryBuilder
            ->select('hidden')
            ->from(self::TABLE_CLUB)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($clubUid, Connection::PARAM_INT)),
            )
            ->executeQuery()
            ->fetchOne();

        return $hidden !== false && (int)$hidden === 1;
    }

    public function isBackendAdmin(): bool
    {
        return $this->context->getPropertyFromAspect('backend.user', 'isAdmin', false) === true;
    }

    /**
     * Same check as <f:security.ifHasRole role="{settings.userGroup}"> in the
     * templates, but a logged-in frontend user is required in any case. So
     * neither "0" nor "-1" (no login) grant access to anonymous visitors.
     */
    public function isMemberOfUserGroup(int $userGroup): bool
    {
        if ($userGroup === 0 || $this->context->getPropertyFromAspect('frontend.user', 'isLoggedIn', false) !== true) {
            return false;
        }

        $groupIds = $this->context->getPropertyFromAspect('frontend.user', 'groupIds', []);

        return is_array($groupIds) && in_array($userGroup, $groupIds, true);
    }

    private function isValidModerationToken(int $clubUid, string $moderationToken): bool
    {
        if ($clubUid <= 0 || $moderationToken === '') {
            return false;
        }

        $payload = $this->getModerationTokenPayload($clubUid);

        return $payload !== '' && $this->hashService->validateHmac($payload, self::MODERATION_TOKEN_SECRET, $moderationToken);
    }

    private function clubExists(int $clubUid): bool
    {
        return $this->getModerationTokenPayload($clubUid) !== '';
    }

    /**
     * Read like Extbase does: plugin.tx_clubdirectory_management overrides
     * plugin.tx_clubdirectory. Without TypoScript no user group is
     * configured, which denies access.
     */
    private function getUserGroup(ServerRequestInterface $request): int
    {
        $frontendTypoScript = $request->getAttribute('frontend.typoscript');
        try {
            $pluginSetup = $frontendTypoScript instanceof FrontendTypoScript
                ? $frontendTypoScript->getSetupArray()['plugin.'] ?? []
                : [];
        } catch (\RuntimeException) {
            $pluginSetup = [];
        }

        return (int)(
            $pluginSetup[self::PLUGIN_NAMESPACE . '.']['settings.']['userGroup']
            ?? $pluginSetup['tx_clubdirectory.']['settings.']['userGroup']
            ?? 0
        );
    }

    /**
     * Returns "<club UID>:<tstamp>" or an empty string, if the club does not exist
     */
    private function getModerationTokenPayload(int $clubUid): string
    {
        if ($clubUid <= 0) {
            return '';
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_CLUB);
        $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());

        $timestamp = $queryBuilder
            ->select('tstamp')
            ->from(self::TABLE_CLUB)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($clubUid, Connection::PARAM_INT)),
            )
            ->executeQuery()
            ->fetchOne();

        return $timestamp === false ? '' : $clubUid . ':' . (int)$timestamp;
    }

    private function isClubAssignedToFrontendUser(int $clubUid, int $frontendUserUid): bool
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_CLUB_USER_MM);
        $queryBuilder->getRestrictions()->removeAll();

        $numberOfRelations = $queryBuilder
            ->count('mm.uid_local')
            ->from(self::TABLE_CLUB_USER_MM, 'mm')
            ->innerJoin('mm', self::TABLE_CLUB, 'club', $queryBuilder->expr()->eq('club.uid', 'mm.uid_local'))
            ->where(
                $queryBuilder->expr()->eq('mm.uid_local', $queryBuilder->createNamedParameter($clubUid, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('mm.uid_foreign', $queryBuilder->createNamedParameter($frontendUserUid, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('club.deleted', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)),
            )
            ->executeQuery()
            ->fetchOne();

        return (int)$numberOfRelations > 0;
    }
}
