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
use JWeiland\Clubdirectory\Controller\Traits\PostProcessControllerActionTrait;
use JWeiland\Clubdirectory\Controller\Traits\PostProcessFluidVariablesTrait;
use JWeiland\Clubdirectory\Controller\Traits\PreProcessControllerActionTrait;
use JWeiland\Clubdirectory\Domain\Model\Search;
use JWeiland\Clubdirectory\Domain\Repository\DistrictRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/**
 * Controller to show the search form and its (uncached) search results
 */
class SearchController extends ActionController
{
    use AddressTrait;
    use ControllerInjectionTrait;
    use InitializeControllerTrait;
    use PostProcessFluidVariablesTrait;
    use PostProcessControllerActionTrait;
    use PreProcessControllerActionTrait;

    public function __construct(
        protected DistrictRepository $districtRepository,
        protected PersistenceManagerInterface $persistenceManager,
    ) {}

    public function initializeShowAction(): void
    {
        $this->preProcessControllerAction();
    }

    public function showAction(): ResponseInterface
    {
        $this->postProcessAndAssignFluidVariables([
            'categories' => $this->categoryRepository->getCategories(),
            'districts' => $this->districtRepository->findAll(),
            'search' => GeneralUtility::makeInstance(Search::class),
            'allowedUserGroup' => $this->extConf->getUserGroup(),
        ]);

        return $this->htmlResponse();
    }

    public function initializeListSearchResultsAction(): void
    {
        $this->preProcessControllerAction();
    }

    public function listSearchResultsAction(Search $search): ResponseInterface
    {
        $this->postProcessAndAssignFluidVariables([
            'clubs' => $this->clubRepository->findBySearch($search),
            'categories' => $this->categoryRepository->getCategories(),
            'districts' => $this->districtRepository->findAll(),
            'search' => $search,
            'allowedUserGroup' => $this->extConf->getUserGroup(),
        ]);

        return $this->htmlResponse();
    }
}
