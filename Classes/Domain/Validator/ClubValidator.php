<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Domain\Validator;

use JWeiland\Clubdirectory\Domain\Model\Address;
use JWeiland\Clubdirectory\Domain\Model\Club;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validates that a club has at least one address and that each address
 * contains at least street, zip and city.
 *
 * Addresses without an address type are already removed from the request
 * by AddressTrait::removeEmptyAddressesFromRequest().
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
     * @param Club $value The value that should be validated
     */
    public function isValid(mixed $value): void
    {
        $addresses = $value->getAddresses();
        if ($addresses === []) {
            $this->addError(
                $this->translateErrorMessage('error.addresses.missing', 'clubdirectory'),
                1399904889,
            );
            return;
        }

        foreach ($addresses as $address) {
            $this->validateAddress($address);
        }
    }

    protected function validateAddress(Address $address): void
    {
        if (
            trim($address->getStreet()) !== ''
            && trim($address->getZip()) !== ''
            && trim($address->getCity()) !== ''
        ) {
            return;
        }

        $addressType = $this->translateErrorMessage(
            'tx_clubdirectory_domain_model_address.title.' . $address->getTitle(),
            'clubdirectory',
        );

        $this->addError(
            $this->translateErrorMessage(
                'error.address.incomplete',
                'clubdirectory',
                [$addressType !== '' ? $addressType : $address->getTitle()],
            ),
            1793606614,
        );
    }
}
