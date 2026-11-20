<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Hook\Form;

use JWeiland\Clubdirectory\Domain\Validator\GeocodingFailureValidator;
use JWeiland\Clubdirectory\Hook\Form\GeocodeAddressesHook;
use JWeiland\Clubdirectory\Service\GeocodingService;
use JWeiland\Clubdirectory\Tests\Functional\Traits\ClubFormTestTrait;
use JWeiland\Maps2\Client\ClientError;
use JWeiland\Maps2\Client\ClientErrorEnum;
use JWeiland\Maps2\Domain\Model\GeoCodeResult;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Service\GeoCodeService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Domain\Runtime\FormState;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case for the geocoding on the transition from the page with the
 * addresses to the page with the map positions. The map provider of
 * EXT:maps2 is replaced by a stub, so no request leaves the test.
 */
class GeocodeAddressesHookTest extends FunctionalTestCase
{
    use ClubFormTestTrait;

    protected array $coreExtensionsToLoad = [
        'typo3/cms-form',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    private const REQUEST_ARGUMENTS = [
        'address-1-title' => 'clubAddress',
        'address-1-street' => 'Main street',
        'address-1-house-number' => '1',
        'address-1-zip' => '79100',
        'address-1-city' => 'Freiburg',
    ];

    private GeoCodeService&MockObject $geoCodeServiceMock;

    private FormDefinition $formDefinition;

    private FormState $formState;

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->create('default');
        $this->geoCodeServiceMock = $this->createMock(GeoCodeService::class);
        $this->formDefinition = $this->createFormDefinition();
        $this->formState = new FormState();
    }

    /**
     * @param array<string, mixed> $requestArguments
     */
    private function submitPage(string $pageIdentifier, array $requestArguments = self::REQUEST_ARGUMENTS): void
    {
        $formRuntime = $this->createMock(FormRuntime::class);
        $formRuntime->method('getFormDefinition')->willReturn($this->formDefinition);
        $formRuntime->method('getFormState')->willReturn($this->formState);
        $formRuntime->method('getElementValue')->willReturnCallback(
            fn(string $identifier): mixed => $this->formState->getFormValue($identifier)
                ?? $this->formDefinition->getElementDefaultValueByIdentifier($identifier),
        );

        $subject = new GeocodeAddressesHook(new GeocodingService($this->geoCodeServiceMock, new NullLogger()));
        $subject->afterSubmit($formRuntime, $this->getPage($pageIdentifier), null, $requestArguments);
    }

    private function getPage(string $pageIdentifier): Page
    {
        foreach ($this->formDefinition->getPages() as $page) {
            if ($page->getIdentifier() === $pageIdentifier) {
                return $page;
            }
        }

        self::fail('Page ' . $pageIdentifier . ' not found');
    }

    private function createGeoCodeResult(float $latitude, float $longitude): GeoCodeResult
    {
        $position = new Position();
        $position->setLatitude($latitude);
        $position->setLongitude($longitude);

        /** @var ObjectStorage<Position> $positions */
        $positions = new ObjectStorage();
        $positions->attach($position);

        return new GeoCodeResult($positions);
    }

    /**
     * @return string[]
     */
    private function getErrorMessagesOfStreet(): array
    {
        $messages = [];
        foreach ($this->formDefinition->getElementByIdentifier('address-1-street')?->getValidators() ?? [] as $validator) {
            if ($validator instanceof GeocodingFailureValidator) {
                foreach ($validator->validate('Main street')->getErrors() as $error) {
                    $messages[] = $error->getMessage();
                }
            }
        }

        return $messages;
    }

    #[Test]
    public function afterSubmitOfAddressPageSetsGeocodedPosition(): void
    {
        $this->geoCodeServiceMock
            ->expects(self::once())
            ->method('getGeoCodeResult')
            ->with('Main street 1, 79100 Freiburg')
            ->willReturn($this->createGeoCodeResult(47.991234567, 7.84123));

        $this->submitPage('club-data');

        $expectedPosition = ['latitude' => '47.991235', 'longitude' => '7.841230'];
        self::assertSame($expectedPosition, $this->formState->getFormValue('address-1-position'));
        self::assertSame($expectedPosition, $this->formDefinition->getElementDefaultValueByIdentifier('address-1-position'));
        self::assertSame([], $this->getErrorMessagesOfStreet());
    }

    #[Test]
    public function afterSubmitOfUnchangedAddressKeepsCorrectedPosition(): void
    {
        foreach (self::REQUEST_ARGUMENTS as $identifier => $value) {
            $this->formState->setFormValue($identifier, $value);
        }
        $correctedPosition = ['latitude' => '47.5', 'longitude' => '7.5'];
        $this->formState->setFormValue('address-1-position', $correctedPosition);

        $this->geoCodeServiceMock->expects(self::never())->method('getGeoCodeResult');

        $this->submitPage('club-data');

        self::assertSame($correctedPosition, $this->formState->getFormValue('address-1-position'));
    }

    #[Test]
    public function afterSubmitOfChangedAddressGeocodesAgain(): void
    {
        foreach (self::REQUEST_ARGUMENTS as $identifier => $value) {
            $this->formState->setFormValue($identifier, $value);
        }
        $this->formState->setFormValue('address-1-position', ['latitude' => '47.5', 'longitude' => '7.5']);

        $this->geoCodeServiceMock
            ->expects(self::once())
            ->method('getGeoCodeResult')
            ->with('Main street 1, 79098 Freiburg')
            ->willReturn($this->createGeoCodeResult(48.0, 7.9));

        $this->submitPage('club-data', array_replace(self::REQUEST_ARGUMENTS, ['address-1-zip' => '79098']));

        self::assertSame(['latitude' => '48.000000', 'longitude' => '7.900000'], $this->formState->getFormValue('address-1-position'));
    }

    public static function unusedAddressDataProvider(): array
    {
        return [
            'no address type' => [['address-1-title' => '']],
            'no zip' => [['address-1-zip' => '']],
            'no city' => [['address-1-city' => ' ']],
        ];
    }

    /**
     * @param array<string, string> $overrideArguments
     */
    #[Test]
    #[DataProvider('unusedAddressDataProvider')]
    public function afterSubmitWithUnusedAddressDoesNotGeocode(array $overrideArguments): void
    {
        $this->geoCodeServiceMock->expects(self::never())->method('getGeoCodeResult');

        $this->submitPage('club-data', array_replace(self::REQUEST_ARGUMENTS, $overrideArguments));

        self::assertNull($this->formState->getFormValue('address-1-position'));
    }

    #[Test]
    public function afterSubmitOfMapPageDoesNotGeocode(): void
    {
        $this->geoCodeServiceMock->expects(self::never())->method('getGeoCodeResult');

        $this->submitPage('map-positions');
    }

    public static function geocodingFailureDataProvider(): array
    {
        $address = 'Main street 1, 79100 Freiburg';

        return [
            'no positions found' => [
                ClientErrorEnum::NO_POSITIONS_FOUND,
                'Address not found: The position of the address "' . $address . '" could not be determined.'
                . ' Please check street, house number, ZIP and city.',
            ],
            'request denied' => [
                ClientErrorEnum::REQUEST_DENIED,
                'Address lookup not authorized: The position of the address "' . $address . '" could not be determined,'
                . ' because this server is currently not authorized for address lookups at the map service.'
                . ' Your input was not saved. Please inform the website administrator.',
            ],
            'quota exceeded' => [
                ClientErrorEnum::QUOTA_EXCEEDED,
                'Map service not available: The position of the address "' . $address . '" could not be determined,'
                . ' because the map service is currently not available. Your input was not saved.'
                . ' Please try again later or inform the website administrator.',
            ],
        ];
    }

    #[Test]
    #[DataProvider('geocodingFailureDataProvider')]
    public function afterSubmitWithGeocodingFailureAddsErrorToStreet(ClientErrorEnum $type, string $expectedMessage): void
    {
        $this->geoCodeServiceMock
            ->method('getGeoCodeResult')
            ->willReturn(new GeoCodeResult(null, new ClientError($type, 'Provider message', 'Provider title')));

        $this->submitPage('club-data');

        self::assertSame([$expectedMessage], $this->getErrorMessagesOfStreet());
        self::assertNull($this->formState->getFormValue('address-1-position'));
    }

    #[Test]
    public function afterSubmitIgnoresFormOfOtherExtension(): void
    {
        $this->formDefinition = $this->createFormDefinition('yellowpages2');
        $this->geoCodeServiceMock->expects(self::never())->method('getGeoCodeResult');

        $this->submitPage('club-data');

        self::assertNull($this->formState->getFormValue('address-1-position'));
        self::assertSame([], $this->getErrorMessagesOfStreet());
    }
}
