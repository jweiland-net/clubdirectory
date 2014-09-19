<?php
namespace JWeiland\Clubdirectory\ViewHelpers;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <projects@jweiland.net>, jweiland.net
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
use TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper;

/**
 * @package clubdirectory
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GrayScaleImageViewHelper extends ImageViewHelper {

	/**
	 * Resizes a given image (if required) and renders the respective img tag
	 *
	 * @see http://typo3.org/documentation/document-library/references/doc_core_tsref/4.2.0/view/1/5/#id4164427
	 * @param string $src
	 * @param FileInterface|AbstractFileFolder $image
	 * @param string $width width of the image. This can be a numeric value representing the fixed width of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
	 * @param string $height height of the image. This can be a numeric value representing the fixed height of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
	 * @param integer $minWidth minimum width of the image
	 * @param integer $minHeight minimum height of the image
	 * @param integer $maxWidth maximum width of the image
	 * @param integer $maxHeight maximum height of the image
	 * @param boolean $treatIdAsReference given src argument is a sys_file_reference record
	 *
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 * @return string Rendered tag
	 */
	public function render($src = NULL, $image = NULL, $width = NULL, $height = NULL, $minWidth = NULL, $minHeight = NULL, $maxWidth = NULL, $maxHeight = NULL, $treatIdAsReference = FALSE) {
		if (is_null($src) && is_null($image) || !is_null($src) && !is_null($image)) {
			throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('You must either specify a string src or a File object.', 1382284105);
		}
		$image = $this->imageService->getImage($src, $image, $treatIdAsReference);
		$processingInstructions = array(
			'width' => $width,
			'height' => $height,
			'minWidth' => $minWidth,
			'minHeight' => $minHeight,
			'maxWidth' => $maxWidth,
			'maxHeight' => $maxHeight,
			'additionalParameters' => '-colorspace GRAY'
		);
		$processedImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);
		$imageUri = $this->imageService->getImageUri($processedImage);

		$this->tag->addAttribute('src', $imageUri);
		$this->tag->addAttribute('width', $processedImage->getProperty('width'));
		$this->tag->addAttribute('height', $processedImage->getProperty('height'));

		$alt = $image->getProperty('alternative');
		$title = $image->getProperty('title');

		// The alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
		if (empty($this->arguments['alt'])) {
			$this->tag->addAttribute('alt', $alt);
		}
		if (empty($this->arguments['title']) && $title) {
			$this->tag->addAttribute('title', $title);
		}

		return $this->tag->render();
	}

}