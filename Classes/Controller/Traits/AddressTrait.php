<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Controller\Traits;

use JWeiland\Clubdirectory\Domain\Model\Address;
use JWeiland\Clubdirectory\Domain\Model\Club;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Trait with a method to translate the three different address types
 * Currently used in Club- and MapController
 */
trait AddressTrait
{
    /**
     * Get translated titles of all address types for select box in address records form.
     */
    protected function getAddressTitles(): array
    {
        $values = GeneralUtility::trimExplode(',', 'organizationAddress, postAddress, clubAddress', true);
        $titles = [];
        foreach ($values as $value) {
            $title = new \stdClass();
            $title->value = $value;
            $title->label = LocalizationUtility::translate(
                'tx_clubdirectory_domain_model_address.title.' . $value,
                'clubdirectory',
            );
            $titles[] = $title;
        }

        return $titles;
    }

    /**
     * As we have a Checkbox in Address Model, we have to fill
     * Club with the maximum of Address Models to prevent Rendering Errors.
     *
     * Never put that into Club Model as we don't want all these empty addresses in DB.
     * So this Method will only correct the rendering of frontend.
     */
    protected function fillAddressesUpToMaximum(Club $club): void
    {
        for ($i = \count($club->getAddresses()); $i < 3; ++$i) {
            $club->addAddress(GeneralUtility::makeInstance(Address::class));
        }
    }

    /**
     * The form always renders 3 address blocks. Remove all addresses without
     * an address type from request before property mapping starts. Already
     * persisted addresses without an address type will be removed from club.
     */
    protected function removeEmptyAddressesFromRequest(array &$requestArgument): void
    {
        if (!is_array($requestArgument['addresses'] ?? null)) {
            return;
        }

        foreach ($requestArgument['addresses'] as $key => $address) {
            if (empty($address['title'])) {
                unset($requestArgument['addresses'][$key]);
            }
        }

        $this->request = $this->request->withArgument('club', $requestArgument);
    }
}
