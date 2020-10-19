<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Property\TypeConverter;

use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Property\Exception\TypeConverterException;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Converter to upload multiple images for one property
 */
class UploadMultipleFilesConverter extends AbstractTypeConverter
{
    /**
     * @var array<string>
     */
    protected $sourceTypes = ['array'];

    /**
     * @var string
     */
    protected $targetType = ObjectStorage::class;

    /**
     * @var int
     */
    protected $priority = 2;

    /**
     * @var ResourceFactory
     */
    protected $fileFactory;

    /**
     * inject fileFactory
     *
     * @param ResourceFactory $fileFactory
     */
    public function injectFileFactory(ResourceFactory $fileFactory)
    {
        $this->fileFactory = $fileFactory;
    }

    /**
     * This implementation always returns TRUE for this method.
     *
     * @param mixed  $source     the source data
     * @param string $targetType the type to convert to.
     *
     * @return bool TRUE if this TypeConverter can convert from $source to $targetType, FALSE otherwise.
     */
    public function canConvertFrom($source, $targetType): bool
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

    /**
     * Actually convert from $source to $targetType, taking into account the fully
     * built $convertedChildProperties and $configuration.
     *
     * The return value can be one of three types:
     * - an arbitrary object, or a simple type (which has been created while mapping).
     *   This is the normal case.
     * - NULL, indicating that this object should *not* be mapped
     *      (i.e. a "File Upload" Converter could return NULL if no file has been uploaded,
     *      and a silent failure should occur.
     * - An instance of \TYPO3\CMS\Extbase\Error\Error -- This will be a user-visible error message later on.
     * Furthermore, it should throw an Exception if an unexpected failure
     *      (like a security error) occurred or a configuration issue happened.
     *
     * @param mixed $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param PropertyMappingConfigurationInterface $configuration
     *
     * @return mixed|Error the target type, or an error object if a user-error occurred
     *
     * @throws TypeConverterException thrown in case a developer error occurred
     */
    public function convertFrom(
        $source,
        $targetType,
        array $convertedChildProperties = [],
        PropertyMappingConfigurationInterface $configuration = null
    ) {
        /** @var array|\TYPO3\CMS\Extbase\Domain\Model\FileReference[] $alreadyPersistedImages */
        $alreadyPersistedImages = [];

        if ($configuration) {
            $alreadyPersistedImages = $configuration->getConfigurationValue(
                __CLASS__,
                'IMAGES'
            );
        }
        $originalSource = $source;
        foreach ($originalSource as $key => $uploadedFile) {
            // If file is not valid use file from persisted record
            if (!$this->isValidUploadFile($uploadedFile)) {
                if (isset($uploadedFile['delete']) && $uploadedFile['delete'] === '1') {
                    // Delete sys_file, delete reference
                    $this->deleteFile($alreadyPersistedImages[$key]);
                    $alreadyPersistedImages->detach($alreadyPersistedImages[$key]);
                    unset($source[$key]);
                } elseif ($alreadyPersistedImages[$key] !== null) {
                    // Take image from persisted record
                    $source[$key] = $alreadyPersistedImages[$key];
                } else {
                    // Remove empty upload file array from source
                    unset($source[$key]);
                }
                continue;
            }

            // check if uploaded file returns an error
            if (!$uploadedFile['error'] === 0) {
                return new Error(
                    LocalizationUtility::translate('error.upload', 'clubdirectory') . $uploadedFile['error'],
                    1396957314
                );
            }

            // now we have a valid uploaded file. Check if user has rights to upload this file
            if (!isset($uploadedFile['rights']) || empty($uploadedFile['rights'])) {
                return new Error(
                    LocalizationUtility::translate('error.uploadRights', 'clubdirectory'),
                    1397464390
                );
            }

            // check if file extension is allowed
            $fileParts = GeneralUtility::split_fileref($uploadedFile['name']);
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

            $this->deleteFile($alreadyPersistedImages[$key]);
        }

        // I will do two foreach here. First: everything must be OK, before files will be uploaded

        // upload file and add it to ObjectStorage
        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $references */
        $references = $this->objectManager->get(ObjectStorage::class);
        foreach ($source as $uploadedFile) {
            if ($uploadedFile instanceof FileReference) {
                $references->attach($uploadedFile);
            } else {
                $references->attach($this->getExtbaseFileReference($uploadedFile));
            }
        }

        return $references;
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
        } elseif (!isset(
            $uploadedFile['error'],
            $uploadedFile['name'],
            $uploadedFile['size'],
            $uploadedFile['tmp_name'],
            $uploadedFile['type']
        )) {
            return false;
        }
        return true;
    }

    /**
     * If file is in our own upload folder we can delete it from filesystem and sys_file table.
     *
     * @param FileReference|null $fileReference
     */
    protected function deleteFile(\TYPO3\CMS\Extbase\Domain\Model\FileReference $fileReference = null)
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
     * @return FileReference|object
     */
    protected function getExtbaseFileReference($source)
    {
        /** @var $reference \TYPO3\CMS\Extbase\Domain\Model\FileReference */
        $extbaseFileReference = $this->objectManager->get(FileReference::class);
        $extbaseFileReference->setOriginalResource($this->getCoreFileReference($source));

        return $extbaseFileReference;
    }

    /**
     * upload file and get a file reference object.
     *
     * @param array $source
     * @return \TYPO3\CMS\Core\Resource\FileReference
     */
    protected function getCoreFileReference(array $source): \TYPO3\CMS\Core\Resource\FileReference
    {
        // upload file
        $uploadFolder = ResourceFactory::getInstance()->retrieveFileOrFolderObject('uploads/tx_clubdirectory/');
        $uploadedFile = $uploadFolder->addUploadedFile($source, \TYPO3\CMS\Core\Resource\DuplicationBehavior::RENAME);
        // create Core FileReference
        return ResourceFactory::getInstance()->createFileReferenceObject(
            [
                'uid_local' => $uploadedFile->getUid(),
                'uid_foreign' => \uniqid('NEW_', true),
                'uid' => \uniqid('NEW_', true)
            ]
        );
    }
}
