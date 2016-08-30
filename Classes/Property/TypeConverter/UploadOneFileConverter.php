<?php

namespace JWeiland\Clubdirectory\Property\TypeConverter;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2014 Stefan Froemken <froemken@gmail.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Converter for uploads.
 */
class UploadOneFileConverter extends \TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter
{
    /**
     * @var array<string>
     */
    protected $sourceTypes = array('array');

    /**
     * @var string
     */
    protected $targetType = 'TYPO3\\CMS\\Extbase\\Domain\\Model\\FileReference';

    /**
     * @var int
     */
    protected $priority = 2;

    /**
     * @var \TYPO3\CMS\Core\Resource\ResourceFactory
     * @inject
     */
    protected $fileFactory;
    
    /**
     * Actually convert from $source to $targetType, taking into account the fully
     * built $convertedChildProperties and $configuration.
     *
     * The return value can be one of three types:
     * - an arbitrary object, or a simple type (which has been created while mapping).
     *   This is the normal case.
     * - NULL, indicating that this object should *not* be mapped (i.e. a "File Upload" Converter could return NULL if no file has been uploaded, and a silent failure should occur.
     * - An instance of \TYPO3\CMS\Extbase\Error\Error -- This will be a user-visible error message later on.
     * Furthermore, it should throw an Exception if an unexpected failure (like a security error) occurred or a configuration issue happened.
     *
     * @param mixed $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
     *
     * @return mixed|\TYPO3\CMS\Extbase\Error\Error the target type, or an error object if a user-error occurred
     *
     * @throws \TYPO3\CMS\Extbase\Property\Exception\TypeConverterException thrown in case a developer error occurred
     */
    public function convertFrom(
        $source,
        $targetType,
        array $convertedChildProperties = array(),
        \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = null
    ) {
        /** @var \TYPO3\CMS\Extbase\Domain\Model\FileReference $alreadyPersistedImage */
        $alreadyPersistedImage = $configuration->getConfigurationValue(
            'JWeiland\\Clubdirectory\\Property\\TypeConverter\\UploadOneFileConverter',
            'IMAGE'
        );

        // if no file was uploaded use the already persisted one
        if (
            !isset($source['error']) ||
            !isset($source['name']) ||
            !isset($source['size']) ||
            !isset($source['tmp_name']) ||
            !isset($source['type']) ||
            $source['error'] === 4
        ) {
            return $alreadyPersistedImage;
        }
        // check if uploaded file returns an error
        if ($source['error'] !== 0) {
            return new \TYPO3\CMS\Extbase\Error\Error(
                LocalizationUtility::translate('error.upload', 'clubdirectory').$source['error'],
                1396957314
            );
        }
        // now we have a valid uploaded file. Check if user has rights to upload this file
        if (!isset($source['rights']) || empty($source['rights'])) {
            return new \TYPO3\CMS\Extbase\Error\Error(
                LocalizationUtility::translate('error.uploadRights', 'clubdirectory'),
                1397464390
            );
        }
        // check if file extension is allowed
        $fileParts = GeneralUtility::split_fileref($source['name']);
        if (!GeneralUtility::inList($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'], $fileParts['fileext'])) {
            return new \TYPO3\CMS\Extbase\Error\Error(
                LocalizationUtility::translate(
                    'error.fileExtension',
                    'clubdirectory',
                    array(
                        $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
                    )
                ),
                1402981282
            );
        }

        // before uploading the new file we should remove the old one
        if ($alreadyPersistedImage instanceof \TYPO3\CMS\Extbase\Domain\Model\FileReference) {
            $alreadyPersistedImage->getOriginalResource()->delete();
        }

        return $this->getExtbaseFileReference($source);
    }

    /**
     * upload file and get a file reference object.
     *
     * @param array  $source
     *
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    protected function getExtbaseFileReference($source)
    {
        /** @var $reference \TYPO3\CMS\Extbase\Domain\Model\FileReference */
        $extbaseFileReference = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Domain\\Model\\FileReference');
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
    protected function getCoreFileReference(array $source)
    {
        // upload file
        $uploadFolder = ResourceFactory::getInstance()->retrieveFileOrFolderObject('uploads/tx_clubdirectory/');
        $uploadedFile = $uploadFolder->addUploadedFile($source, 'changeName');
        // create Core FileReference
        return ResourceFactory::getInstance()->createFileReferenceObject(
            array(
                'uid_local' => $uploadedFile->getUid(),
                'uid_foreign' => uniqid('NEW_'),
                'uid' => uniqid('NEW_'),
            )
        );
    }
}
