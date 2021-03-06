<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Controller;

use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Helper\HiddenObjectHelper;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to edit map records. Just after saving a club.
 */
class MapController extends AbstractController
{
    public function initializeAction(): void
    {
        parent::initializeAction();

        $hiddenObjectHelper = $this->objectManager->get(HiddenObjectHelper::class);
        $hiddenObjectHelper->registerHiddenObjectInExtbaseSession(
            $this->clubRepository,
            $this->request,
            'club'
        );
    }

    /**
     * As club was already validated in ClubController create/update there can't be any errors. So ignore validation.
     *
     * @param Club $club
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("club")
     */
    public function newAction(Club $club): void
    {
        $this->view->assign('club', $club);
    }

    /**
     * @param Club $club
     */
    public function createAction(Club $club): void
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
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("club")
     */
    public function editAction(Club $club): void
    {
        $this->view->assign('club', $club);
        $this->view->assign('categories', $this->categoryRepository->findByParent($this->extConf->getRootCategory()));
        $this->view->assign('addressTitles', $this->getAddressTitles());
    }

    /**
     * @param Club $club
     */
    public function updateAction(Club $club): void
    {
        $this->addMapRecordIfPossible($club);
        $this->clubRepository->update($club);
        $this->sendMail('update', $club);
        $club->setHidden(true);
        $this->redirect('list', 'Club', null, ['club' => $club]);
    }
}
