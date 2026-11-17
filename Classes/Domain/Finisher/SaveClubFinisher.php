<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Domain\Finisher;

use JWeiland\Clubdirectory\Converter\RteContentConverter;
use JWeiland\Clubdirectory\Helper\PathSegmentHelper;
use JWeiland\Clubdirectory\Service\ClubAccessService;
use JWeiland\Clubdirectory\Service\ClubCacheService;
use JWeiland\Clubdirectory\Service\ClubFormRecordService;
use JWeiland\Clubdirectory\Service\GeocodingService;
use JWeiland\Clubdirectory\Service\PoiCollectionService;
use JWeiland\Clubdirectory\Service\RichTextService;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * Stores the values of the club form based on EXT:form as club record incl.
 * categories, frontend user, addresses with their POI collection of EXT:maps2
 * and file references of logo and images.
 *
 * Each step of "options" defines a "table", the form "elements" mapped on
 * its columns and further "databaseColumnMappings". Addresses and file
 * references have a "position". The club to edit is the query parameter
 * "club" of the Management plugin, see ClubAccessService.
 *
 * Options of the element mappings:
 * - castToInteger: Store the value as integer
 * - normalizePlainText: Store plain text like the backend textarea
 * - convertPlainTextToHtml: Store plain text like the backend RTE
 * - sanitizeRichText: Sanitize the HTML of the rich text editor and store
 *   it like the backend RTE. See RichTextFormElementConfigurator.
 * - poiCollection: Store the position of "Maps2PoiPosition" in EXT:maps2
 *
 * Values of elements with "options" (select boxes, checkboxes) are stored
 * only, if they are an option.
 */
class SaveClubFinisher extends AbstractFinisher
{
    private const TABLE_CATEGORY_MM = 'sys_category_record_mm';

    private const TABLE_USER_MM = 'tx_clubdirectory_club_user_mm';

    private const VARIABLE_CLUB_UID = 'insertedUids.' . ClubFormRecordService::TABLE_CLUB;

    /**
     * @var array<string, mixed>
     */
    protected $defaultOptions = [
        'mode' => 'insert',
    ];

    public function __construct(
        protected readonly ConnectionPool $connectionPool,
        protected readonly ClubAccessService $clubAccessService,
        protected readonly ClubFormRecordService $clubFormRecordService,
        protected readonly ClubCacheService $clubCacheService,
        protected readonly PathSegmentHelper $pathSegmentHelper,
        protected readonly PoiCollectionService $poiCollectionService,
        protected readonly GeocodingService $geocodingService,
        protected readonly RteContentConverter $rteContentConverter,
        protected readonly RichTextService $richTextService,
        protected readonly Context $context,
    ) {}

    /**
     * @throws FinisherException
     */
    protected function executeInternal(): void
    {
        if (!$this->clubAccessService->isFormAccessAllowed($this->finisherContext->getRequest())) {
            $this->finisherContext->cancel();
            throw new FinisherException('The current user is not allowed to create or edit this club.', 1763337603);
        }

        $steps = isset($this->options['table']) ? [1 => $this->options] : $this->options;
        foreach ($steps as $step => $stepOptions) {
            $this->options = is_array($stepOptions) ? $stepOptions : [];
            $this->processStep((int)$step);
        }

        $this->clubCacheService->flushCachesOfClub($this->getClubUid());
    }

    protected function processStep(int $step): void
    {
        $table = (string)$this->parseOption('table');
        $data = $this->mapElementValues($this->getColumnMappings());
        $data = match ($table) {
            ClubFormRecordService::TABLE_CLUB => $this->prepareClub($data, $step),
            self::TABLE_CATEGORY_MM => $this->saveCategories($data),
            self::TABLE_USER_MM => $this->saveFrontendUser($data),
            ClubFormRecordService::TABLE_ADDRESS => $this->prepareAddress($data),
            ClubFormRecordService::TABLE_FILE_REFERENCE => $this->prepareFileReference($data),
            default => $data,
        };

        $this->saveToDatabase($data, $table, $step);

        if ($table === ClubFormRecordService::TABLE_CLUB) {
            $this->finishClub($data);
        } elseif ($table === ClubFormRecordService::TABLE_FILE_REFERENCE) {
            $this->updateNumberOfFiles((string)$this->parseOption('databaseColumnMappings.fieldname.value'));
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getColumnMappings(): array
    {
        $columnMappings = $this->options['databaseColumnMappings'] ?? [];
        $data = [];
        foreach (is_array($columnMappings) ? $columnMappings : [] as $column => $configuration) {
            $value = $this->parseOption('databaseColumnMappings.' . $column . '.value');
            if (empty($value) && is_array($configuration) && ($configuration['skipIfValueIsEmpty'] ?? false) === true) {
                continue;
            }

            $data[(string)$column] = $value;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function mapElementValues(array $data): array
    {
        $elements = $this->options['elements'] ?? [];
        $formValues = $this->finisherContext->getFormValues();
        foreach (is_array($elements) ? $elements : [] as $identifier => $configuration) {
            $element = $this->finisherContext->getFormRuntime()->getFormDefinition()->getElementByIdentifier((string)$identifier);
            if (!$element instanceof FormElementInterface
                || !is_array($configuration)
                || !isset($configuration['mapOnDatabaseColumn'])
            ) {
                continue;
            }

            $column = (string)$configuration['mapOnDatabaseColumn'];
            $value = $this->restrictToOptions($formValues[$identifier] ?? null, $element);
            $data[$column] = ($configuration['sanitizeRichText'] ?? false) === true
                ? $this->prepareRichText($this->toString($value), $column, $this->toInteger($data['pid'] ?? 0))
                : $this->convertValue($value, $configuration);
        }

        return $data;
    }

    /**
     * Sanitizes the value of a rich text element for the column of the
     * current table. t3:// links are compared with the stored record.
     */
    protected function prepareRichText(string $value, string $column, int $pid): string
    {
        $table = (string)$this->parseOption('table');
        $uid = $table === ClubFormRecordService::TABLE_CLUB
            ? $this->clubAccessService->getClubUidFromQuery($this->finisherContext->getRequest())
            : 0;

        return $this->richTextService->prepareForPersistence($value, $table, $column, $uid, $pid);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    protected function convertValue(mixed $value, array $configuration): mixed
    {
        return match (true) {
            $value instanceof FileReference => (int)$value->getOriginalResource()->getOriginalFile()->getUid(),
            ($configuration['poiCollection'] ?? false) === true => is_array($value) ? $value : [],
            is_array($value) => implode(',', $value),
            ($configuration['castToInteger'] ?? false) === true => is_scalar($value) ? (int)$value : 0,
            ($configuration['convertPlainTextToHtml'] ?? false) === true
                => $this->rteContentConverter->convertPlainTextToHtml($this->toString($value)),
            ($configuration['normalizePlainText'] ?? false) === true
                => $this->rteContentConverter->normalizePlainText($this->toString($value)),
            default => trim($this->toString($value)),
        };
    }

    /**
     * A manipulated request could submit values, which are not an option of
     * a select box or of checkboxes. These values are removed.
     */
    protected function restrictToOptions(mixed $value, FormElementInterface $element): mixed
    {
        $options = $element->getProperties()['options'] ?? null;
        if (!is_array($options) || $value === null || $value === '') {
            return $value;
        }

        $isOption = static fn(mixed $item): bool => is_scalar($item) && array_key_exists((string)$item, $options);
        if (is_array($value)) {
            return array_values(array_filter($value, $isOption));
        }

        return $isOption($value) ? $value : '';
    }

    /**
     * An edited club keeps its page and creation date. The hidden step
     * updates the club of its "whereClause".
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function prepareClub(array $data, int $step): array
    {
        $clubUid = $this->clubAccessService->getClubUidFromQuery($this->finisherContext->getRequest());
        $this->addVariable('isNewClub', $clubUid === 0);
        if ($clubUid > 0) {
            $this->options['mode'] = 'update';
            $this->options['whereClause'] = ['uid' => $clubUid];
            unset($data['pid'], $data['crdate']);
        }

        $uid = ($this->options['mode'] ?? 'insert') === 'update' ? (int)$this->parseOption('whereClause.uid') : 0;
        if ($uid > 0) {
            $this->addVariable('insertedUids.' . $step, $uid);
            $this->addVariable(self::VARIABLE_CLUB_UID, $uid);
        } elseif (isset($data['pid'])) {
            $data['pid'] = $this->toInteger($data['pid']);
        }

        return $data;
    }

    /**
     * Like the DataHandler: a club gets a path segment, if it has none.
     * The moderation token signs the "tstamp" of the club, so it is
     * generated after the club was stored.
     *
     * @param array<string, mixed> $data
     */
    protected function finishClub(array $data): void
    {
        $club = $this->clubFormRecordService->getClubRecord($this->getClubUid());
        if ($club === []) {
            return;
        }

        if (isset($data['title']) && trim((string)($club['path_segment'] ?? '')) === '') {
            $this->getConnection(ClubFormRecordService::TABLE_CLUB)->update(
                ClubFormRecordService::TABLE_CLUB,
                ['path_segment' => $this->pathSegmentHelper->generatePathSegment($club, (int)$club['pid'])],
                ['uid' => (int)$club['uid']],
            );
        }

        $this->addVariable('moderationToken', $this->clubAccessService->generateModerationToken((int)$club['uid']));
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function saveCategories(array $data): array
    {
        $clubUid = $this->toInteger($data['uid_foreign'] ?? 0);
        $connection = $this->getConnection(self::TABLE_CATEGORY_MM);
        $connection->delete(self::TABLE_CATEGORY_MM, [
            'uid_foreign' => $clubUid,
            'tablenames' => $data['tablenames'] ?? '',
            'fieldname' => $data['fieldname'] ?? '',
        ]);

        $categoryUids = array_filter(array_map('intval', explode(',', $this->toString($data['categories'] ?? ''))));
        foreach (array_values($categoryUids) as $sorting => $categoryUid) {
            $connection->insert(self::TABLE_CATEGORY_MM, [
                'uid_local' => $categoryUid,
                'uid_foreign' => $clubUid,
                'tablenames' => $data['tablenames'] ?? '',
                'fieldname' => $data['fieldname'] ?? '',
                'sorting_foreign' => $sorting + 1,
            ]);
        }

        $this->updateClub($clubUid, ['categories' => count($categoryUids)]);

        return [];
    }

    /**
     * The frontend user, who creates the club, gets access to it. The users
     * of an edited club are kept. Else a backend administrator or the
     * moderator would remove the owner of the club.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function saveFrontendUser(array $data): array
    {
        $clubUid = $this->toInteger($data['uid_local'] ?? 0);
        $frontendUserUid = $this->toInteger($data['uid_foreign'] ?? 0);
        if ($this->clubAccessService->getClubUidFromQuery($this->finisherContext->getRequest()) > 0
            || $clubUid === 0
            || $frontendUserUid === 0
        ) {
            return [];
        }

        $this->getConnection(self::TABLE_USER_MM)->insert(self::TABLE_USER_MM, [
            'uid_local' => $clubUid,
            'uid_foreign' => $frontendUserUid,
            'sorting' => 1,
        ]);
        $this->updateClub($clubUid, ['fe_users' => 1]);

        return [];
    }

    /**
     * An address without address type is removed. The position of the form
     * element "Maps2PoiPosition" is stored in the POI collection of the address.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function prepareAddress(array $data): array
    {
        $storedAddress = $this->clubFormRecordService->getAddressRecord(
            $this->toInteger($data['club'] ?? 0),
            $this->toInteger($this->parseOption('position')),
        );
        $position = $data['tx_maps2_uid'] ?? [];
        unset($data['tx_maps2_uid']);

        if ($this->toString($data['title'] ?? '') === '') {
            $this->deleteAddress($storedAddress);
            return [];
        }

        if ($storedAddress !== []) {
            $this->options['mode'] = 'update';
            $this->options['whereClause'] = ['uid' => (int)$storedAddress['uid']];
            unset($data['pid'], $data['crdate'], $data['club']);
        }

        return $this->addPoiCollectionToAddress($data, $storedAddress, $position);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $storedAddress
     * @return array<string, mixed>
     */
    protected function addPoiCollectionToAddress(array $data, array $storedAddress, mixed $position): array
    {
        $poiCollectionUid = $this->poiCollectionService->savePosition(
            $this->toInteger($storedAddress['tx_maps2_uid'] ?? 0),
            $position,
            $this->toString($this->finisherContext->getFormValues()['title'] ?? ''),
            $this->geocodingService->formatAddress(
                $this->toString($data['street'] ?? ''),
                $this->toString($data['house_number'] ?? ''),
                $this->toString($data['zip'] ?? ''),
                $this->toString($data['city'] ?? ''),
            ),
        );

        if ($poiCollectionUid > 0) {
            $data['tx_maps2_uid'] = $poiCollectionUid;
        }
        if (isset($data['pid'])) {
            $data['pid'] = $this->toInteger($data['pid']);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $storedAddress
     */
    protected function deleteAddress(array $storedAddress): void
    {
        if ($storedAddress === []) {
            return;
        }

        $this->getConnection(ClubFormRecordService::TABLE_ADDRESS)->update(
            ClubFormRecordService::TABLE_ADDRESS,
            ['deleted' => 1, 'tstamp' => time()],
            ['uid' => (int)$storedAddress['uid']],
        );
        $this->poiCollectionService->deletePoiCollection($this->toInteger($storedAddress['tx_maps2_uid'] ?? 0));
    }

    /**
     * A new file replaces the file reference at the position. The image
     * upload of EXT:form can not remove a file, so an empty value keeps the
     * stored file reference.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function prepareFileReference(array $data): array
    {
        $clubUid = $this->toInteger($data['uid_foreign'] ?? 0);
        $fileUid = $this->toInteger($data['uid_local'] ?? 0);
        $position = $this->toInteger($this->parseOption('position'));
        $storedReference = $this->clubFormRecordService->getFileReferenceRecord(
            $clubUid,
            $this->toString($data['fieldname'] ?? ''),
            $position,
        );
        if ($fileUid === 0 || $fileUid === $this->toInteger($storedReference['uid_local'] ?? 0)) {
            return [];
        }

        if ($storedReference !== []) {
            $this->options['mode'] = 'update';
            $this->options['whereClause'] = ['uid' => (int)$storedReference['uid']];
            return ['uid_local' => $fileUid, 'tstamp' => $data['tstamp'] ?? time()];
        }

        $data['pid'] = $this->toInteger($this->clubFormRecordService->getClubRecord($clubUid)['pid'] ?? 0);
        $data['sorting_foreign'] = $position;

        return $data;
    }

    /**
     * The file columns of the club contain the number of file references
     */
    protected function updateNumberOfFiles(string $fieldName): void
    {
        $clubUid = $this->getClubUid();
        if ($clubUid > 0 && $fieldName !== '') {
            $this->updateClub($clubUid, [$fieldName => $this->clubFormRecordService->countFileReferences($clubUid, $fieldName)]);
        }
    }

    /**
     * Inserts or updates the record. The "tstamp" of the club is not changed
     * by these updates, so the moderation token stays valid.
     *
     * @param array<string, mixed> $data
     */
    protected function saveToDatabase(array $data, string $table, int $step): void
    {
        if ($data === [] || $table === '') {
            return;
        }

        $connection = $this->getConnection($table);
        if (($this->options['mode'] ?? 'insert') === 'update') {
            $connection->update($table, $data, ['uid' => $this->toInteger($this->parseOption('whereClause.uid'))]);
            return;
        }

        $connection->insert($table, $data);
        $uid = (int)$connection->lastInsertId();
        $this->addVariable('insertedUids.' . $step, $uid);
        if ($table === ClubFormRecordService::TABLE_CLUB) {
            $this->addVariable(self::VARIABLE_CLUB_UID, $uid);
        }
    }

    /**
     * @param array<string, int> $values
     */
    protected function updateClub(int $clubUid, array $values): void
    {
        $this->getConnection(ClubFormRecordService::TABLE_CLUB)->update(
            ClubFormRecordService::TABLE_CLUB,
            $values,
            ['uid' => $clubUid],
        );
    }

    /**
     * "{__currentFrontendUser}" is the UID of the logged-in frontend user
     *
     * @return int|string|array<int|string, mixed>
     */
    protected function resolveRuntimeReference(string $property, FormRuntime $formRuntime)
    {
        if ($property === '__currentFrontendUser') {
            return (int)$this->context->getPropertyFromAspect('frontend.user', 'id', 0);
        }

        return parent::resolveRuntimeReference($property, $formRuntime);
    }

    protected function getClubUid(): int
    {
        $variableProvider = $this->finisherContext->getFinisherVariableProvider();

        return $this->toInteger($variableProvider->get($this->shortFinisherIdentifier, self::VARIABLE_CLUB_UID, 0));
    }

    protected function addVariable(string $key, mixed $value): void
    {
        $this->finisherContext->getFinisherVariableProvider()->add($this->shortFinisherIdentifier, $key, $value);
    }

    protected function getConnection(string $table): Connection
    {
        return $this->connectionPool->getConnectionForTable($table);
    }

    /**
     * A list like "12,13" (e.g. a storage folder setting) results in 12
     */
    protected function toInteger(mixed $value): int
    {
        return is_scalar($value) ? (int)$value : 0;
    }

    protected function toString(mixed $value): string
    {
        return is_scalar($value) ? trim((string)$value) : '';
    }
}
