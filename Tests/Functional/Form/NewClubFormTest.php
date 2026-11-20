<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Form;

use JWeiland\Clubdirectory\Domain\Factory\ArrayFormFactory;
use JWeiland\Clubdirectory\Domain\Validator\GeocodingFailureValidator;
use JWeiland\Clubdirectory\Form\FormOwner;
use JWeiland\Clubdirectory\Hook\Form\RichTextEditorHook;
use JWeiland\Clubdirectory\Tests\Functional\Traits\ClubAccessTestTrait;
use JWeiland\Clubdirectory\Tests\Functional\Traits\ClubFormTestTrait;
use JWeiland\Maps2\Client\ClientError;
use JWeiland\Maps2\Client\ClientErrorEnum;
use JWeiland\Maps2\Domain\Model\GeoCodeResult;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Service\GeoCodeService;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Form\Domain\Configuration\ConfigurationService;
use TYPO3\CMS\Form\Domain\Factory\ArrayFormFactory as CoreArrayFormFactory;
use TYPO3\CMS\Form\Domain\Factory\FormFactoryInterface;
use TYPO3\CMS\Form\Domain\Finishers\EmailFinisher;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Domain\Runtime\FormState;
use TYPO3\CMS\Form\Security\HashScope;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Builds the complete club form of NewClub.form.yaml with the prototype of
 * EXT:form incl. the form setup of EXT:maps2 and the hooks of clubdirectory.
 *
 * The map step needs the form element "Maps2PoiPosition" of EXT:maps2
 * 12.3.0. The tests are skipped, as long as an older version of EXT:maps2
 * is installed.
 */
class NewClubFormTest extends FunctionalTestCase
{
    use ClubAccessTestTrait;
    use ClubFormTestTrait;

    protected array $coreExtensionsToLoad = [
        'typo3/cms-form',
        'typo3/cms-rte-ckeditor',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (version_compare(ExtensionManagementUtility::getExtensionVersion('maps2'), '12.3.0', '<')) {
            self::markTestSkipped('The form element "Maps2PoiPosition" of EXT:maps2 >= 12.3.0 is not available.');
        }

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/ClubAccess.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/ClubForm.csv');
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);
        parent::tearDown();
    }

    /**
     * @param array<string, mixed> $settings Additional settings of plugin.tx_clubdirectory
     */
    private function buildForm(Request $request, array $settings = [], ?FormFactoryInterface $formFactory = null): FormDefinition
    {
        $frontendTypoScript = $request->getAttribute('frontend.typoscript');
        self::assertInstanceOf(FrontendTypoScript::class, $frontendTypoScript);
        $setup = $frontendTypoScript->getSetupArray();
        $setup['plugin.']['tx_form.']['settings.']['yamlConfigurations.'] = [
            '10' => 'EXT:form/Configuration/Yaml/FormSetup.yaml',
            '20' => 'EXT:maps2/Configuration/Form/FormSetup.yaml',
            '30' => 'EXT:clubdirectory/Configuration/Form/OverrideFormSettings.yaml',
        ];
        $setup['plugin.']['tx_clubdirectory.']['settings.'] = array_replace(
            $setup['plugin.']['tx_clubdirectory.']['settings.'],
            $settings,
        );
        $frontendTypoScript->setSetupArray($setup);
        $GLOBALS['TYPO3_REQUEST'] = $request;
        $this->get(ConfigurationManagerInterface::class)->setRequest($request);

        $configuration = Yaml::parseFile(__DIR__ . '/../../../Configuration/Form/NewClub.form.yaml');
        if ($formFactory instanceof FormFactoryInterface) {
            // A copy of the club form with a rich text element, rendered by the form plugin of EXT:form
            $configuration['renderables'][0]['renderables'][3]['properties']['dbMapping']['dataType'] = 'richText';

            return $formFactory->build($configuration, null, $request);
        }

        return $this->get(ArrayFormFactory::class)->build($configuration, null, $request);
    }

    #[Test]
    public function buildCreatesPageOfClubDataAndMapStepWithOnePositionPerAddress(): void
    {
        $this->setUserAspects($this->get(Context::class), self::OWNER);

        $formDefinition = $this->buildForm($this->createFormRequest());

        self::assertSame('club-data', $formDefinition->getPageByIndex(0)->getIdentifier());
        self::assertSame('map-positions', $formDefinition->getPageByIndex(1)->getIdentifier());
        foreach ([1, 2, 3] as $position) {
            self::assertSame(
                'Maps2PoiPosition',
                $formDefinition->getElementByIdentifier('address-' . $position . '-position')?->getType(),
            );
        }
    }

    #[Test]
    public function buildPrefillsAddressesAndPositionsOfEditedClub(): void
    {
        $this->setUserAspects($this->get(Context::class), self::OWNER);

        $formDefinition = $this->buildForm($this->createFormRequest(self::VISIBLE_CLUB));

        self::assertSame('Swimming club', $formDefinition->getElementDefaultValueByIdentifier('title'));
        self::assertSame('organizationAddress', $formDefinition->getElementDefaultValueByIdentifier('address-1-title'));
        self::assertSame(
            ['latitude' => '47.900000', 'longitude' => '7.800000'],
            $formDefinition->getElementDefaultValueByIdentifier('address-1-position'),
        );
        self::assertNull($formDefinition->getElementDefaultValueByIdentifier('address-2-position'));
    }

    #[Test]
    public function buildAddsClubAndModerationTokenToFormAction(): void
    {
        $this->setUserAspects($this->get(Context::class));

        $formDefinition = $this->buildForm($this->createFormRequest(self::HIDDEN_CLUB, 'token'));

        self::assertSame(
            ['club' => self::HIDDEN_CLUB, 'moderationToken' => 'token'],
            $formDefinition->getRenderingOptions()['additionalParams']['tx_clubdirectory_management'],
        );
    }

    #[Test]
    public function buildAddsOptionsOfCategoriesAndDistricts(): void
    {
        $this->setUserAspects($this->get(Context::class), self::OWNER);

        $formDefinition = $this->buildForm($this->createFormRequest());

        self::assertSame([8 => 'Music', 7 => 'Sports'], $formDefinition->getElementByIdentifier('categories')?->getProperties()['options']);
        self::assertSame([2 => 'Altstadt', 1 => 'Wiehre'], $formDefinition->getElementByIdentifier('district')?->getProperties()['options']);
    }

    #[Test]
    public function buildWithEnabledRichTextEditorConfiguresDescriptionAsRichText(): void
    {
        $this->setUserAspects($this->get(Context::class), self::OWNER);

        $formDefinition = $this->buildForm(
            $this->createFormRequest(),
            ['new.' => ['richText.' => ['enable' => '1', 'elements' => 'description']]],
        );

        self::assertSame(
            'richText',
            $formDefinition->getElementByIdentifier('description')?->getProperties()['dbMapping']['dataType'],
        );
    }

    /**
     * The mail templates of the club form must not change the mails of other
     * forms of the installation. So the prototype keeps the templates of
     * EXT:form.
     */
    #[Test]
    public function formSetupKeepsMailTemplatesOfPrototype(): void
    {
        $this->setUserAspects($this->get(Context::class), self::OWNER);
        $this->buildForm($this->createFormRequest());

        $emailFinisherDefinition = GeneralUtility::makeInstance(ConfigurationService::class)
            ->getPrototypeConfiguration('standard')['finishersDefinition']['EmailToReceiver'];

        self::assertSame('Default', $emailFinisherDefinition['options']['templateName']);
        foreach ($emailFinisherDefinition['options']['templateRootPaths'] as $templateRootPath) {
            self::assertStringStartsWith('EXT:form/', $templateRootPath);
        }
    }

    /**
     * The keys "element.<identifier>..." of the translation file of the club
     * form must not translate elements of other forms.
     */
    #[Test]
    public function formSetupKeepsTranslationFilesOfPrototype(): void
    {
        $this->setUserAspects($this->get(Context::class), self::OWNER);
        $this->buildForm($this->createFormRequest());

        $translationFiles = GeneralUtility::makeInstance(ConfigurationService::class)
            ->getPrototypeConfiguration('standard')['formElementsDefinition']['Form']['renderingOptions']['translation']['translationFiles'];

        self::assertNotSame([], $translationFiles);
        foreach ($translationFiles as $translationFile) {
            self::assertStringStartsWith('EXT:form/', $translationFile);
        }
    }

    #[Test]
    public function clubFormRegistersOwnTranslationFile(): void
    {
        $this->setUserAspects($this->get(Context::class), self::OWNER);

        $translationFiles = $this->buildForm($this->createFormRequest())
            ->getRenderingOptions()['translation']['translationFiles'];

        self::assertSame(
            'EXT:clubdirectory/Resources/Private/Language/locallang_ext_form.xlf',
            $translationFiles[1763337600],
        );
        self::assertArrayHasKey(10, $translationFiles);
    }

    #[Test]
    public function formSetupRegistersValidatorWithIdentifierOfClubdirectory(): void
    {
        $this->setUserAspects($this->get(Context::class), self::OWNER);
        $this->buildForm($this->createFormRequest());

        $validatorsDefinition = GeneralUtility::makeInstance(ConfigurationService::class)
            ->getPrototypeConfiguration('standard')['validatorsDefinition'];

        self::assertSame(
            GeocodingFailureValidator::class,
            $validatorsDefinition[GeocodingFailureValidator::IDENTIFIER]['implementationClassName'],
        );
        self::assertArrayNotHasKey('GeocodingFailure', $validatorsDefinition);
    }

    #[Test]
    public function emailFinisherOfClubFormUsesMailTemplatesOfClubdirectory(): void
    {
        $this->setUserAspects($this->get(Context::class), self::OWNER);
        $formDefinition = $this->buildForm($this->createFormRequest());

        $emailFinishers = array_values(array_filter(
            $formDefinition->getFinishers(),
            static fn(object $finisher): bool => $finisher instanceof EmailFinisher,
        ));
        self::assertCount(1, $emailFinishers);
        $options = (new \ReflectionProperty(EmailFinisher::class, 'options'))->getValue($emailFinishers[0]);

        self::assertSame(
            'EXT:clubdirectory/Resources/Private/FormFramework/Templates/Finishers/Email/',
            $options['templateRootPaths'][1763337600],
        );
        self::assertSame('ClubModeration', $options['templateName']);
        self::assertArrayHasKey(10, $options['templateRootPaths']);
    }

    #[Test]
    public function buildMarksClubFormWithFormOwner(): void
    {
        $this->setUserAspects($this->get(Context::class), self::OWNER);

        $formDefinition = $this->buildForm($this->createFormRequest());

        self::assertSame(FormOwner::CLUBDIRECTORY, $formDefinition->getRenderingOptions()[FormOwner::RENDERING_OPTION]);
    }

    /**
     * Integrators could render a copy of NewClub.form.yaml with the form
     * plugin of EXT:form. The hooks of clubdirectory must not touch it.
     */
    #[Test]
    public function hooksOfClubdirectoryIgnoreFormBuiltByFactoryOfCore(): void
    {
        $this->setUserAspects($this->get(Context::class), self::OWNER);
        $request = $this->createFormRequest(self::VISIBLE_CLUB)
            ->withAttribute('frontend.user', new FrontendUserAuthentication());
        $formDefinition = $this->buildForm($request, [], GeneralUtility::makeInstance(CoreArrayFormFactory::class));

        self::assertArrayNotHasKey(FormOwner::RENDERING_OPTION, $formDefinition->getRenderingOptions());
        // The default value of the form definition, not the title of the club
        self::assertSame('', $formDefinition->getElementDefaultValueByIdentifier('title'));
        self::assertNull($formDefinition->getElementDefaultValueByIdentifier('address-1-position'));
        self::assertArrayNotHasKey('options', $formDefinition->getElementByIdentifier('categories')?->getProperties() ?? []);
        self::assertArrayNotHasKey('options', $formDefinition->getElementByIdentifier('district')?->getProperties() ?? []);

        $description = $formDefinition->getElementByIdentifier('description');
        self::assertNotNull($description);
        GeneralUtility::makeInstance(RichTextEditorHook::class)->beforeRendering($formDefinition->bind($request), $description);
        self::assertArrayNotHasKey(
            RichTextEditorHook::DATA_ATTRIBUTE,
            $description->getProperties()['fluidAdditionalAttributes'] ?? [],
        );
    }

    #[Test]
    public function submitOfAddressPageOfFormBuiltByFactoryOfCoreDoesNotGeocode(): void
    {
        $geoCodeService = $this->createMock(GeoCodeService::class);
        $geoCodeService->expects(self::never())->method('getGeoCodeResult');
        $this->getContainer()->set(GeoCodeService::class, $geoCodeService);

        $formRuntime = $this->submitAddressPage(
            $this->getValuesOfAddressPage(),
            GeneralUtility::makeInstance(CoreArrayFormFactory::class),
        );

        self::assertSame('map-positions', $formRuntime->getCurrentPage()?->getIdentifier());
        self::assertNull($formRuntime->getElementValue('address-1-position'));
    }

    /**
     * Submits the first page of the form with the given values, so EXT:form
     * processes the page transition incl. the hooks of clubdirectory.
     *
     * @param array<string, mixed> $values
     */
    private function submitAddressPage(array $values, ?FormFactoryInterface $formFactory = null): FormRuntime
    {
        $this->setUserAspects($this->get(Context::class), self::OWNER);
        $formState = new FormState();
        $formState->setLastDisplayedPageIndex(0);
        $contentObject = $this->get(ContentObjectRenderer::class);
        $contentObject->setUserObjectType(ContentObjectRenderer::OBJECTTYPE_USER_INT);

        $request = $this->createFormRequest()
            ->withAttribute('currentContentObject', $contentObject)
            // The honeypot of EXT:form is stored in the session of the frontend user
            ->withAttribute('frontend.user', new FrontendUserAuthentication())
            ->withAttribute('language', new SiteLanguage(0, 'en_US.UTF-8', new Uri('https://www.example.com/'), []));
        $formDefinition = $this->buildForm($request, [], $formFactory);
        $request = $request->withArguments([
            'newClub' => array_replace($values, [
                '__state' => $this->get(HashService::class)->appendHmac(
                    base64_encode(serialize($formState)),
                    HashScope::FormState->prefix(),
                ),
                '__currentPage' => '1',
            ]),
        ]);

        return $formDefinition->bind($request);
    }

    private function mockGeoCodeResult(GeoCodeResult $geoCodeResult): void
    {
        $geoCodeService = $this->createMock(GeoCodeService::class);
        $geoCodeService
            ->expects(self::once())
            ->method('getGeoCodeResult')
            ->with('Main street 1, 79100 Freiburg')
            ->willReturn($geoCodeResult);
        $this->getContainer()->set(GeoCodeService::class, $geoCodeService);
    }

    /**
     * @return array<string, string>
     */
    private function getValuesOfAddressPage(): array
    {
        return [
            'title' => 'Chess club',
            'address-1-title' => 'clubAddress',
            'address-1-street' => 'Main street',
            'address-1-house-number' => '1',
            'address-1-zip' => '79100',
            'address-1-city' => 'Freiburg',
        ];
    }

    #[Test]
    public function submitOfAddressPageShowsMapStepWithGeocodedPosition(): void
    {
        $position = new Position();
        $position->setLatitude(47.991234);
        $position->setLongitude(7.841234);
        /** @var ObjectStorage<Position> $positions */
        $positions = new ObjectStorage();
        $positions->attach($position);
        $this->mockGeoCodeResult(new GeoCodeResult($positions));

        $formRuntime = $this->submitAddressPage($this->getValuesOfAddressPage());

        self::assertSame('map-positions', $formRuntime->getCurrentPage()?->getIdentifier());
        self::assertSame(
            ['latitude' => '47.991234', 'longitude' => '7.841234'],
            $formRuntime->getElementValue('address-1-position'),
        );
        self::assertTrue($formRuntime->getFormDefinition()->getElementByIdentifier('address-1-position')?->isEnabled());
        self::assertFalse($formRuntime->getFormDefinition()->getElementByIdentifier('address-2-position')?->isEnabled());
    }

    #[Test]
    public function submitOfAddressPageWithUnknownAddressStaysOnAddressPage(): void
    {
        $this->mockGeoCodeResult(new GeoCodeResult(
            null,
            new ClientError(ClientErrorEnum::NO_POSITIONS_FOUND, 'ZERO_RESULTS', 'No positions'),
        ));

        $formRuntime = $this->submitAddressPage($this->getValuesOfAddressPage());

        self::assertSame('club-data', $formRuntime->getCurrentPage()?->getIdentifier());
        $extbaseRequestParameters = $formRuntime->getRequest()->getAttribute('extbase');
        self::assertInstanceOf(ExtbaseRequestParameters::class, $extbaseRequestParameters);
        $errors = $extbaseRequestParameters->getOriginalRequestMappingResults()
            ->forProperty('newClub.address-1-street')
            ->getErrors();
        self::assertCount(1, $errors);
        self::assertStringStartsWith('Address not found: ', $errors[0]->getMessage());
        self::assertNull($formRuntime->getElementValue('address-1-position'));
    }

    #[Test]
    public function submitOfAddressPageWithAddressTypeAndWithoutCityStaysOnAddressPage(): void
    {
        $geoCodeService = $this->createMock(GeoCodeService::class);
        $geoCodeService->expects(self::never())->method('getGeoCodeResult');
        $this->getContainer()->set(GeoCodeService::class, $geoCodeService);

        $formRuntime = $this->submitAddressPage(array_replace($this->getValuesOfAddressPage(), ['address-1-city' => '']));

        self::assertSame('club-data', $formRuntime->getCurrentPage()?->getIdentifier());
    }
}
