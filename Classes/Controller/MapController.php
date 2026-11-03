<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Controller;

use JWeiland\Clubdirectory\Controller\Traits\ClubListRedirectTrait;
use JWeiland\Clubdirectory\Controller\Traits\ControllerInjectionTrait;
use JWeiland\Clubdirectory\Controller\Traits\InitializeControllerTrait;
use JWeiland\Clubdirectory\Controller\Traits\PreProcessControllerActionTrait;
use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Service\ClubAccessService;
use JWeiland\Clubdirectory\Service\ClubCacheService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\Argument;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

/**
 * Controller to confirm the map positions of a new club. Changes of
 * existing clubs incl. their map positions are done in ManagementController.
 */
class MapController extends ActionController
{
    use ClubListRedirectTrait;
    use ControllerInjectionTrait;
    use InitializeControllerTrait;
    use PreProcessControllerActionTrait;

    public function __construct(
        protected ClubAccessService $clubAccessService,
        protected ClubCacheService $clubCacheService,
        protected PersistenceManagerInterface $persistenceManager,
    ) {}

    public function initializeNewAction(): void
    {
        $this->preProcessControllerAction();
    }

    /**
     * As club was already validated in ClubController create/update there can't be any errors. So ignore validation.
     */
    #[Extbase\IgnoreValidation(['value' => 'club'])]
    public function newAction(Club $club): ResponseInterface
    {
        $this->view->assign('club', $club);

        return $this->htmlResponse();
    }

    public function initializeCreateAction(): void
    {
        $this->preProcessControllerAction();
        $this->addUnsubmittedAddressesToClubArgument();
        $this->restrictClubArgumentToCoordinates();
    }

    /**
     * Extbase replaces the addresses of the club with the submitted addresses
     * and removes all other addresses (cascade remove). The map form submits
     * the addresses with POI collection only. So the identities of all other
     * addresses of the club are added to the argument "club".
     */
    protected function addUnsubmittedAddressesToClubArgument(): void
    {
        $clubArgument = $this->request->hasArgument('club') ? $this->request->getArgument('club') : null;
        if (!is_array($clubArgument) || !is_array($clubArgument['addresses'] ?? null)) {
            return;
        }

        $club = $this->clubRepository->findHiddenObject($this->clubAccessService->getIdentity($clubArgument));
        if (!$club instanceof Club) {
            return;
        }

        $submittedAddressUids = array_map($this->clubAccessService->getIdentity(...), $clubArgument['addresses']);
        foreach ($club->getAddresses() as $address) {
            if (!in_array($address->getUid(), $submittedAddressUids, true)) {
                $clubArgument['addresses'][] = ['__identity' => (string)$address->getUid()];
            }
        }

        $this->request = $this->request->withArgument('club', $clubArgument);
    }

    /**
     * The trusted properties of all forms of the plugin are signed for the
     * argument "club". So the argument gets a new property mapping
     * configuration, which maps the coordinates of the existing POI
     * collections of the existing addresses only. All other submitted
     * properties are skipped. Creation of new addresses or POI collections
     * is not allowed.
     */
    protected function restrictClubArgumentToCoordinates(): void
    {
        $propertyMappingConfiguration = $this->replaceClubArgument()->getPropertyMappingConfiguration();
        $propertyMappingConfiguration
            ->allowProperties('addresses')
            ->skipUnknownProperties()
            ->setTypeConverterOptions(PersistentObjectConverter::class, [
                PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED => true,
            ]);
        $propertyMappingConfiguration->allowModificationForSubProperty('addresses.*');
        $propertyMappingConfiguration->forProperty('addresses.*')
            ->allowProperties('txMaps2Uid')
            ->skipUnknownProperties();
        $propertyMappingConfiguration->allowModificationForSubProperty('addresses.*.txMaps2Uid');
        $propertyMappingConfiguration->forProperty('addresses.*.txMaps2Uid')
            ->allowProperties('latitude', 'longitude')
            ->skipUnknownProperties();
    }

    /**
     * Replaces the argument "club" with a new argument without property
     * mapping configuration. The validator of the argument is kept.
     */
    protected function replaceClubArgument(): Argument
    {
        $validator = $this->arguments->getArgument('club')->getValidator();
        $clubArgument = $this->arguments->addNewArgument('club', Club::class, true);
        if ($validator instanceof ValidatorInterface) {
            $clubArgument->setValidator($validator);
        }

        return $clubArgument;
    }

    public function createAction(Club $club): ResponseInterface
    {
        if (!$this->frontendUserRepository->getCurrentFrontendUserUid()) {
            return $this->redirectToClubList('There is no valid user logged in. So record was not saved');
        }

        // The map step never publishes a club. It gets activated by moderation only.
        $club->setHidden(true);
        $this->clubRepository->update($club);
        // The moderation token of the mail signs the "tstamp" of the persisted club
        $this->persistenceManager->persistAll();
        $this->clubCacheService->flushCachesOfClub((int)$club->getUid());

        $this->view->assignMultiple([
            'club' => $club,
            'moderationToken' => $this->clubAccessService->generateModerationToken((int)$club->getUid()),
        ]);
        $this->mailHelper->sendMail(
            $this->view->render(),
            LocalizationUtility::translate('email.subject.create', 'clubdirectory'),
        );

        return $this->redirectToClubList(
            (string)LocalizationUtility::translate('clubCreated', 'clubdirectory'),
        );
    }
}
