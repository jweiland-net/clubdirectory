<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Controller\Traits;

use JWeiland\Clubdirectory\Helper\HiddenObjectHelper;

/**
 * Trait to initialize the view in controllers.
 * It adds tt_content data and ExtConf object to view
 */
trait InitializeControllerTrait
{
    protected HiddenObjectHelper $hiddenObjectHelper;

    public function injectHiddenObjectHelper(HiddenObjectHelper $hiddenObjectHelper): void
    {
        $this->hiddenObjectHelper = $hiddenObjectHelper;
    }

    protected function initializeView($view): void
    {
        $view->assign('data', $this->request->getAttribute('currentContentObject')->data);
        $view->assign('extConf', $this->extConf);
    }

    public function initializeAction(): void
    {
        // If this value was not set, then it will be filled with 0,
        // but that is not good, because UriBuilder accepts 0 as pid, so it's better to set it to NULL
        if (empty($this->settings['pidOfDetailPage'])) {
            $this->settings['pidOfDetailPage'] = null;
        }

        $this->hiddenObjectHelper->registerHiddenObjectInExtbaseSession(
            $this->clubRepository,
            $this->request,
            'club',
        );
    }
}
