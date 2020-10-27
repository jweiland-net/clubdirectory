<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Property\TypeConverter;

use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/*
 * A for PropertyMapper to convert one file upload into an array
 */
class UploadOneFileConverter extends AbstractTypeConverter
{
    /**
     * @var array<string>
     */
    protected $sourceTypes = ['array'];

    /**
     * @var string
     */
    protected $targetType = FileReference::class;

    /**
     * @var int
     */
    protected $priority = 2;

    /**
     * @var PropertyMappingConfigurationInterface
     */
    protected $converterConfiguration = [];

    /**
     * This implementation always returns TRUE for this method.
     *
     * @param mixed  $source     the source data
     * @param string $targetType the type to convert to.
     * @return bool true if this TypeConverter can convert from $source to $targetType, FALSE otherwise.
     */
    public function canConvertFrom($source, string $targetType): bool
    {
        // check if $source consists of uploaded files
        foreach ($source as $uploadedFile) {
            if (!isset(
                $uploadedFile['error'],
                $uploadedFile['name'],
                $uploadedFile['size'],
                $uploadedFile['tmp_name'],
                $uploadedFile['type']
            )) {
                return false;
            }
        }

        return true;
    }

    public function convertFrom(
        $source,
        string $targetType,
        array $convertedChildProperties = [],
        PropertyMappingConfigurationInterface $configuration = null
    ) {
        $alreadyPersistedImage = null;
        $this->converterConfiguration = $configuration;

        if ($configuration) {
            /** @var FileReference $alreadyPersistedImage */
            $alreadyPersistedImage = $configuration->getConfigurationValue(
                __CLASS__,
                'IMAGE'
            );
        }

        // if no file was uploaded use the already persisted one
        if (!$this->isValidUploadFile($source)) {
            if (isset($source['delete']) && $source['delete'] === '1') {
                // Delete sys_file, delete reference
                $this->deleteFile($alreadyPersistedImage);
                return null;
            }
            // Take image from persisted record. Can also be NULL
            return $alreadyPersistedImage;
        }

        // check if uploaded file returns an error
        if ($source['error'] !== 0) {
            return new Error(
                LocalizationUtility::translate('error.upload', 'clubdirectory') . $source['error'],
                1396957314
            );
        }

        // check if file extension is allowed
        $fileParts = GeneralUtility::split_fileref($source['name']);
        if (!GeneralUtility::inList($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'], $fileParts['fileext'])) {
            return new Error(
                LocalizationUtility::translate(
                    'error.fileExtension',
                    'clubdirectory',
                    [
                        $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
                    ]
                ),
                1402981282
            );
        }

        $this->deleteFile($alreadyPersistedImage);

        return $this->getExtbaseFileReference($source);
    }

    /**
     * Check, if we have a valid uploaded file
     * Error = 4: No file uploaded
     *
     * @param array $uploadedFile
     * @return bool
     */
    protected function isValidUploadFile(array $uploadedFile): bool
    {
        if ($uploadedFile['error'] === 4) {
            return false;
        }

        return isset(
            $uploadedFile['error'],
            $uploadedFile['name'],
            $uploadedFile['size'],
            $uploadedFile['tmp_name'],
            $uploadedFile['type']
        );
    }

    /**
     * If file is in our own upload folder we can delete it from filesystem and sys_file table.
     *
     * @param FileReference|null $fileReference
     */
    protected function deleteFile(?FileReference $fileReference)
    {
        if ($fileReference !== null) {
            $fileReference = $fileReference->getOriginalResource();

            if (
                $fileReference->getParentFolder()->getName() === 'tx_clubdirectory'
                && $fileReference->getParentFolder()->getParentFolder()->getName() === 'uploads'
            ) {
                try {
                    $fileReference->getOriginalFile()->delete();
                } catch (\Exception $exception) {
                    // Do nothing. File already deleted or not found
                }
            }
        }
    }

    /**
     * upload file and get a file reference object.
     *
     * @param array $source
     * @return FileReference
     */
    protected function getExtbaseFileReference(array $source): FileReference
    {
        $extbaseFileReference = GeneralUtility::makeInstance(FileReference::class);
        $extbaseFileReference->setOriginalResource($this->getCoreFileReference($source));

        return $extbaseFileReference;
    }

    /**
     * Upload file and get a file reference object.
     *
     * @param array $source
     * @return \TYPO3\CMS\Core\Resource\FileReference
     */
    protected function getCoreFileReference(array $source): \TYPO3\CMS\Core\Resource\FileReference
    {
        $settings = $this->converterConfiguration->getConfigurationValue(
            self::class,
            'settings'
        ) ?? [];

        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        $uploadFolderIdentifier = $settings['new']['uploadFolder'] ?? '';

        try {
            $uploadFolder = $resourceFactory->getFolderObjectFromCombinedIdentifier($uploadFolderIdentifier);
        } catch (FolderDoesNotExistException $e) {
            [$storageUid, $identifier] = GeneralUtility::trimExplode(':', $uploadFolderIdentifier);
            try {
                $storage = $resourceFactory->getStorageObject($storageUid);
            } catch (\InvalidArgumentException $e) {
                $storage = $resourceFactory->getDefaultStorage();
                $identifier = $uploadFolderIdentifier;
            }
            $uploadFolder = $storage->createFolder($identifier);
        }

        $uploadedFile = $uploadFolder->addUploadedFile($source, DuplicationBehavior::RENAME);

        // create Core FileReference
        return $resourceFactory->createFileReferenceObject(
            [
                'uid_local' => $uploadedFile->getUid(),
                'uid_foreign' => uniqid('NEW_'),
                'uid' => uniqid('NEW_'),
            ]
        );
    }
}
