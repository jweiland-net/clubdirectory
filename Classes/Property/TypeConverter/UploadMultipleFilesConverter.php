<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Property\TypeConverter;

use JWeiland\Checkfaluploads\Service\FalUploadService;
use JWeiland\Clubdirectory\Event\PostCheckFileReferenceEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Resource\Enum\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * A TypeConverter for PropertyMapper to convert multiple file uploads into an array
 *
 * NOTE: This TypeConverter must NOT be registered via Symfony DI with the tag "extbase.type_converter".
 * It is designed for explicit usage within the clubdirectory extension and should only be applied
 * to specific properties manually via PropertyMappingConfiguration.
 */
class UploadMultipleFilesConverter extends AbstractTypeConverter
{
    protected array|PropertyMappingConfigurationInterface $converterConfiguration = [];

    public function __construct(
        protected readonly EventDispatcher $eventDispatcher,
        protected readonly ResourceFactory $resourceFactory,
    ) {}

    public function convertFrom(
        $source,
        $targetType,
        array $convertedChildProperties = [],
        PropertyMappingConfigurationInterface $configuration = null,
    ) {
        $this->initialize($configuration);

        $filesToProcess = [];
        $rightsConfiguration = [];
        $uploadFolder = $this->createUploadFolder();

        foreach ($source as $sourceItem) {
            if ($sourceItem instanceof UploadedFile) {
                $filesToProcess[] = $sourceItem;
            } elseif (is_array($sourceItem)) {
                // Check if this array looks like the 'rights' container
                if (isset($sourceItem['rights'])) {
                    $rightsConfiguration[] = $sourceItem;
                }
            }
        }

        foreach ($filesToProcess as $key => $uploadedFile) {
            $alreadyPersistedImage = $this->getAlreadyPersistedFileReferenceByPosition(
                $this->getAlreadyPersistedImages(),
                $key,
            );

            // If no file was uploaded, use the already persisted one
            if (!$this->isValidUploadFile($uploadedFile)) {
                if (isset($uploadedFile['delete']) && $uploadedFile['delete'] === '1') {
                    $this->deleteFile($alreadyPersistedImage, $uploadFolder);
                    unset($source[$key]);
                } elseif ($alreadyPersistedImage instanceof FileReference) {
                    $source[$key] = $alreadyPersistedImage;
                } else {
                    unset($source[$key]);
                }

                continue;
            }

            // Check if an uploaded file returns an error
            if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
                return new Error(
                    LocalizationUtility::translate('error.upload', 'clubdirectory') . $uploadedFile->getError(),
                    1396957314,
                );
            }

            // Check if file extension is allowed
            $fileParts = GeneralUtility::split_fileref($uploadedFile->getClientFilename());
            if (!GeneralUtility::inList($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'], $fileParts['fileext'])) {
                return new Error(
                    LocalizationUtility::translate(
                        'error.fileExtension',
                        'clubdirectory',
                        [
                            $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
                        ],
                    ),
                    1402981282,
                );
            }

            $rights = null;
            if (isset($rightsConfiguration[$key]['rights'])) {
                $rights = $rightsConfiguration[$key];
            }

            if (
                ExtensionManagementUtility::isLoaded('checkfaluploads')
                && $error = $this->getFalUploadService()->checkFile($uploadedFile, $rights)
            ) {
                return $error;
            }

            $event = new PostCheckFileReferenceEvent($source, $key, $uploadedFile, $alreadyPersistedImage);
            if (
                ($error = $this->emitPostCheckFileReference($event))
                && $error instanceof Error
            ) {
                return $error;
            }
        }

        // Upload a file and add it to ObjectStorage
        $references = GeneralUtility::makeInstance(ObjectStorage::class);
        foreach ($source as $uploadedFile) {
            if ($uploadedFile instanceof FileReference) {
                $references->attach($uploadedFile);
            } elseif ($uploadedFile instanceof UploadedFile) {
                $references->attach($this->getExtbaseFileReference($uploadedFile, $uploadFolder));
            }
        }

        return $references;
    }

    protected function initialize(?PropertyMappingConfigurationInterface $configuration): void
    {
        if (!$configuration instanceof PropertyMappingConfigurationInterface) {
            throw new \InvalidArgumentException(
                'Missing PropertyMapper configuration in UploadMultipleFilesConverter',
                1604051720,
            );
        }

        $this->converterConfiguration = $configuration;
    }

    protected function getAlreadyPersistedImages(): ObjectStorage
    {
        $alreadyPersistedImages = $this->converterConfiguration->getConfigurationValue(
            self::class,
            'IMAGES',
        );

        return $alreadyPersistedImages instanceof ObjectStorage ? $alreadyPersistedImages : new ObjectStorage();
    }

    protected function getAlreadyPersistedFileReferenceByPosition(
        ObjectStorage $alreadyPersistedFileReferences,
        int $position,
    ): ?FileReference {
        return $alreadyPersistedFileReferences->toArray()[$position] ?? null;
    }

    protected function getTypoScriptPluginSettings(): array
    {
        $settings = $this->converterConfiguration->getConfigurationValue(
            self::class,
            'settings',
        );

        return $settings ?? [];
    }

    protected function createUploadFolder(): Folder
    {
        $combinedUploadFolderIdentifier = $this->getTypoScriptPluginSettings()['new']['uploadFolder'] ?? '';
        if ($combinedUploadFolderIdentifier === '') {
            throw new \InvalidArgumentException(
                'You have forgotten to set an Upload Folder in TypoScript for clubdirectory',
                1603808777,
            );
        }

        try {
            $uploadFolder = $this->resourceFactory->getFolderObjectFromCombinedIdentifier($combinedUploadFolderIdentifier);
        } catch (ResourceDoesNotExistException $resourceDoesNotExistException) {
            [$storageUid] = GeneralUtility::trimExplode(':', $combinedUploadFolderIdentifier);
            $resourceStorage = $this->resourceFactory->getStorageObject((int)$storageUid);
            $uploadFolder = $resourceStorage->createFolder($combinedUploadFolderIdentifier);
        }

        return $uploadFolder;
    }

    /**
     * Check if we have a valid uploaded file
     * Error = 4: No file uploaded
     */
    protected function isValidUploadFile(UploadedFile $uploadedFile): bool
    {

        // upload must be successful
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            return false;
        }

        // filename must exist
        if (trim((string)$uploadedFile->getClientFilename()) === '') {
            return false;
        }

        // size must be greater than 0
        if ($uploadedFile->getSize() === null || $uploadedFile->getSize() <= 0) {
            return false;
        }

        // temp file / stream must exist
        try {
            $uploadedFile->getStream();
        } catch (\RuntimeException) {
            return false;
        }

        return true;
    }

    /**
     * If a file is in our own upload folder, we can delete it from the filesystem and sys_file table.
     */
    protected function deleteFile(?FileReference $extbaseFileReference, Folder $uploadFolder): void
    {
        if ($extbaseFileReference instanceof FileReference) {
            $coreFileReference = $extbaseFileReference->getOriginalResource();

            if ($coreFileReference->getStorage()->isWithinFolder($uploadFolder, $coreFileReference)) {
                try {
                    $coreFileReference->getOriginalFile()->delete();
                } catch (\Exception $exception) {
                    // Do nothing. File already deleted or not found
                }
            }
        }
    }

    /**
     * Upload a file and get a file reference object.
     */
    protected function getExtbaseFileReference(UploadedFile $source, Folder $uploadFolder): FileReference
    {
        $extbaseFileReference = GeneralUtility::makeInstance(FileReference::class);
        $extbaseFileReference->setOriginalResource($this->getCoreFileReference($source, $uploadFolder));

        return $extbaseFileReference;
    }

    /**
     * Upload the file and get a file reference object.
     */
    protected function getCoreFileReference(UploadedFile $source, Folder $uploadFolder): \TYPO3\CMS\Core\Resource\FileReference
    {
        $uploadedFile = $uploadFolder->addUploadedFile($source, DuplicationBehavior::RENAME);

        // create Core FileReference
        return $this->resourceFactory->createFileReferenceObject(
            [
                'uid_local' => $uploadedFile->getUid(),
                'uid_foreign' => uniqid('NEW_', true),
                'uid' => uniqid('NEW_', true),
            ],
        );
    }

    protected function emitPostCheckFileReference(PostCheckFileReferenceEvent $event): ?Error
    {
        /** @var PostCheckFileReferenceEvent $modifiedEvent */
        $modifiedEvent = $this->eventDispatcher->dispatch($event);

        return $modifiedEvent->getError();
    }

    /**
     * Do not inject this service, as extension "checkfaluploads" may not be loaded
     */
    protected function getFalUploadService(): FalUploadService
    {
        return GeneralUtility::makeInstance(FalUploadService::class);
    }
}
