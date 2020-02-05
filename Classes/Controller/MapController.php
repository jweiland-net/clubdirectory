<?php
declare(strict_types = 1);
namespace JWeiland\Clubdirectory\Controller;

/*
 * This file is part of the clubdirectory project.
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

use JWeiland\Clubdirectory\Domain\Model\Club;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to edit map records. Just after saving a club.
 */
class MapController extends AbstractController
{
    /**
     * We are using int to prevent calling any Validator
     *
     * @param int $club
     */
    public function newAction(int $club)
    {
        $this->view->assign(
            'club',
            $this->clubRepository->findHiddenEntryByUid($club)
        );
    }

    /**
     * Club is a hidden record. So register it in session object
     */
    public function initializeCreateAction()
    {
        $this->registerClubFromRequest('club');
    }

    /**
     * @param Club $club
     */
    public function createAction(Club $club)
    {
        if ($GLOBALS['TSFE']->fe_user->user['uid']) {
            $this->sendMail('create', $club);
            $this->clubRepository->update($club);
            $this->addFlashMessage(LocalizationUtility::translate('clubCreated', 'clubdirectory'));
        } else {
            $this->addFlashMessage('There is no valid user logged in. So record was not saved');
        }
        $this->redirect('list', 'Club');
    }

    /**
     * @param Club $club
     */
    public function editAction(Club $club)
    {
        $this->view->assign('club', $club);
        $this->view->assign('categories', $this->categoryRepository->findByParent($this->extConf->getRootCategory()));
        $this->view->assign('addressTitles', $this->getAddressTitles());
    }

    public function initializeUpdateAction()
    {
        $this->registerClubFromRequest('club');

        // we can't work with addresses.* here, because f:form has created addresses.0-3 already,
        // and numbered paths have a higher priority
        $this->arguments->getArgument('club')
            ->getPropertyMappingConfiguration()
            ->setTargetTypeForSubProperty('logo', 'array');

        $this->arguments->getArgument('club')
            ->getPropertyMappingConfiguration()
            ->setTargetTypeForSubProperty('images', 'array');

        for ($i = 0; $i < 3; ++$i) {
            $this->arguments->getArgument('club')->getPropertyMappingConfiguration()
                ->forProperty('addresses.' . $i)->allowProperties('txMaps2Uid')
                ->forProperty('txMaps2Uid')->allowProperties('latitude', 'longitude', '__identity');
            $this->arguments->getArgument('club')
                ->getPropertyMappingConfiguration()
                ->allowModificationForSubProperty('addresses.' . $i . '.txMaps2Uid');
        }
    }

    /**
     * @param Club $club
     */
    public function updateAction(Club $club)
    {
        $this->addMapRecordIfPossible($club);
        $this->clubRepository->update($club);
        $this->sendMail('update', $club);
        $club->setHidden(true);
        $this->redirect('edit', null, null, ['club' => $club]);
    }

    /**
     * $search isn't a domain model, so we have to do htmlspecialchars on our own.
     */
    public function initializeSearchAction()
    {
        if ($this->request->hasArgument('search')) {
            $search = \htmlspecialchars($this->request->getArgument('search'));
            $this->request->setArgument('search', $search);
        }
    }

    /**
     * @param string $search
     */
    public function searchAction(string $search)
    {
        $clubs = $this->clubRepository->searchClubs($search);
        $this->view->assign('search', $search);
        $this->view->assign('clubs', $clubs);
    }
}
