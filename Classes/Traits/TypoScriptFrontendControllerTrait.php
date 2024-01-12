<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Traits;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Trait to retrieve the TSFE.
 * Available in frontend requests only.
 */
trait TypoScriptFrontendControllerTrait
{
    public function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        /** @var ServerRequestInterface $request */
        $request = $GLOBALS['TYPO3_REQUEST'];

        return $request->getAttribute('frontend.controller');
    }
}
