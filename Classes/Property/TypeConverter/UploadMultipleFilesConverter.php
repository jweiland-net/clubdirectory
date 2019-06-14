<?php
declare(strict_types=1);
namespace JWeiland\Clubdirectory\Property\TypeConverter;

/*
 * This file is part of the clubdirectory project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Property\Exception\TypeConverterException;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class UploadMultipleFilesConverter
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
     * @return void
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
     *
     * @api
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
        $alreadyPersistedImages = null;

        if ($configuration) {
            $alreadyPersistedImages = $configuration->getConfigurationValue(
                __CLASS__,
                'IMAGES'
            );
        }
        $originalSource = $source;
        foreach ($originalSource as $key => $uploadedFile) {
            // check if $source contains an uploaded file. 4 = no file uploaded
            if ($uploadedFile['error'] === 4
                ||! isset(
                    $uploadedFile['error'],
                    $uploadedFile['name'],
                    $uploadedFile['size'],
                    $uploadedFile['tmp_name'],
                    $uploadedFile['type']
                )
            ) {
                if ($alreadyPersistedImages[$key] !== null) {
                    $source[$key] = $alreadyPersistedImages[$key];
                } else {
                    unset($source[$key]);
                }
                continue;
            }
            // check if uploaded file returns an error
            if (!$uploadedFile['error'] === 0) {
                return new Error(
                    LocalizationUtility::translate('error.upload', 'clubdirectory').$uploadedFile['error'],
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
            // OK...we have a valid file and the user has the rights. It's time to check, if an old file can be deleted
            if ($alreadyPersistedImages[$key] instanceof FileReference) {
                /** @var \TYPO3\CMS\Extbase\Domain\Model\FileReference $oldFile */
                $oldFile = $alreadyPersistedImages[$key];
                $oldFile->getOriginalResource()->getOriginalFile()->delete();
            }
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
     * upload file and get a file reference object.
     *
     * @param array  $source
     *
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
     *
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
