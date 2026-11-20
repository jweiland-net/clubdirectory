<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Hook\Form;

use JWeiland\Clubdirectory\Domain\Validator\GeocodingFailureValidator;
use JWeiland\Clubdirectory\Form\FormOwner;
use JWeiland\Clubdirectory\Helper\GeocodingFailure;
use JWeiland\Clubdirectory\Service\GeocodingService;
use JWeiland\Maps2\Domain\Model\Position;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Form\Domain\Model\FormElements\AbstractFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * Geocodes the addresses of a submitted page of a form based on EXT:form.
 * The position is the initial value of the related form element of type
 * "Maps2PoiPosition" of EXT:maps2, which is shown on a later page, so the
 * user can correct the position by dragging the marker.
 *
 * The position element defines the address elements in its properties:
 *
 * properties:
 *   geocoding:
 *     requiredElement: address-1-title
 *     errorElement: address-1-street
 *     addressElements:
 *       street: address-1-street
 *       houseNumber: address-1-house-number
 *       zip: address-1-zip
 *       city: address-1-city
 *
 * An address is geocoded, if the "requiredElement", "zip" and "city" are
 * filled and the address differs from the address submitted before (or
 * prefilled on edit). So a corrected position is kept, as long as the
 * address is not changed. If the address could not be geocoded, the
 * "errorElement" gets an error and the user stays on the page.
 *
 * Forms, which are not built by clubdirectory, are skipped.
 */
final readonly class GeocodeAddressesHook
{
    public const ELEMENT_TYPE = 'Maps2PoiPosition';

    public function __construct(
        private GeocodingService $geocodingService,
    ) {}

    /**
     * Called by EXT:form for the submitted page first and then for each of its
     * elements. Only the call for the page is used, as all values of the page
     * are available then and the elements are validated afterwards.
     *
     * @param array<string, mixed> $requestArguments
     */
    public function afterSubmit(
        FormRuntime $formRuntime,
        RenderableInterface $renderable,
        mixed $elementValue = null,
        array $requestArguments = [],
    ): mixed {
        if (!$renderable instanceof Page || !FormOwner::isClubdirectoryForm($formRuntime->getFormDefinition())) {
            return $elementValue;
        }

        foreach ($formRuntime->getFormDefinition()->getRenderablesRecursively() as $positionElement) {
            if ($positionElement instanceof FormElementInterface && $positionElement->getType() === self::ELEMENT_TYPE) {
                $this->processPositionElement($formRuntime, $renderable, $positionElement, $requestArguments);
            }
        }

        return $elementValue;
    }

    /**
     * @param array<string, mixed> $requestArguments
     */
    private function processPositionElement(
        FormRuntime $formRuntime,
        Page $page,
        FormElementInterface $positionElement,
        array $requestArguments,
    ): void {
        $configuration = $positionElement->getProperties()['geocoding'] ?? [];
        if (!is_array($configuration) || !$this->isConfigurationOfPage($configuration, $page)) {
            return;
        }

        $address = $this->getAddress($configuration, $requestArguments);
        if ($address === '' || $this->isGeocoded($formRuntime, $configuration, $positionElement, $address)) {
            return;
        }

        $result = $this->geocodingService->geocode($address);
        if ($result instanceof Position) {
            $this->setPosition($formRuntime, $positionElement, $result, $address);
            return;
        }

        $this->addGeocodingFailure($page, (string)($configuration['errorElement'] ?? ''), $result);
    }

    /**
     * The position of the form state (e.g. of a former visit of the map page)
     * takes precedence over the default value. So both are set.
     */
    private function setPosition(
        FormRuntime $formRuntime,
        FormElementInterface $positionElement,
        Position $position,
        string $address,
    ): void {
        $value = [
            'latitude' => number_format($position->getLatitude(), 6, '.', ''),
            'longitude' => number_format($position->getLongitude(), 6, '.', ''),
        ];

        $positionElement->setDefaultValue($value);
        $positionElement->setProperty('markerTitle', $address);
        $formRuntime->getFormState()?->setFormValue($positionElement->getIdentifier(), $value);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function isConfigurationOfPage(array $configuration, Page $page): bool
    {
        $identifiers = is_array($configuration['addressElements'] ?? null) ? $configuration['addressElements'] : [];
        $identifiers[] = $configuration['requiredElement'] ?? '';
        $pageIdentifiers = array_map(
            static fn(RenderableInterface $element): string => $element->getIdentifier(),
            $page->getElementsRecursively(),
        );

        return array_diff(array_filter($identifiers), $pageIdentifiers) === [];
    }

    /**
     * Returns an empty string, if the address is not used or incomplete
     *
     * @param array<string, mixed> $configuration
     * @param array<string, mixed> $requestArguments
     */
    private function getAddress(array $configuration, array $requestArguments): string
    {
        $addressElements = is_array($configuration['addressElements'] ?? null) ? $configuration['addressElements'] : [];
        $values = [];
        foreach (['street', 'houseNumber', 'zip', 'city'] as $key) {
            $values[$key] = $this->getRequestValue($requestArguments, $addressElements[$key] ?? '');
        }

        if ($this->getRequestValue($requestArguments, $configuration['requiredElement'] ?? '') === ''
            || $values['zip'] === ''
            || $values['city'] === ''
        ) {
            return '';
        }

        return $this->geocodingService->formatAddress($values['street'], $values['houseNumber'], $values['zip'], $values['city']);
    }

    /**
     * @param array<string, mixed> $requestArguments
     */
    private function getRequestValue(array $requestArguments, mixed $identifier): string
    {
        $value = is_string($identifier) ? $requestArguments[$identifier] ?? '' : '';

        return is_string($value) ? trim($value) : '';
    }

    /**
     * EXT:form calls this hook before the submitted values are stored in the
     * form state. So getElementValue() returns the values of the former
     * submission or the values prefilled on edit.
     *
     * @param array<string, mixed> $configuration
     */
    private function isGeocoded(
        FormRuntime $formRuntime,
        array $configuration,
        FormElementInterface $positionElement,
        string $address,
    ): bool {
        $position = $formRuntime->getElementValue($positionElement->getIdentifier());
        if (!is_array($position) || !is_numeric($position['latitude'] ?? null) || !is_numeric($position['longitude'] ?? null)) {
            return false;
        }

        $formerValues = [];
        $addressElements = is_array($configuration['addressElements'] ?? null) ? $configuration['addressElements'] : [];
        foreach (['street', 'houseNumber', 'zip', 'city'] as $key) {
            $formerValue = $formRuntime->getElementValue((string)($addressElements[$key] ?? ''));
            $formerValues[$key] = is_scalar($formerValue) ? (string)$formerValue : '';
        }

        return $this->geocodingService->formatAddress(...$formerValues) === $address;
    }

    private function addGeocodingFailure(Page $page, string $errorElementIdentifier, GeocodingFailure $failure): void
    {
        foreach ($page->getElementsRecursively() as $element) {
            if ($element->getIdentifier() !== $errorElementIdentifier || !$element instanceof AbstractFormElement) {
                continue;
            }

            $labelPrefix = $failure->getLabelPrefix();
            $element->createValidator(GeocodingFailureValidator::IDENTIFIER, [
                'message' => LocalizationUtility::translate($labelPrefix . 'title', 'clubdirectory') . ': '
                    . LocalizationUtility::translate($labelPrefix . 'body', 'clubdirectory', [$failure->address]),
            ]);
        }
    }
}
