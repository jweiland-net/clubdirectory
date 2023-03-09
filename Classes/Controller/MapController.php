<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Controller;

use JWeiland\Clubdirectory\Controller\Traits\AddressTrait;
use JWeiland\Clubdirectory\Controller\Traits\ControllerInjectionTrait;
use JWeiland\Clubdirectory\Controller\Traits\InitializeControllerTrait;
use JWeiland\Clubdirectory\Domain\Model\Club;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to edit map records. Just after saving a club.
 */
class MapController extends ActionController
{
    use ControllerInjectionTrait;
    use InitializeControllerTrait;
    use AddressTrait;

    /**
     * As club was already validated in ClubController create/update there can't be any errors. So ignore validation.
     *
     * @Extbase\IgnoreValidation("club")
     */
    public function newAction(Club $club): void
    {
        $this->view->assign('club', $club);
    }

    public function createAction(Club $club): void
    {
        if ($this->frontendUserRepository->getCurrentFrontendUserUid()) {
            $this->view->assign('club', $club);
            $this->mailHelper->sendMail(
                $this->view->render(),
                LocalizationUtility::translate('email.subject.create', 'clubdirectory')
            );

            $this->clubRepository->update($club);
            $this->addFlashMessage(LocalizationUtility::translate('clubCreated', 'clubdirectory'));
        } else {
            $this->addFlashMessage('There is no valid user logged in. So record was not saved');
        }

        $this->redirect('list', 'Club');
    }

    /**
     * @Extbase\IgnoreValidation("club")
     */
    public function editAction(Club $club): void
    {
        $this->view->assign('club', $club);
        $this->view->assign('categories', $this->categoryRepository->findByParent($this->extConf->getRootCategory()));
        $this->view->assign('addressTitles', $this->getAddressTitles());
    }

    public function updateAction(Club $club): void
    {
        if ($this->mapHelper->addMapRecordIfPossible($club, $this) === false) {
            $this->errorAction();
        }
        $this->clubRepository->update($club);
        $this->view->assign('club', $club);
        $this->mailHelper->sendMail(
            $this->view->render(),
            LocalizationUtility::translate('email.subject.update', 'clubdirectory')
        );
        $club->setHidden(true);

        $this->redirect('list', 'Club', null, ['club' => $club]);
    }
}
