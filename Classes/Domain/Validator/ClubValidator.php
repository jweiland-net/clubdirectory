<?php
declare(strict_types=1);
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
     * @param mixed $value The value that should be validated
     *
     * @return bool TRUE if the value is valid, FALSE if an error occurred
     */
    public function isValid($value): bool
    {
        /* @var $value Club */
        $this->removeEmptyAddresses($value);
        $addresses = $value->getAddresses();
        if (\is_object($addresses) && $addresses instanceof \Countable && $addresses->count() === 0) {
            $this->addError('You have forgotten to set at lease one address', 1399904889);
            return false;
        }

        return true;
    }

    /**
     * For our form we have to add all 3 addresses. Filled or not filled.
     * But it's bad to add these empty addresses into DB. So we remove empty addresses here.
     *
     * @param Club $club
     * @return void
     */
    protected function removeEmptyAddresses(Club $club)
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var Address $emptyAddress */
        $emptyAddress = $objectManager->get(Address::class);
        $addresses = clone $club->getAddresses();
        /** @var Address $address */
        foreach ($addresses as $address) {
            if ($address === $emptyAddress) {
                $club->removeAddress($address);
            }
        }
    }
}
