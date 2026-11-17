<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\Form;

use JWeiland\Clubdirectory\Form\RichTextFormElementConfigurator;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 */
class RichTextFormElementConfiguratorTest extends UnitTestCase
{
    private RichTextFormElementConfigurator $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->buildSubject(true);
    }

    #[Test]
    public function isEnabledWithoutRequestReturnsFalse(): void
    {
        self::assertFalse($this->subject->isEnabled(null));
    }

    #[Test]
    public function isEnabledWithoutTypoScriptReturnsFalse(): void
    {
        self::assertFalse($this->subject->isEnabled(new ServerRequest('https://example.com/')));
    }

    #[Test]
    public function isEnabledWithDisabledSettingReturnsFalse(): void
    {
        self::assertFalse($this->subject->isEnabled($this->buildRequest('0')));
    }

    #[Test]
    public function isEnabledWithoutRteCkeditorReturnsFalse(): void
    {
        self::assertFalse($this->buildSubject(false)->isEnabled($this->buildRequest('1')));
    }

    #[Test]
    public function isEnabledWithEnabledSettingReturnsTrue(): void
    {
        self::assertTrue($this->subject->isEnabled($this->buildRequest('1')));
    }

    #[Test]
    public function getConfiguredElementIdentifiersReturnsTrimmedIdentifiers(): void
    {
        self::assertSame(
            ['description', 'program'],
            $this->subject->getConfiguredElementIdentifiers($this->buildRequest('1', ' description, program,')),
        );
    }

    #[Test]
    public function configureWithDefaultElementSwitchesDescriptionToRichText(): void
    {
        $configuration = $this->subject->configure($this->getFormDefinition(), $this->buildRequest('1'));

        self::assertSame('richText', $this->getDataType($configuration, 'description'));
        self::assertSame('plainText', $this->getDataType($configuration, 'activity'));

        $elements = $this->getClubElementsOfFinisher($configuration);
        self::assertTrue($elements['description']['sanitizeRichText']);
        self::assertArrayNotHasKey('convertPlainTextToHtml', $elements['description']);
        self::assertTrue($elements['activity']['normalizePlainText']);
        self::assertArrayNotHasKey('sanitizeRichText', $elements['activity']);
    }

    #[Test]
    public function configureWithTwoConfiguredElementsSwitchesBothToRichText(): void
    {
        $configuration = $this->subject->configure(
            $this->getFormDefinition(),
            $this->buildRequest('1', 'activity, description'),
        );

        $elements = $this->getClubElementsOfFinisher($configuration);
        foreach (['activity', 'description'] as $identifier) {
            self::assertSame('richText', $this->getDataType($configuration, $identifier));
            self::assertTrue($elements[$identifier]['sanitizeRichText']);
            self::assertArrayNotHasKey('normalizePlainText', $elements[$identifier]);
            self::assertArrayNotHasKey('convertPlainTextToHtml', $elements[$identifier]);
        }
    }

    #[Test]
    public function configureWithDataTypeRichTextInFormDefinitionSwitchesElementToRichText(): void
    {
        $formDefinition = $this->addProgramElement($this->getFormDefinition(), ['convertPlainTextToHtml' => true]);

        $configuration = $this->subject->configure($formDefinition, $this->buildRequest('1', 'description'));

        $elements = $this->getClubElementsOfFinisher($configuration);
        self::assertSame('richText', $this->getDataType($configuration, 'program'));
        self::assertSame(['mapOnDatabaseColumn' => 'tx_myext_program', 'sanitizeRichText' => true], $elements['program']);
        self::assertTrue($elements['description']['sanitizeRichText']);
    }

    #[Test]
    public function configureWhileDisabledFallsBackToPlainText(): void
    {
        $formDefinition = $this->addProgramElement($this->getFormDefinition(), ['sanitizeRichText' => true]);

        $configuration = $this->subject->configure($formDefinition, $this->buildRequest('0'));

        self::assertSame('plainText', $this->getDataType($configuration, 'program'));
        self::assertSame('plainText', $this->getDataType($configuration, 'description'));

        $elements = $this->getClubElementsOfFinisher($configuration);
        self::assertSame(['mapOnDatabaseColumn' => 'tx_myext_program', 'convertPlainTextToHtml' => true], $elements['program']);
        self::assertTrue($elements['description']['convertPlainTextToHtml']);
        self::assertArrayNotHasKey('sanitizeRichText', $elements['description']);
    }

    #[Test]
    public function configureKeepsOtherElements(): void
    {
        $formDefinition = $this->getFormDefinition();
        $configuration = $this->subject->configure($formDefinition, $this->buildRequest('1'));

        self::assertSame($this->findElement($formDefinition, 'title'), $this->findElement($configuration, 'title'));
        self::assertSame(
            $this->getClubElementsOfFinisher($formDefinition)['title'],
            $this->getClubElementsOfFinisher($configuration)['title'],
        );
    }

    private function buildSubject(bool $isRteCkeditorActive): RichTextFormElementConfigurator
    {
        $packageManager = $this->createMock(PackageManager::class);
        $packageManager->method('isPackageActive')->with('rte_ckeditor')->willReturn($isRteCkeditorActive);

        return new RichTextFormElementConfigurator($packageManager);
    }

    /**
     * @return array<string, mixed>
     */
    private function getFormDefinition(): array
    {
        return Yaml::parseFile(__DIR__ . '/../../../Configuration/Form/NewClub.form.yaml');
    }

    /**
     * Adds an own element with "dataType: richText" and maps it in the SaveClubFinisher
     *
     * @param array<string, mixed> $formDefinition
     * @param array<string, mixed> $mapping
     * @return array<string, mixed>
     */
    private function addProgramElement(array $formDefinition, array $mapping): array
    {
        $formDefinition['renderables'][0]['renderables'][] = [
            'type' => 'Textarea',
            'identifier' => 'program',
            'properties' => ['dbMapping' => ['column' => 'tx_myext_program', 'dataType' => 'richText']],
        ];
        foreach ($formDefinition['finishers'][1]['options'] as $key => $option) {
            if (($option['table'] ?? '') === 'tx_clubdirectory_domain_model_club') {
                $formDefinition['finishers'][1]['options'][$key]['elements']['program']
                    = ['mapOnDatabaseColumn' => 'tx_myext_program'] + $mapping;
            }
        }

        return $formDefinition;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function getDataType(array $configuration, string $identifier): string
    {
        return (string)($this->findElement($configuration, $identifier)['properties']['dbMapping']['dataType'] ?? '');
    }

    /**
     * @param array<string, mixed> $configuration
     * @return array<string, mixed>
     */
    private function findElement(array $configuration, string $identifier): array
    {
        foreach ($configuration['renderables'] ?? [] as $renderable) {
            if (($renderable['identifier'] ?? '') === $identifier) {
                return $renderable;
            }
            $element = $this->findElement($renderable, $identifier);
            if ($element !== []) {
                return $element;
            }
        }

        return [];
    }

    /**
     * @param array<string, mixed> $configuration
     * @return array<string, array<string, mixed>>
     */
    private function getClubElementsOfFinisher(array $configuration): array
    {
        foreach ($configuration['finishers'][1]['options'] as $option) {
            if (($option['table'] ?? '') === 'tx_clubdirectory_domain_model_club') {
                return $option['elements'];
            }
        }

        return [];
    }

    private function buildRequest(string $enable, string $elements = 'description'): ServerRequest
    {
        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray([
            'plugin.' => [
                'tx_clubdirectory.' => [
                    'settings.' => [
                        'new.' => [
                            'richText.' => [
                                'enable' => $enable,
                                'elements' => $elements,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        return (new ServerRequest('https://example.com/'))->withAttribute('frontend.typoscript', $frontendTypoScript);
    }
}
