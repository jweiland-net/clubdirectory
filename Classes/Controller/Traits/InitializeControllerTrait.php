<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Controller\Traits;

use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3Fluid\Fluid\View\ViewInterface as FluidStandaloneViewInterface;

/**
 * Trait to initialize the view in controllers.
 * It adds tt_content data and ExtConf object to view
 */
trait InitializeControllerTrait
{
    protected function initializeView(FluidStandaloneViewInterface|ViewInterface $view): void
    {
        $view->assign('data', $this->request->getAttribute('currentContentObject')->data);
        $view->assign('extConf', $this->extConf);
    }
}
