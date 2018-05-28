<?php
declare(strict_types=1);
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

use JWeiland\Clubdirectory\Domain\Model\Address;
use JWeiland\Clubdirectory\Domain\Model\Club;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class MapController
 *
 * @package JWeiland\Clubdirectory\Controller
 */
class MapController extends AbstractController
{
    /**
     * initialize action new
     * hidden record throws an exception. Thats why I check it here before calling newAction.
     *
     * @return void
     */
    public function initializeNewAction()
    {
        $this->registerClubFromRequest('club');
    }

    /**
     * action new.
     *
     * @param Club $club
     *
     * @return void
     */
    public function newAction(Club $club)
    {
        $this->view->assign('club', $club);
    }

    /**
     * initialize action create
     * hidden record throws an exception. Thats why I check it here before calling createAction.
     *
     * @return void
     */
    public function initializeCreateAction()
    {
        $this->registerClubFromRequest('club');

        // we can't work with addresses.* here, because f:form has created addresses.0-3 already,
        // and numbered paths have a higher priority
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
            $this->arguments->getArgument('club')
                ->getPropertyMappingConfiguration()
                ->allowModificationForSubProperty('addresses.'.$i);

            $this->arguments->getArgument('club')
                ->getPropertyMappingConfiguration()
                ->allowModificationForSubProperty('addresses.'.$i.'.txMaps2Uid');
        }
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
        // register hidden object
        $postVars = $this->request->getArgument('club');
        $object = $this->clubRepository->findHiddenEntryByUid($postVars['__identity']);
        $this->session->registerObject($object, $postVars['__identity']);

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
                ->forProperty('addresses.'.$i)->allowProperties('txMaps2Uid')
                ->forProperty('txMaps2Uid')->allowProperties('latitude', 'longitude', '__identity');
            $this->arguments->getArgument('club')
                ->getPropertyMappingConfiguration()
                ->allowModificationForSubProperty('addresses.'.$i.'.txMaps2Uid');
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
        $this->redirect('edit', null, null, ['club' => $club]);
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
