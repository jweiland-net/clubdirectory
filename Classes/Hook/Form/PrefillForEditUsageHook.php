<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Hook\Form;

use JWeiland\Clubdirectory\Converter\RteContentConverter;
use JWeiland\Clubdirectory\Form\FormOwner;
use JWeiland\Clubdirectory\Service\ClubAccessService;
use JWeiland\Clubdirectory\Service\ClubFormRecordService;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\FileReference as CoreFileReference;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;

/**
 * Prefills the elements of the club form based on EXT:form with the values
 * of the club to edit (query parameter "club" of the Management plugin).
 *
 * "properties.dbMapping" of an element defines its value:
 *
 * - column: A column of the club record
 * - column + address: A column of the address at the given position (1-3)
 * - address + dataType "poiCollection": Latitude and longitude of the POI
 *   collection of EXT:maps2 of the address
 * - column + dataType "file" + position: The file reference at the position
 * - relation.table + relation.valueColumn + relation.expressions: The values
 *   of all matching records. "{club:<column>}" is replaced by the column of
 *   the club.
 * - dataType "plainText": The HTML of the column is converted to plain text
 * - dataType "richText": The HTML of the column is used for the rich text
 *   editor. Fluid escapes it for the textarea.
 *
 * Elements of forms, which are not built by clubdirectory, are skipped.
 *
 * The form could be used outside the Management plugin (e.g. as form content
 * element). So the access is checked here, too. Else everyone could read
 * hidden clubs of other users.
 */
readonly class PrefillForEditUsageHook
{
    private const ALLOWED_EXPRESSIONS = ['eq', 'neq'];

    private const CACHE_IDENTIFIER_PREFIX = 'clubdirectory-form-club-';

    public function __construct(
        private ClubAccessService $clubAccessService,
        private ClubFormRecordService $clubFormRecordService,
        private ConnectionPool $connectionPool,
        private ResourceFactory $resourceFactory,
        private RteContentConverter $rteContentConverter,
        #[Autowire(service: 'cache.runtime')]
        private FrontendInterface $runtimeCache,
    ) {}

    /**
     * Called by EXT:form for each element after the form was built
     */
    public function afterBuildingFinished(RenderableInterface $renderable): void
    {
        $request = $this->getRequest();
        if (!$renderable instanceof FormElementInterface
            || !$request instanceof ServerRequestInterface
            || !FormOwner::isRenderableOfClubdirectoryForm($renderable)
        ) {
            return;
        }

        $dbMapping = $renderable->getProperties()['dbMapping'] ?? null;
        $club = is_array($dbMapping) ? $this->getClubToEdit($request) : [];
        $defaultValue = $club === [] ? null : $this->getDefaultValue($dbMapping, $club);
        if ($defaultValue !== null) {
            $renderable->setDefaultValue($defaultValue);
        }
    }

    /**
     * Returns an empty array for new clubs and on denied access. The hook is
     * called for each element, so the result is cached for the request.
     *
     * @return array<string, mixed>
     */
    protected function getClubToEdit(ServerRequestInterface $request): array
    {
        $clubUid = $this->clubAccessService->getClubUidFromQuery($request);
        if ($clubUid === 0) {
            return [];
        }

        $cacheIdentifier = self::CACHE_IDENTIFIER_PREFIX . $clubUid;
        $club = $this->runtimeCache->get($cacheIdentifier);
        if (!is_array($club)) {
            $club = $this->clubAccessService->isFormAccessAllowed($request)
                ? $this->clubFormRecordService->getClubRecord($clubUid)
                : [];
            $this->runtimeCache->set($cacheIdentifier, $club);
        }

        return $club;
    }

    /**
     * @param array<string, mixed> $dbMapping
     * @param array<string, mixed> $club
     */
    protected function getDefaultValue(array $dbMapping, array $club): mixed
    {
        $dataType = (string)($dbMapping['dataType'] ?? '');
        $column = (string)($dbMapping['column'] ?? '');

        return match (true) {
            isset($dbMapping['address']) => $this->getAddressValue($dbMapping, (int)$club['uid']),
            $dataType === 'file' => $this->getFileReference((int)$club['uid'], $column, (int)($dbMapping['position'] ?? 1)),
            is_array($dbMapping['relation'] ?? null) => $this->getRelationValues($dbMapping['relation'], $club),
            $column === '' || !array_key_exists($column, $club) => null,
            $dataType === 'plainText' => $this->rteContentConverter->convertHtmlToPlainText((string)$club[$column]),
            default => $this->convertToString($club[$column]),
        };
    }

    /**
     * @param array<string, mixed> $dbMapping
     */
    protected function getAddressValue(array $dbMapping, int $clubUid): mixed
    {
        $address = $this->clubFormRecordService->getAddressRecord($clubUid, (int)$dbMapping['address']);
        if ($address === []) {
            return null;
        }

        if (($dbMapping['dataType'] ?? '') === 'poiCollection') {
            return $this->getPosition((int)($address['tx_maps2_uid'] ?? 0));
        }

        $column = (string)($dbMapping['column'] ?? '');

        return array_key_exists($column, $address) ? $this->convertToString($address[$column]) : null;
    }

    /**
     * @return array{latitude: string, longitude: string}|null
     */
    protected function getPosition(int $poiCollectionUid): ?array
    {
        $poiCollection = $this->clubFormRecordService->getPoiCollectionRecord($poiCollectionUid);
        if ($poiCollection === []) {
            return null;
        }

        return [
            'latitude' => number_format((float)$poiCollection['latitude'], 6, '.', ''),
            'longitude' => number_format((float)$poiCollection['longitude'], 6, '.', ''),
        ];
    }

    protected function getFileReference(int $clubUid, string $fieldName, int $position): ?FileReference
    {
        $record = $this->clubFormRecordService->getFileReferenceRecord($clubUid, $fieldName, $position);
        if ($record === []) {
            return null;
        }

        $coreFileReference = $this->resourceFactory->getFileReferenceObject((int)$record['uid']);
        if (!$coreFileReference instanceof CoreFileReference) {
            return null;
        }

        // The ImageUpload element of EXT:form needs a file reference of Extbase
        $fileReference = GeneralUtility::makeInstance(FileReference::class);
        $fileReference->setOriginalResource($coreFileReference);

        return $fileReference;
    }

    /**
     * @param array<string, mixed> $relation
     * @param array<string, mixed> $club
     * @return list<string>|null
     */
    protected function getRelationValues(array $relation, array $club): ?array
    {
        $table = (string)($relation['table'] ?? '');
        $valueColumn = (string)($relation['valueColumn'] ?? '');
        if ($table === '' || $valueColumn === '' || !is_array($relation['expressions'] ?? null)) {
            return null;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->select($valueColumn)->from($table);
        foreach ($relation['expressions'] as $expression) {
            $this->addExpression($queryBuilder, $expression, $club);
        }

        return array_map($this->convertToString(...), $queryBuilder->executeQuery()->fetchFirstColumn());
    }

    /**
     * @param array<string, mixed> $club
     */
    protected function addExpression(QueryBuilder $queryBuilder, mixed $expression, array $club): void
    {
        if (!is_array($expression)
            || !isset($expression['column'], $expression['expression'], $expression['value'])
            || !in_array($expression['expression'], self::ALLOWED_EXPRESSIONS, true)
        ) {
            return;
        }

        $value = (string)$expression['value'];
        if (str_starts_with($value, '{club:')) {
            $value = $this->convertToString($club[substr($value, 6, -1)] ?? '');
        }

        $queryBuilder->andWhere($queryBuilder->expr()->{$expression['expression']}(
            (string)$expression['column'],
            $queryBuilder->createNamedParameter($value, Connection::PARAM_STR),
        ));
    }

    protected function convertToString(mixed $value): string
    {
        return is_scalar($value) ? (string)$value : '';
    }

    /**
     * EXT:form does not pass the request to this hook
     */
    protected function getRequest(): ?ServerRequestInterface
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;

        return $request instanceof ServerRequestInterface ? $request : null;
    }
}
