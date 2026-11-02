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
use JWeiland\Clubdirectory\Controller\Traits\PostProcessFluidVariablesTrait;
use JWeiland\Clubdirectory\Controller\Traits\PreProcessControllerActionTrait;
use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Domain\Model\Search;
use JWeiland\Clubdirectory\Domain\Repository\DistrictRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Frontend\Controller\ErrorController;

/**
 * Controller to list, show and edit clubs
 */
class ClubController extends ActionController
{
    use AddressTrait;
    use ControllerInjectionTrait;
    use InitializeControllerTrait;
    use PostProcessFluidVariablesTrait;
    use PreProcessControllerActionTrait;

    public function __construct(
        protected DistrictRepository $districtRepository,
        protected PersistenceManagerInterface $persistenceManager,
        protected ErrorController $errorController,
    ) {}

    public function initializeListAction(): void
    {
        $this->preProcessControllerAction();
    }

    public function listAction(string $letter = ''): ResponseInterface
    {
        $this->postProcessAndAssignFluidVariables([
            'clubs' => $this->clubRepository->findFilteredBy(
                (int)$this->settings['category'],
                (int)$this->settings['district'],
                $letter,
            ),
            'categories' => $this->categoryRepository->getCategories(),
            'districts' => $this->districtRepository->findAll(),
            'search' => GeneralUtility::makeInstance(Search::class),
            'allowedUserGroup' => $this->extConf->getUserGroup(),
        ]);

        return $this->htmlResponse();
    }

    public function initializeShowAction(): void
    {
        $this->preProcessControllerAction();
    }

    /**
     * We are using int to prevent calling any Validator.
     * Hidden (not activated yet) and unknown clubs result in the 404 error
     * handling of the site.
     *
     * @throws PropagateResponseException
     */
    public function showAction(int $club): ResponseInterface
    {
        $clubObject = $this->clubRepository->findByIdentifier($club);
        if (!$clubObject instanceof Club) {
            throw new PropagateResponseException(
                $this->errorController->pageNotFoundAction($this->request, 'Club not found'),
                1762098000,
            );
        }

        $this->postProcessAndAssignFluidVariables([
            'club' => $clubObject,
        ]);

        return $this->htmlResponse();
    }
}
