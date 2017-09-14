<?php
declare(strict_types=1);
namespace JWeiland\Clubdirectory\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use JWeiland\Clubdirectory\Domain\Model\Address;
use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Property\TypeConverter\UploadMultipleFilesConverter;
use JWeiland\Clubdirectory\Property\TypeConverter\UploadOneFileConverter;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class ClubController
 *
 * @package JWeiland\Clubdirectory\Controller
 */
class ClubController extends AbstractController
{
    /**
     * action list.
     *
     * @param string $letter Show only records starting with this letter
     * @validate $letter String, StringLength(minimum=0,maximum=3)
     * @return void
     */
    public function listAction($letter = null)
    {
        if ($letter === null) {
            if ($this->settings['category'] || $this->settings['district']) {
                $clubs = $this->clubRepository->findByCategory(
                    (int) $this->settings['category'],
                    (int) $this->settings['district']
                );
            } else {
                $clubs = $this->clubRepository->findAll();
            }
        } else {
            $clubs = $this->clubRepository->findByStartingLetter(
                $letter,
                (int) $this->settings['category'],
                (int) $this->settings['district']
            );
        }
        $this->view->assign('clubs', $clubs);
        $this->view->assign('glossar', $this->getGlossar());
        $this->view->assign('allowedUserGroup', $this->extConf->getUserGroup());
    }

    /**
     * action listMyClubs.
     *
     * @return void
     */
    public function listMyClubsAction()
    {
        $clubs = $this->clubRepository->findByFeUser((int) $GLOBALS['TSFE']->fe_user->user['uid']);
        $this->view->assign('clubs', $clubs);
        $this->view->assign('allowedUserGroup', $this->extConf->getUserGroup());
    }

    /**
     * action show.
     *
     * @param Club $club
     * @return void
     */
    public function showAction(Club $club)
    {
        $isValidUser = false;
        if (\is_array($GLOBALS['TSFE']->fe_user->user) && \count($club->getFeUsers())) {
            /** @var FrontendUser $feUser */
            foreach ($club->getFeUsers() as $feUser) {
                if ($feUser->getUid() === (integer) $GLOBALS['TSFE']->fe_user->user['uid']) {
                    $isValidUser = true;
                    break;
                }
            }
        }
        $this->view->assign('club', $club);
        $this->view->assign('isValidUser', $isValidUser);
    }

    /**
     * action new.
     *
     * @return void
     */
    public function newAction()
    {
        $this->deleteUploadedFilesOnValidationErrors('club');
        /** @var Club $club */
        $club = $this->objectManager->get(Club::class);
        for ($i = 0; $i < 3; ++$i) {
            /** @var Address $address */
            $address = $this->objectManager->get(Address::class);
            $club->addAddress($address);
        }

        $script = ExtensionManagementUtility::siteRelPath($this->request->getControllerExtensionKey())
            . 'Resources/Public/JavaScript/script.js';
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addJsFooterFile($script);

        $this->view->assign('club', $club);
        $this->view->assign('categories', $this->categoryRepository->findByParent($this->extConf->getRootCategory()));
        $this->view->assign('addressTitles', $this->getAddressTitles());
    }

    /**
     * initialized create action
     *
     * @return void
     */
    public function initializeCreateAction()
    {
        /** @var UploadOneFileConverter $oneFileTypeConverter */
        $oneFileTypeConverter = $this->objectManager->get(
            UploadOneFileConverter::class
        );
        $this->arguments->getArgument('club')
            ->getPropertyMappingConfiguration()
            ->forProperty('logo')
            ->setTypeConverter($oneFileTypeConverter);

        /** @var UploadMultipleFilesConverter $multipleFilesTypeConverter */
        $multipleFilesTypeConverter = $this->objectManager->get(
            UploadMultipleFilesConverter::class
        );
        $this->arguments->getArgument('club')
            ->getPropertyMappingConfiguration()
            ->forProperty('images')
            ->setTypeConverter($multipleFilesTypeConverter);
    }

    /**
     * action create.
     *
     * @param Club $club
     * @return void
     */
    public function createAction(Club $club)
    {
        if ($GLOBALS['TSFE']->fe_user->user['uid']) {
            /** @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $feUser */
            $feUser = $this->feUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
            $club->addFeUser($feUser);
            $this->addMapRecordIfPossible($club);
            $club->setHidden(true);
            $this->clubRepository->add($club);
            $this->persistenceManager->persistAll();
            $this->redirect('new', 'Map', 'clubdirectory', ['club' => $club]);
        } else {
            $this->addFlashMessage('There is no valid user logged in. So record was not saved');
            $this->redirect('list');
        }
    }

    /**
     * initialized edit action.
     *
     * @return void
     */
    public function initializeEditAction()
    {
        $this->registerClubFromRequest('club');
    }

    /**
     * action edit.
     *
     * @param Club $club
     * @return void
     */
    public function editAction(Club $club)
    {
        // this is something very terrible of extbase
        // because of the checkboxes in address records we have to add all 3 addresses. Filled or not filled.
        for ($i = \count($club->getAddresses()); $i < 3; ++$i) {
            $club->getAddresses()->attach(new Address());
        }

        $this->view->assign('club', $club);
        $this->view->assign('categories', $this->categoryRepository->findByParent($this->extConf->getRootCategory()));
        $this->view->assign('addressTitles', $this->getAddressTitles());
    }

    /**
     * initialized update action.
     *
     * @return void
     */
    public function initializeUpdateAction()
    {
        $this->registerClubFromRequest('club');

        $argument = $this->request->getArgument('club');
        /** @var \JWeiland\Clubdirectory\Domain\Model\Club $club */
        $club = $this->clubRepository->findByIdentifier($argument['__identity']);
        /** @var UploadOneFileConverter $oneFileTypeConverter */
        $oneFileTypeConverter = $this->objectManager->get(
            UploadOneFileConverter::class
        );
        $this->arguments->getArgument('club')
            ->getPropertyMappingConfiguration()
            ->forProperty('logo')
            ->setTypeConverter($oneFileTypeConverter)
            ->setTypeConverterOptions(
                UploadOneFileConverter::class,
                [
                    'IMAGE' => $club->getLogo()
                ]
            );

        /** @var UploadMultipleFilesConverter $multipleFilesTypeConverter */
        $multipleFilesTypeConverter = $this->objectManager->get(
            UploadMultipleFilesConverter::class
        );
        $this->arguments->getArgument('club')
            ->getPropertyMappingConfiguration()
            ->forProperty('images')
            ->setTypeConverter($multipleFilesTypeConverter)
            ->setTypeConverterOptions(
                UploadMultipleFilesConverter::class,
                [
                    'IMAGES' => $club->getImages()
                ]
            );

        // we can't work with addresses.* here,
        // because f:form has created addresses.0-3 already, and numbered paths have a higher priority
        for ($i = 0; $i < 3; ++$i) {
            $this->arguments->getArgument('club')->getPropertyMappingConfiguration()
                ->forProperty('addresses.'.$i)->allowProperties('txMaps2Uid')
                ->forProperty('txMaps2Uid')->allowProperties('latitude', 'longitude', '__identity');
            $this->arguments->getArgument('club')
                ->getPropertyMappingConfiguration()
                ->allowModificationForSubProperty('addresses.' . $i . '.txMaps2Uid');
        }
    }

    /**
     * action update.
     *
     * @param Club $club
     * @return void
     */
    public function updateAction(Club $club)
    {
        $this->addMapRecordIfPossible($club);
        $this->clubRepository->update($club);
        $this->sendMail('update', $club);
        $club->setHidden(true);
        $this->addFlashMessage(LocalizationUtility::translate('clubUpdated', 'clubdirectory'));
        $this->redirect('list', null, null, ['club' => $club]);
    }

    /**
     * $search isn't a domainmodel, so we have to do htmlspecialchars on our own.
     *
     * @return void
     */
    public function initializeSearchAction()
    {
        if ($this->request->hasArgument('search')) {
            $search = \htmlspecialchars($this->request->getArgument('search'));
            $this->request->setArgument('search', $search);
        }
    }

    /**
     * search show.
     *
     * @param string $search
     * @return void
     */
    public function searchAction($search)
    {
        $clubs = $this->clubRepository->searchClubs($search);
        $this->view->assign('search', $search);
        $this->view->assign('clubs', $clubs);
    }
}
