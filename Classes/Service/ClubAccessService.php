<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Service;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;

/**
 * Checks, if the current user is allowed to manage a club in frontend.
 * Hidden clubs are respected, as new and updated clubs are hidden until
 * they get activated.
 */
readonly class ClubAccessService
{
    private const TABLE_CLUB = 'tx_clubdirectory_domain_model_club';

    private const TABLE_ADDRESS = 'tx_clubdirectory_domain_model_address';

    private const TABLE_CLUB_USER_MM = 'tx_clubdirectory_club_user_mm';

    public function __construct(
        private ConnectionPool $connectionPool,
        private Context $context,
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
     * Only frontend users assigned to the club (fe_users relation) and
     * backend administrators are allowed to manage a club.
     */
    public function canManageClub(int $clubUid): bool
    {
        if ($clubUid <= 0) {
            return false;
        }

        if ($this->context->getPropertyFromAspect('backend.user', 'isAdmin', false) === true) {
            return true;
        }

        $frontendUserUid = (int)$this->context->getPropertyFromAspect('frontend.user', 'id', 0);

        return $frontendUserUid > 0 && $this->isClubAssignedToFrontendUser($clubUid, $frontendUserUid);
    }

    /**
     * Prevent property mapping of addresses and POI records of foreign clubs
     * by submitting a manipulated "__identity" within the club argument.
     *
     * @param array<string|int, mixed> $clubArgument
     */
    public function hasOnlyRecordsOfClub(array $clubArgument, int $clubUid): bool
    {
        $addresses = $clubArgument['addresses'] ?? [];
        if (!is_array($addresses)) {
            return $this->getIdentity($addresses) === 0;
        }

        foreach ($addresses as $address) {
            if (!$this->isAddressOfClub($address, $clubUid)) {
                return false;
            }
        }

        return true;
    }

    private function isAddressOfClub(mixed $address, int $clubUid): bool
    {
        $addressUid = $this->getIdentity($address);
        $poiCollectionUid = $this->getIdentity(is_array($address) ? ($address['txMaps2Uid'] ?? 0) : 0);
        if ($addressUid === 0) {
            return $poiCollectionUid === 0;
        }

        $addressRecord = $this->getAddressRecord($addressUid);

        return $clubUid > 0
            && $addressRecord !== []
            && (int)$addressRecord['club'] === $clubUid
            && ($poiCollectionUid === 0 || (int)$addressRecord['tx_maps2_uid'] === $poiCollectionUid);
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

    /**
     * @return array<string, mixed>
     */
    private function getAddressRecord(int $addressUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_ADDRESS);
        $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());

        $addressRecord = $queryBuilder
            ->select('uid', 'club', 'tx_maps2_uid')
            ->from(self::TABLE_ADDRESS)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($addressUid, Connection::PARAM_INT)),
            )
            ->executeQuery()
            ->fetchAssociative();

        return is_array($addressRecord) ? $addressRecord : [];
    }
}
