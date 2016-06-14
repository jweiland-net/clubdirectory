<?php

namespace JWeiland\Clubdirectory\Domain\Validator;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Validator for club domain models.
 */
class ClubValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{
    /**
     * This validator always needs to be executed even if the given value is empty.
     * See AbstractValidator::validate().
     *
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    /**
     * Checks if the given property ($propertyValue) is not empty (NULL, empty string, empty array or empty object).
     *
     * If at least one error occurred, the result is FALSE.
     *
     * @param mixed $value The value that should be validated
     *
     * @return bool TRUE if the value is valid, FALSE if an error occurred
     */
    public function isValid($value)
    {
        /* @var $value \JWeiland\Clubdirectory\Domain\Model\Club */
        $this->removeEmptyAddresses($value);
        $addresses = $value->getAddresses();
        if (is_object($addresses) && $addresses instanceof \Countable && $addresses->count() === 0) {
            $this->addError('You have forgotten to set at lease one address', 1399904889);
        }
    }

    /**
     * For our form we have to add all 3 addresses. Filled or not filled.
     * But it's bad to add these empty addresses into DB. So we remove empty addresses here.
     *
     * @param \JWeiland\Clubdirectory\Domain\Model\Club $club
     */
    protected function removeEmptyAddresses(\JWeiland\Clubdirectory\Domain\Model\Club $club)
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var \JWeiland\Clubdirectory\Domain\Model\Address $emptyAddress */
        $emptyAddress = $objectManager->get('JWeiland\\Clubdirectory\\Domain\\Model\\Address');
        $addresses = clone $club->getAddresses();
        /** @var \JWeiland\Clubdirectory\Domain\Model\Address $address */
        foreach ($addresses as $address) {
            if ($address == $emptyAddress) {
                $club->removeAddress($address);
            }
        }
    }
}
