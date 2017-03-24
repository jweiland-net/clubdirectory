<?php

namespace JWeiland\Clubdirectory\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <projects@jweiland.net>, jweiland.net
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use JWeiland\Clubdirectory\Domain\Model\Address;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MapController extends AbstractController
{
    /**
     * initialize action new
     * hidden record throws an exception. Thats why I check it here before calling newAction.
     */
    public function initializeNewAction()
    {
        $this->registerClubFromRequest('club');
    }

    /**
     * action new.
     *
     * @param \JWeiland\Clubdirectory\Domain\Model\Club $club
     */
    public function newAction(\JWeiland\Clubdirectory\Domain\Model\Club $club)
    {
        $this->view->assign('club', $club);
    }

    /**
     * initialize action create
     * hidden record throws an exception. Thats why I check it here before calling createAction.
     */
    public function initializeCreateAction()
    {
        $this->registerClubFromRequest('club');

        // we can't work with addresses.* here, because f:form has created addresses.0-3 already, and numbered paths have a higher priority
        //$this->arguments->getArgument('club')->getPropertyMappingConfiguration()->allowModificationForSubProperty('addresses');
        for ($i = 0; $i < 3; ++$i) {
            $this->arguments->getArgument('club')->getPropertyMappingConfiguration()
                ->allowProperties('addresses')
                ->forProperty('addresses')
                ->allowProperties($i)
                ->forProperty($i)
                ->allowProperties('txMaps2Uid')
                ->forProperty('txMaps2Uid')
                ->allowProperties('latitude', 'longitude', '__identity');
            $this->arguments->getArgument('club')->getPropertyMappingConfiguration()->allowModificationForSubProperty('addresses.'.$i);
            $this->arguments->getArgument('club')->getPropertyMappingConfiguration()->allowModificationForSubProperty('addresses.'.$i.'.txMaps2Uid');
        }
    }

    /**
     * action create.
     *
     * @param \JWeiland\Clubdirectory\Domain\Model\Club $club
     */
    public function createAction(\JWeiland\Clubdirectory\Domain\Model\Club $club)
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
     * action edit.
     *
     * @param \JWeiland\Clubdirectory\Domain\Model\Club $club
     */
    public function editAction(\JWeiland\Clubdirectory\Domain\Model\Club $club)
    {
        // this is something very terrible of extbase
        // because of the checkboxes in address records we have to add all 3 addresses. Filled or not filled.
        $i = count($club->getAddresses());
        for ($i; $i < 3; ++$i) {
            $club->getAddresses()->attach(new Address());
        }

        $this->view->assign('club', $club);
        $this->view->assign('categories', $this->categoryRepository->findByParent($this->extConf->getRootCategory()));
        $this->view->assign('addressTitles', $this->getAddressTitles());
    }

    /**
     * initialized update action.
     */
    public function initializeUpdateAction()
    {
        // register hidden object
        $postVars = $this->request->getArgument('club');
        $object = $this->clubRepository->findHiddenEntryByUid($postVars['__identity']);
        $this->session->registerObject($object, $postVars['__identity']);

        // we can't work with addresses.* here, because f:form has created addresses.0-3 already, and numbered paths have a higher priority
        $this->arguments->getArgument('club')->getPropertyMappingConfiguration()->setTargetTypeForSubProperty('logo', 'array');
        $this->arguments->getArgument('club')->getPropertyMappingConfiguration()->setTargetTypeForSubProperty('images', 'array');
        for ($i = 0; $i < 3; ++$i) {
            $this->arguments->getArgument('club')->getPropertyMappingConfiguration()
                ->forProperty('addresses.'.$i)->allowProperties('txMaps2Uid')
                ->forProperty('txMaps2Uid')->allowProperties('latitude', 'longitude', '__identity');
            $this->arguments->getArgument('club')->getPropertyMappingConfiguration()->allowModificationForSubProperty('addresses.'.$i.'.txMaps2Uid');
        }
    }

    /**
     * action update.
     *
     * @param \JWeiland\Clubdirectory\Domain\Model\Club $club
     */
    public function updateAction(\JWeiland\Clubdirectory\Domain\Model\Club $club)
    {
        $this->addMapRecordIfPossible($club);
        $this->clubRepository->update($club);
        $this->sendMail('update', $club);
        $club->setHidden(true);
        $this->redirect('edit', null, null, array('club' => $club));
    }

    /**
     * $search isn't a domainmodel, so we have to do htmlspecialchars on our own.
     */
    public function initializeSearchAction()
    {
        if ($this->request->hasArgument('search')) {
            $search = htmlspecialchars($this->request->getArgument('search'));
            $this->request->setArgument('search', $search);
        }
    }

    /**
     * search show.
     *
     * @param string $search
     */
    public function searchAction($search)
    {
        $clubs = $this->clubRepository->searchClubs($search);
        $this->view->assign('search', $search);
        $this->view->assign('clubs', $clubs);
    }
}
