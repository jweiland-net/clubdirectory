<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Domain\Factory;

use JWeiland\Clubdirectory\Configuration\ExtConf;
use JWeiland\Clubdirectory\Service\ClubAccessService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Form\Domain\Exception\RenderingException;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;

/**
 * Builds the club form based on EXT:form. Before building, the
 * configuration is modified:
 *
 * - The club to edit and the moderation token of the query are kept for
 *   the form action
 * - Sender and recipients of the EmailToReceiver finisher are taken from
 *   the extension settings, if they are not configured in the form
 */
class ArrayFormFactory extends \TYPO3\CMS\Form\Domain\Factory\ArrayFormFactory
{
    private const PLUGIN_NAMESPACE = 'tx_clubdirectory_management';

    public function __construct(
        protected readonly ExtConf $extConf,
        protected readonly ClubAccessService $clubAccessService,
    ) {}

    /**
     * @param array<string, mixed> $configuration
     * @throws RenderingException
     */
    public function build(
        array $configuration,
        ?string $prototypeName = null,
        ?ServerRequestInterface $request = null,
    ): FormDefinition {
        if ($request instanceof ServerRequestInterface) {
            $configuration = $this->addClubToFormAction($configuration, $request);
        }

        $configuration['finishers'] = array_map(
            $this->addEmailAddressesToFinisher(...),
            is_array($configuration['finishers'] ?? null) ? $configuration['finishers'] : [],
        );

        return parent::build($configuration, $prototypeName, $request);
    }

    /**
     * The club to edit and the moderation token are part of the URI of the
     * form. Keep them for the form action. These are the values, which are
     * checked and stored.
     *
     * @param array<string, mixed> $configuration
     * @return array<string, mixed>
     */
    protected function addClubToFormAction(array $configuration, ServerRequestInterface $request): array
    {
        $clubUid = $this->clubAccessService->getClubUidFromQuery($request);
        if ($clubUid === 0) {
            return $configuration;
        }

        $configuration['renderingOptions']['additionalParams'][self::PLUGIN_NAMESPACE]['club'] = $clubUid;
        $moderationToken = $this->clubAccessService->getModerationTokenFromQuery($request);
        if ($moderationToken !== '') {
            $configuration['renderingOptions']['additionalParams'][self::PLUGIN_NAMESPACE]['moderationToken'] = $moderationToken;
        }

        return $configuration;
    }

    protected function addEmailAddressesToFinisher(mixed $finisher): mixed
    {
        if (!is_array($finisher) || ($finisher['identifier'] ?? '') !== 'EmailToReceiver') {
            return $finisher;
        }

        try {
            $finisher['options']['senderName'] ??= $this->extConf->getEmailFromName();
            $finisher['options']['senderAddress'] ??= $this->extConf->getEmailFromAddress();
            $finisher['options']['recipients'] ??= [
                $this->extConf->getEmailToAddress() => $this->extConf->getEmailToName(),
            ];
        } catch (\InvalidArgumentException) {
            // Missing mail settings must not prevent rendering the form. The
            // EmailFinisher reports missing addresses on submit.
        }

        return $finisher;
    }
}
