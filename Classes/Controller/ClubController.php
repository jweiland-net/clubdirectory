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
use JWeiland\Clubdirectory\Helper\PathSegmentHelper;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/**
 * Controller to list, show and edit clubs
 */
class ClubController extends ActionController
{
    use AddressTrait;
    use ControllerInjectionTrait;
    use InitializeControllerTrait;
    use PostProcessFluidVariablesTrait;
    use PostProcessControllerActionTrait;
    use PreProcessControllerActionTrait;

    public function __construct(
        protected DistrictRepository $districtRepository,
        protected PathSegmentHelper $pathSegmentHelper,
        protected PersistenceManagerInterface $persistenceManager,
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
     * We are using int to prevent calling any Validator
     */
    public function showAction(int $club): ResponseInterface
    {
        $this->postProcessAndAssignFluidVariables([
            'club' => $this->clubRepository->findByIdentifier($club),
        ]);

        return $this->htmlResponse();
    }
}
