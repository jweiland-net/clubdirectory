<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Controller\Traits;

use JWeiland\Clubdirectory\Configuration\ExtConf;
use JWeiland\Clubdirectory\Domain\Repository\CategoryRepository;
use JWeiland\Clubdirectory\Domain\Repository\ClubRepository;
use JWeiland\Clubdirectory\Domain\Repository\FrontendUserRepository;
use JWeiland\Clubdirectory\Helper\MailHelper;
use JWeiland\Clubdirectory\Helper\MapHelper;

/**
 * Trait which injects various properties and inject-methods to controllers
 */
trait ControllerInjectionTrait
{
    protected CategoryRepository $categoryRepository;

    protected ClubRepository $clubRepository;

    protected ExtConf $extConf;

    protected FrontendUserRepository $frontendUserRepository;

    protected MailHelper $mailHelper;

    protected MapHelper $mapHelper;

    public function injectCategoryRepository(CategoryRepository $categoryRepository): void
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function injectClubRepository(ClubRepository $clubRepository): void
    {
        $this->clubRepository = $clubRepository;
    }

    public function injectExtConf(ExtConf $extConf): void
    {
        $this->extConf = $extConf;
    }

    public function injectFrontendUserRepository(FrontendUserRepository $frontendUserRepository): void
    {
        $this->frontendUserRepository = $frontendUserRepository;
    }

    public function injectMailHelper(MailHelper $mailHelper): void
    {
        $this->mailHelper = $mailHelper;
    }

    public function injectMapHelper(MapHelper $mapHelper): void
    {
        $this->mapHelper = $mapHelper;
    }
}
