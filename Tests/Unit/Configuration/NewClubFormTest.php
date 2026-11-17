<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\Configuration;

use JWeiland\Clubdirectory\Hook\Form\GeocodeAddressesHook;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Yaml\Yaml;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Checks the structure of the club form based on EXT:form: the two steps,
 * the position elements and the mappings of the SaveClubFinisher
 */
class NewClubFormTest extends UnitTestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $formDefinition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formDefinition = Yaml::parseFile(__DIR__ . '/../../../Configuration/Form/NewClub.form.yaml');
    }

    /**
     * @param array<int, array<string, mixed>> $renderables
     * @return array<string, array<string, mixed>>
     */
    private function getElements(array $renderables): array
    {
        $elements = [];
        foreach ($renderables as $renderable) {
            $elements[$renderable['identifier']] = $renderable;
            $elements = array_merge($elements, $this->getElements($renderable['renderables'] ?? []));
        }

        return $elements;
    }

    #[Test]
    public function formHasPageOfClubDataAndPageOfMapPositions(): void
    {
        self::assertSame(
            ['club-data', 'map-positions'],
            array_column($this->formDefinition['renderables'], 'identifier'),
        );
    }

    #[Test]
    public function mapPageContainsOnePositionElementForEachAddress(): void
    {
        $positionElements = array_filter(
            $this->getElements($this->formDefinition['renderables'][1]['renderables']),
            static fn(array $element): bool => $element['type'] === GeocodeAddressesHook::ELEMENT_TYPE,
        );

        self::assertSame(['address-1-position', 'address-2-position', 'address-3-position'], array_keys($positionElements));
    }

    #[Test]
    public function geocodingConfigurationReferencesElementsOfFirstPage(): void
    {
        $elementsOfFirstPage = $this->getElements($this->formDefinition['renderables'][0]['renderables']);
        foreach ($this->getElements($this->formDefinition['renderables'][1]['renderables']) as $element) {
            if ($element['type'] !== GeocodeAddressesHook::ELEMENT_TYPE) {
                continue;
            }

            $geocoding = $element['properties']['geocoding'];
            $identifiers = [$geocoding['requiredElement'], $geocoding['errorElement'], ...array_values($geocoding['addressElements'])];
            foreach ($identifiers as $identifier) {
                self::assertArrayHasKey($identifier, $elementsOfFirstPage, $element['identifier'] . ' references ' . $identifier);
            }
        }
    }

    #[Test]
    public function finisherMapsExistingElementsOnly(): void
    {
        $elements = $this->getElements($this->formDefinition['renderables']);
        foreach ($this->formDefinition['finishers'][1]['options'] as $step => $options) {
            foreach (array_keys($options['elements'] ?? []) as $identifier) {
                self::assertArrayHasKey($identifier, $elements, 'Step ' . $step . ' maps unknown element ' . $identifier);
            }
        }
    }

    #[Test]
    public function finisherStoresPositionOfEachAddressInPoiCollection(): void
    {
        $options = $this->formDefinition['finishers'][1]['options'];
        foreach ([1, 2, 3] as $position) {
            $addressStep = $options[$position + 3];
            self::assertSame($position, $addressStep['position']);
            self::assertTrue($addressStep['elements']['address-' . $position . '-position']['poiCollection']);
        }
    }
}
