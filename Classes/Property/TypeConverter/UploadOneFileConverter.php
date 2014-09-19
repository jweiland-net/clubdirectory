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
class UploadOneFileConverter extends \TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter {

	/**
	 * @var array<string>
	 */
	protected $sourceTypes = array('array');

	/**
	 * @var string
	 */
	protected $targetType = 'JWeiland\\Clubdirectory\\Domain\\Model\\FileReference';

	/**
	 * @var integer
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
	 * @param array $source
	 * @param string $targetType
	 * @param array $convertedChildProperties
	 * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
	 * @throws \TYPO3\CMS\Extbase\Property\Exception
	 * @return \TYPO3\CMS\Extbase\Domain\Model\AbstractFileFolder
	 * @api
	 */
	public function convertFrom($source, $targetType, array $convertedChildProperties = array(), \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
		/** @var \JWeiland\Clubdirectory\Domain\Model\Filereference $alreadyPersistedImage */
		$alreadyPersistedImage = $configuration->getConfigurationValue('JWeiland\\Clubdirectory\\Property\\TypeConverter\\UploadOneFileConverter', 'IMAGE');

		// if no file was uploaded use the already persisted one
		if (!isset($source['error']) || !isset($source['name']) || !isset($source['size']) || !isset($source['tmp_name']) || !isset($source['type']) || $source['error'] === 4) {
			return $alreadyPersistedImage;
		}
		// check if uploaded file returns an error
		if ($source['error'] !== 0) {
			return new \TYPO3\CMS\Extbase\Error\Error(LocalizationUtility::translate('error.upload', 'clubdirectory') . $source['error'], 1396957314);
		}
		// now we have a valid uploaded file. Check if user has rights to upload this file
		if (!isset($source['rights']) || empty($source['rights'])) {
			return new \TYPO3\CMS\Extbase\Error\Error(LocalizationUtility::translate('error.uploadRights', 'clubdirectory'), 1397464390);
		}
		// check if file extension is allowed
		$fileParts = GeneralUtility::split_fileref($source['name']);
		if (!GeneralUtility::inList($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'], $fileParts['fileext'])) {
			return new \TYPO3\CMS\Extbase\Error\Error(LocalizationUtility::translate('error.fileExtension', 'clubdirectory', array($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'])), 1402981282);
		}

		// before uploading the new file we should remove the old one
		if ($alreadyPersistedImage instanceof \JWeiland\Clubdirectory\Domain\Model\Filereference) {
			$alreadyPersistedImage->getOriginalResource()->delete();
		}

		// upload new file
		$storage = ResourceFactory::getInstance()->getStorageObject(0);
		$uploadedFile = $storage->addUploadedFile($source, $storage->getFolder('uploads/tx_clubdirectory/'), $this->getTargetFileName($source), 'changeName');
		if (!$uploadedFile instanceof \TYPO3\CMS\Core\Resource\File) {
			throw new \TYPO3\CMS\Extbase\Property\Exception('Uploaded file is not of type \\TYPO3\\CMS\\Core\\Resource\\File', 1396963537);
		}

		// create new reference
		/** @var $reference \JWeiland\Clubdirectory\Domain\Model\FileReference */
		$reference = $this->objectManager->get($targetType);
		$reference->setTablenames($configuration->getConfigurationValue('JWeiland\\Clubdirectory\\Property\\TypeConverter\\UploadOneFileConverter', 'TABLENAME'));
		$reference->setTitle($uploadedFile->getName());
		$reference->setUidLocal($uploadedFile->getUid());
		$reference->setOriginalResource($uploadedFile);

		return $reference;
	}

	/**
	 * Gets a Folder object from an identifier
	 *
	 * @param string $identifier
	 * @return \TYPO3\CMS\Core\Resource\Folder|\TYPO3\CMS\Core\Resource\File
	 * @throws \TYPO3\CMS\Core\Resource\Exception\InvalidFileException
	 */
	protected function getFolderObject($identifier) {
		$object = $this->fileFactory->retrieveFileOrFolderObject($identifier);
		if (!is_object($object)) {
			throw new \TYPO3\CMS\Core\Resource\Exception\InvalidFileException('The item ' . $identifier . ' was not a file or directory!!', 1320122453);
		}
		return $object;
	}

	/**
	 * creates a target filename
	 * orig: dog.PNg --> dog_35268592817.png
	 *
	 * @param array $source
	 * @return string
	 */
	protected function getTargetFileName(array $source) {
		$fileParts = GeneralUtility::split_fileref($source['name']);
		return $fileParts['filebody'] . '_' . time() . '.' . $fileParts['fileext'];
	}

}
