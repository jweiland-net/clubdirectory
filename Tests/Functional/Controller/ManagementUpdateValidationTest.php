<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Controller;

use JWeiland\Clubdirectory\Tests\Functional\Traits\ClubAccessTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Submits the edit form of the Management plugin with invalid link fields.
 * Extbase forwards back to the edit form, which shows the error messages.
 */
class ManagementUpdateValidationTest extends FunctionalTestCase
{
    use ClubAccessTestTrait;

    private const PLUGIN_NAMESPACE = 'tx_clubdirectory_management';

    private const URL_ERROR_MESSAGE = 'Please enter the complete URL including https://.';

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/ClubAccess.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/MapCreate.csv');
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => ['EXT:clubdirectory/Tests/Functional/Fixtures/Frontend/Constants.typoscript'],
                'setup' => ['EXT:clubdirectory/Tests/Functional/Fixtures/Frontend/Management.typoscript'],
            ],
        );
        $this->get(SiteWriter::class)->write('clubdirectory', [
            'rootPageId' => 1,
            'base' => 'https://www.example.com/',
            'languages' => [
                [
                    'languageId' => 0,
                    'title' => 'English',
                    'locale' => 'en_US.UTF-8',
                    'base' => '/',
                ],
            ],
        ]);
    }

    private function requestEditForm(): string
    {
        $queryParameters = [
            'id' => 1,
            self::PLUGIN_NAMESPACE . '[action]' => 'edit',
            self::PLUGIN_NAMESPACE . '[club]' => self::HIDDEN_CLUB,
            self::PLUGIN_NAMESPACE . '[controller]' => 'Management',
        ];
        $queryParameters['cHash'] = $this->get(CacheHashCalculator::class)
            ->generateForParameters(HttpUtility::buildQueryString($queryParameters));

        return (string)$this->executeFrontendSubRequest(
            (new InternalRequest('https://www.example.com/'))->withQueryParameters($queryParameters),
            (new InternalRequestContext())->withFrontendUserId(self::OWNER),
        )->getBody();
    }

    /**
     * @param array<string, string> $overrideFields
     */
    private function submitEditForm(string $html, array $overrideFields): ResponseInterface
    {
        $document = new \DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML($html);
        $form = (new \DOMXPath($document))->query('//form[contains(@action, "update")]')->item(0);
        self::assertInstanceOf(\DOMElement::class, $form);

        $queryParameters = [];
        foreach (explode('&', (string)parse_url($form->getAttribute('action'), PHP_URL_QUERY)) as $queryParameter) {
            [$name, $value] = array_map('urldecode', explode('=', $queryParameter, 2) + [1 => '']);
            $queryParameters[$name] = $value;
        }
        parse_str(http_build_query(array_replace($this->getFormFields($form), $overrideFields)), $parsedBody);

        $request = (new InternalRequest('https://www.example.com/'))
            ->withMethod('POST')
            ->withQueryParameters($queryParameters)
            ->withParsedBody($parsedBody);
        // The testing framework can not build a body of nested arrays
        $request->getBody()->write(http_build_query($parsedBody));

        return $this->executeFrontendSubRequest(
            $request,
            (new InternalRequestContext())->withFrontendUserId(self::OWNER),
        );
    }

    /**
     * Returns the values of the input and textarea fields of the form
     *
     * @return array<string, string>
     */
    private function getFormFields(\DOMElement $form): array
    {
        $fields = [];
        foreach ((new \DOMXPath($form->ownerDocument))->query('.//input[@name]|.//textarea[@name]', $form) as $field) {
            if ($field instanceof \DOMElement && $field->getAttribute('type') !== 'file') {
                $value = $field->tagName === 'textarea' ? $field->textContent : $field->getAttribute('value');
                $fields[$field->getAttribute('name')] = $value;
            }
        }

        return $fields;
    }

    public static function invalidUrlDataProvider(): array
    {
        return [
            'website' => ['website'],
            'facebook' => ['facebook'],
            'twitter' => ['twitter'],
            'instagram' => ['instagram'],
        ];
    }

    #[Test]
    #[DataProvider('invalidUrlDataProvider')]
    public function updateWithInvalidUrlShowsErrorMessageInEditForm(string $propertyName): void
    {
        $response = $this->submitEditForm(
            $this->requestEditForm(),
            [self::PLUGIN_NAMESPACE . '[club][' . $propertyName . ']' => 'no url'],
        );

        $body = (string)$response->getBody();
        self::assertMatchesRegularExpression(
            '#<li>\s*' . $propertyName . ':\s*<ul>\s*<li>' . preg_quote(self::URL_ERROR_MESSAGE, '#') . '</li>#',
            $body,
        );
        self::assertSame(1, substr_count($body, self::URL_ERROR_MESSAGE));
    }
}
