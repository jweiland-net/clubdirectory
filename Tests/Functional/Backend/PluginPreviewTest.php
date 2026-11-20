<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Backend;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Domain\Persistence\RecordIdentityMap;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Renders the preview of the plugins of clubdirectory in the page module
 * like TYPO3 does: the event PageContentPreviewRenderingEvent is dispatched
 * to AddPluginPreviewDataEventListener and to the Fluid based preview of
 * TYPO3, which renders the template of page TSconfig.
 */
class PluginPreviewTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'typo3/cms-form',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    private const FLEX_FORM = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?><T3FlexForms><data><sheet index="sDEF">'
        . '<language index="lDEF">'
        . '<field index="settings.showGlossar"><value index="vDEF">1</value></field>'
        . '<field index="settings.category"><value index="vDEF">%d</value></field>'
        . '<field index="settings.district"><value index="vDEF">%d</value></field>'
        . '</language></sheet></data></T3FlexForms>';

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/ClubAccess.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/ClubForm.csv');
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->create('default');
    }

    private function renderPreview(string $cType, string $flexForm = ''): string
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tt_content');
        $connection->insert('tt_content', ['pid' => 1, 'CType' => $cType, 'pi_flexform' => $flexForm]);
        $record = BackendUtility::getRecord('tt_content', (int)$connection->lastInsertId());
        self::assertIsArray($record);

        $context = $this->createMock(PageLayoutContext::class);
        $context->method('getCurrentRequest')->willReturn(
            (new ServerRequest('https://www.example.com/typo3/'))
                ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE),
        );
        $context->method('getRecordIdentityMap')->willReturn(new RecordIdentityMap());

        $event = $this->get(EventDispatcherInterface::class)->dispatch(
            new PageContentPreviewRenderingEvent('tt_content', $cType, $record, $context),
        );

        return (string)$event->getPreviewContent();
    }

    #[Test]
    public function pageTsConfigContainsPreviewTemplateOfAllPlugins(): void
    {
        $previewTemplates = BackendUtility::getPagesTSconfig(1)['mod.']['web_layout.']['tt_content.']['preview.'] ?? [];

        foreach (['clubdirectory_clubdirectory', 'clubdirectory_management', 'clubdirectory_search'] as $cType) {
            self::assertSame(
                'EXT:clubdirectory/Resources/Private/Backend/PluginPreview/TableView.html',
                $previewTemplates[$cType] ?? '',
                $cType,
            );
        }
    }

    public static function pluginDataProvider(): array
    {
        return [
            'Club Directory' => [
                'clubdirectory_clubdirectory',
                'Club Directory',
                'Shows a list of club entries.',
            ],
            'Management' => [
                'clubdirectory_management',
                'Club Directory Management',
                'Inserts a form to let frontend users create, edit and manage their own club entries.',
            ],
            'Search' => [
                'clubdirectory_search',
                'Club Directory Search',
                'Inserts a search form to find club entries by search word, category and district.',
            ],
        ];
    }

    #[Test]
    #[DataProvider('pluginDataProvider')]
    public function previewShowsTranslatedTitleAndDescriptionOfPlugin(string $cType, string $title, string $description): void
    {
        $preview = $this->renderPreview($cType);

        self::assertStringContainsString('<strong>' . $title . '</strong>', $preview);
        self::assertStringContainsString($description, $preview);
    }

    #[Test]
    public function previewOfDirectoryShowsTitlesOfSelectedCategoryAndDistrict(): void
    {
        $preview = $this->renderPreview('clubdirectory_clubdirectory', sprintf(self::FLEX_FORM, 7, 2));

        self::assertStringContainsString('Sports', $preview);
        self::assertStringContainsString('Altstadt', $preview);
        self::assertStringContainsString('actions-check', $preview);
    }

    #[Test]
    public function previewOfDirectoryWithoutSelectionShowsAllCategoriesAndDistricts(): void
    {
        $preview = $this->renderPreview('clubdirectory_clubdirectory', sprintf(self::FLEX_FORM, 0, 0));

        self::assertStringContainsString('Show all categories', $preview);
        self::assertStringContainsString('Show all districts', $preview);
    }

    #[Test]
    public function previewOfManagementAndSearchShowsNoSettingsOfDirectory(): void
    {
        self::assertStringNotContainsString('<table', $this->renderPreview('clubdirectory_management'));
        self::assertStringNotContainsString('<table', $this->renderPreview('clubdirectory_search'));
    }
}
