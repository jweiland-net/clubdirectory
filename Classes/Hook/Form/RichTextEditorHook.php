<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Hook\Form;

use JWeiland\Clubdirectory\Form\FormOwner;
use JWeiland\Clubdirectory\Form\RichTextEditorAssets;
use JWeiland\Clubdirectory\Form\RichTextFormElementConfigurator;
use JWeiland\Clubdirectory\Sanitizer\RichTextSanitizer;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\Renderable\RootRenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * Adds the CKEditor to the rich text elements (dataType "richText"), which
 * are rendered on the current page of the form. So the assets are loaded on
 * these pages only. Each textarea gets its own options as JSON.
 *
 * The toolbar of the editor is based on the tags, which the sanitizer allows
 * for the column of the element (see ModifyRichTextSanitizerConfigurationEvent).
 * The column is "properties.dbMapping.column", the table is
 * "properties.dbMapping.table" (default: tx_clubdirectory_domain_model_club).
 *
 * Other extensions use the same data type for their forms. So forms, which
 * are not built by clubdirectory, are skipped, see FormOwner.
 */
final readonly class RichTextEditorHook
{
    public const DATA_ATTRIBUTE = 'data-clubdirectory-rich-text';

    private const DEFAULT_TABLE = 'tx_clubdirectory_domain_model_club';

    public function __construct(
        private RichTextEditorAssets $richTextEditorAssets,
        private RichTextSanitizer $richTextSanitizer,
    ) {}

    public function beforeRendering(FormRuntime $formRuntime, RootRenderableInterface $renderable): void
    {
        if (
            !$renderable instanceof FormElementInterface
            || ($renderable->getProperties()['dbMapping']['dataType'] ?? '') !== RichTextFormElementConfigurator::DATA_TYPE
            || !FormOwner::isClubdirectoryForm($formRuntime->getFormDefinition())
        ) {
            return;
        }

        $dbMapping = $renderable->getProperties()['dbMapping'];
        $configuration = $this->richTextSanitizer->getConfiguration(
            (string)($dbMapping['table'] ?? self::DEFAULT_TABLE),
            (string)($dbMapping['column'] ?? ''),
        );

        $renderable->setProperty('fluidAdditionalAttributes', [
            self::DATA_ATTRIBUTE => json_encode(
                $this->richTextEditorAssets->getEditorOptions($formRuntime->getRequest(), $configuration),
                JSON_THROW_ON_ERROR,
            ),
        ]);
        $this->richTextEditorAssets->load();
    }
}
