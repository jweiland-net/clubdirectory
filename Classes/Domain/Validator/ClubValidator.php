<?php
declare(strict_types = 1);
namespace JWeiland\Clubdirectory\Domain\Validator;

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

use JWeiland\Clubdirectory\Domain\Model\Address;
use JWeiland\Clubdirectory\Domain\Model\Club;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * We use the domain based validator to check against at least one address
 * and remove empty addresses.
 */
class ClubValidator extends AbstractValidator
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
     * @param Club $value The value that should be validated
     * @return bool TRUE if the value is valid, FALSE if an error occurred
     */
    public function isValid($value): bool
    {
        $this->removeEmptyAddresses($value);
        if (empty($value->getAddresses())) {
            $this->addError('You have forgotten to set at least one address', 1399904889);
            return false;
        }
        return true;
    }

    /**
     * If a customer has assigned less than 3 addresses to a club,
     * we have to remove these empty addresses before saving to DB.
     *
     * @param Club $club
     */
    protected function removeEmptyAddresses(Club $club)
    {
        foreach ($club->getAddresses() as $address) {
            if (
                empty($address->getStreet())
                && empty($address->getHouseNumber())
                && empty($address->getZip())
                && empty($address->getCity())
            ) {
                $club->removeAddress($address);
            }
        }
    }
}
