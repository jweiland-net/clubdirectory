<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Fixtures\Hook;

use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\Renderable\RootRenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * Hook of another extension (like events2 or yellowpages2), which adds its
 * own rich text editor to elements with "dataType: richText" of its own
 * forms only (rendering option "formOwner").
 */
final readonly class ForeignRichTextEditorHook
{
    public const DATA_ATTRIBUTE = 'data-foreign-rich-text';

    public const FORM_OWNER = 'foreign';

    public function beforeRendering(FormRuntime $formRuntime, RootRenderableInterface $renderable): void
    {
        if (!$renderable instanceof FormElementInterface
            || ($renderable->getProperties()['dbMapping']['dataType'] ?? '') !== 'richText'
            || ($formRuntime->getFormDefinition()->getRenderingOptions()['formOwner'] ?? '') !== self::FORM_OWNER
        ) {
            return;
        }

        $renderable->setProperty('fluidAdditionalAttributes', [self::DATA_ATTRIBUTE => '{}']);
    }
}
