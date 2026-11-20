<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\EventListener;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Service\FlexFormService;

/**
 * Prepares the data of the preview of the plugins of clubdirectory in the
 * page module. The preview itself is the Fluid template of page TSconfig
 * "mod.web_layout.tt_content.preview.[CType]", rendered by TYPO3. The
 * template gets the variable "clubdirectory" with:
 *
 * - pluginKey: "clubdirectory", "management" or "search" to translate the
 *   title and description of the plugin
 * - settings: the settings of the FlexForm
 * - categoryTitle, districtTitle: the titles of the selected category and
 *   district. The relations of a FlexForm are not resolved by TYPO3.
 */
#[AsEventListener(
    identifier: 'clubdirectory/add-plugin-preview-data',
    before: 'typo3-backend/fluid-preview/content',
)]
final readonly class AddPluginPreviewDataEventListener
{
    private const PLUGIN_KEYS = [
        'clubdirectory_clubdirectory' => 'clubdirectory',
        'clubdirectory_management' => 'management',
        'clubdirectory_search' => 'search',
    ];

    public function __construct(
        private FlexFormService $flexFormService,
    ) {}

    public function __invoke(PageContentPreviewRenderingEvent $event): void
    {
        $pluginKey = self::PLUGIN_KEYS[$event->getRecordType()] ?? '';
        if ($event->getTable() !== 'tt_content' || $pluginKey === '') {
            return;
        }

        $record = $event->getRecord();
        $settings = $this->getSettings((string)($record['pi_flexform'] ?? ''));
        $record['clubdirectory'] = [
            'pluginKey' => $pluginKey,
            'settings' => $settings,
            'categoryTitle' => $this->getTitle('sys_category', 'title', $settings['category'] ?? 0),
            'districtTitle' => $this->getTitle('tx_clubdirectory_domain_model_district', 'district', $settings['district'] ?? 0),
        ];

        $event->setRecord($record);
    }

    /**
     * @return array<string, mixed>
     */
    private function getSettings(string $flexForm): array
    {
        $settings = $flexForm === '' ? [] : $this->flexFormService->convertFlexFormContentToArray($flexForm)['settings'] ?? [];

        return is_array($settings) ? $settings : [];
    }

    private function getTitle(string $table, string $labelColumn, mixed $uid): string
    {
        $record = is_numeric($uid) && (int)$uid > 0 ? BackendUtility::getRecord($table, (int)$uid, $labelColumn) : null;

        return (string)($record[$labelColumn] ?? '');
    }
}
