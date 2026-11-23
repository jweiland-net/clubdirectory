<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Form;

use JWeiland\Clubdirectory\Service\ClubAccessService;
use JWeiland\Clubdirectory\Tests\Functional\Traits\ClubAccessTestTrait;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Form\Domain\Runtime\FormState;
use TYPO3\CMS\Form\Security\HashScope;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Submits the last page of the club form in frontend and follows the
 * activation link of the moderation mail. The mail is written into a mbox
 * file. The map step needs the form element "Maps2PoiPosition" of EXT:maps2
 * 12.3.0, so the tests are skipped with older versions.
 */
class ClubFormModerationMailTest extends FunctionalTestCase
{
    use ClubAccessTestTrait;

    private const PLUGIN_NAMESPACE = 'tx_clubdirectory_management';

    private const MBOX_FILE = '/tmp/clubdirectory-functional-test.mbox';

    protected array $coreExtensionsToLoad = [
        'typo3/cms-form',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    protected array $configurationToUseInTestInstance = [
        'MAIL' => [
            'transport' => 'mbox',
            'transport_mbox_file' => self::MBOX_FILE,
            'defaultMailFromAddress' => 'noreply@example.com',
            'defaultMailFromName' => 'Club directory',
        ],
        'EXTENSIONS' => [
            'clubdirectory' => [
                'poiCollectionPid' => '1',
                'rootCategory' => '0',
                'emailFromAddress' => 'sender@example.com',
                'emailFromName' => 'Sender',
                'emailToAddress' => 'moderator@example.com',
                'emailToName' => 'Moderator',
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (version_compare(ExtensionManagementUtility::getExtensionVersion('maps2'), '12.3.0', '<')) {
            self::markTestSkipped('The form element "Maps2PoiPosition" of EXT:maps2 >= 12.3.0 is not available.');
        }

        @unlink(self::MBOX_FILE);
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/ClubAccess.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/ClubForm.csv');
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => ['EXT:clubdirectory/Tests/Functional/Fixtures/Frontend/Constants.typoscript'],
                'setup' => ['EXT:clubdirectory/Tests/Functional/Fixtures/Frontend/ClubForm.typoscript'],
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

    protected function tearDown(): void
    {
        @unlink(self::MBOX_FILE);
        parent::tearDown();
    }

    /**
     * @param array<string, mixed> $pluginArguments Arguments of the query string
     * @param array<string, mixed> $parsedBody
     */
    private function request(
        string $actionName,
        array $pluginArguments = [],
        array $parsedBody = [],
        int $frontendUserUid = 0,
    ): ResponseInterface {
        $queryParameters = [
            'id' => 1,
            self::PLUGIN_NAMESPACE . '[action]' => $actionName,
            self::PLUGIN_NAMESPACE . '[controller]' => 'Management',
        ];
        foreach ($pluginArguments as $argumentName => $value) {
            $queryParameters[self::PLUGIN_NAMESPACE . '[' . $argumentName . ']'] = $value;
        }
        $queryParameters['cHash'] = $this->get(CacheHashCalculator::class)
            ->generateForParameters(HttpUtility::buildQueryString($queryParameters));

        $request = (new InternalRequest('https://www.example.com/'))->withQueryParameters($queryParameters);
        if ($parsedBody !== []) {
            $request = $request->withMethod('POST')->withParsedBody($parsedBody);
            // The testing framework can not build a body of nested arrays
            $request->getBody()->write(http_build_query($parsedBody));
        }

        $context = new InternalRequestContext();
        if ($frontendUserUid > 0) {
            $context = $context->withFrontendUserId($frontendUserUid);
        }

        return $this->executeFrontendSubRequest($request, $context);
    }

    /**
     * The values of the first page are part of the signed form state, so the
     * second page with the map can be submitted without geocoding.
     */
    private function submitMapPage(): void
    {
        $formState = new FormState();
        $formState->setLastDisplayedPageIndex(1);
        $valuesOfFirstPage = [
            'title' => 'Chess club',
            'description' => 'Every Monday',
            'address-1-title' => 'clubAddress',
            'address-1-street' => 'Main street',
            'address-1-house-number' => '1',
            'address-1-zip' => '79100',
            'address-1-city' => 'Freiburg',
        ];
        foreach ($valuesOfFirstPage as $identifier => $value) {
            $formState->setFormValue($identifier, $value);
        }

        $this->request('perform', [], [
            self::PLUGIN_NAMESPACE => [
                'newClub' => [
                    '__state' => $this->get(HashService::class)->appendHmac(
                        base64_encode(serialize($formState)),
                        HashScope::FormState->prefix(),
                    ),
                    '__currentPage' => '2',
                    'address-1-position' => ['latitude' => '47.991234', 'longitude' => '7.841234'],
                ],
            ],
        ], self::OWNER);
    }

    /**
     * Returns the decoded text and HTML part of the sent mail
     */
    private function getMail(): string
    {
        self::assertFileExists(self::MBOX_FILE, 'No mail was sent');
        $mail = (string)file_get_contents(self::MBOX_FILE);

        return quoted_printable_decode($mail);
    }

    /**
     * Returns the decoded body of the MIME part with the given content type
     */
    private function getMailPart(string $contentType): string
    {
        self::assertFileExists(self::MBOX_FILE, 'No mail was sent');
        $mail = str_replace("\r\n", "\n", (string)file_get_contents(self::MBOX_FILE));
        self::assertMatchesRegularExpression('/boundary=([^\s;]+)/', $mail);
        preg_match('/boundary=([^\s;]+)/', $mail, $matches);
        $boundary = trim($matches[1], '"');

        foreach (explode('--' . $boundary, $mail) as $part) {
            [$headers, $body] = array_pad(explode("\n\n", ltrim($part, "\n"), 2), 2, '');
            if (stripos($headers, 'Content-Type: ' . $contentType) === false) {
                continue;
            }

            return match (true) {
                stripos($headers, 'Content-Transfer-Encoding: quoted-printable') !== false => quoted_printable_decode($body),
                stripos($headers, 'Content-Transfer-Encoding: base64') !== false => (string)base64_decode($body),
                default => $body,
            };
        }

        self::fail('The mail has no part of type ' . $contentType);
    }

    /**
     * @return array<string, mixed>
     */
    private function getNewClub(): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_clubdirectory_domain_model_club');
        $queryBuilder->getRestrictions()->removeAll();
        $club = $queryBuilder
            ->select('*')
            ->from('tx_clubdirectory_domain_model_club')
            ->where($queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter('Chess club')))
            ->executeQuery()
            ->fetchAssociative();
        self::assertIsArray($club, 'The club was not stored');

        return $club;
    }

    #[Test]
    public function submitOfClubFormSendsMailWithActivationLinkOfClubdirectory(): void
    {
        $this->submitMapPage();

        $club = $this->getNewClub();
        $moderationToken = $this->get(ClubAccessService::class)->generateModerationToken((int)$club['uid']);
        $mail = $this->getMail();

        self::assertSame(1, (int)$club['hidden']);
        self::assertNotSame('', $moderationToken);
        self::assertStringContainsString('To: Moderator <moderator@example.com>', $mail);
        self::assertStringContainsString(urlencode(self::PLUGIN_NAMESPACE . '[action]') . '=confirmActivation', $mail);
        self::assertStringContainsString(urlencode(self::PLUGIN_NAMESPACE . '[club]') . '=' . $club['uid'], $mail);
        self::assertStringContainsString(
            urlencode(self::PLUGIN_NAMESPACE . '[moderationToken]') . '=' . $moderationToken,
            $mail,
        );
        self::assertStringContainsString(urlencode(self::PLUGIN_NAMESPACE . '[action]') . '=perform', $mail);
        self::assertStringNotContainsString('tx_yellowpages2', $mail);
        self::assertStringNotContainsString('tx_events2', $mail);
    }

    /**
     * Plain text clients show the text part as it is. HTML entities like
     * "&amp;" in the links would break the parameters of the links.
     */
    #[Test]
    public function textPartOfMailContainsLinksWithoutHtmlEntities(): void
    {
        $this->submitMapPage();

        $club = $this->getNewClub();
        $moderationToken = $this->get(ClubAccessService::class)->generateModerationToken((int)$club['uid']);
        $textPart = $this->getMailPart('text/plain');

        self::assertStringContainsString(
            urlencode(self::PLUGIN_NAMESPACE . '[action]') . '=confirmActivation&'
            . urlencode(self::PLUGIN_NAMESPACE . '[club]') . '=' . $club['uid'] . '&',
            $textPart,
        );
        self::assertStringContainsString(
            urlencode(self::PLUGIN_NAMESPACE . '[moderationToken]') . '=' . $moderationToken,
            $textPart,
        );
        self::assertStringContainsString(urlencode(self::PLUGIN_NAMESPACE . '[action]') . '=perform&', $textPart);
        self::assertStringContainsString('Chess club', $textPart);
        self::assertStringNotContainsString('&amp;', $textPart);
        self::assertStringNotContainsString('&quot;', $textPart);
        self::assertStringNotContainsString('&#039;', $textPart);
    }

    #[Test]
    public function activationLinkOfMailShowsConfirmButtonAndActivatesClubWithMessage(): void
    {
        $this->submitMapPage();
        $club = $this->getNewClub();
        $moderationToken = $this->get(ClubAccessService::class)->generateModerationToken((int)$club['uid']);

        $content = (string)$this->request('confirmActivation', [
            'club' => (string)$club['uid'],
            'moderationToken' => $moderationToken,
        ])->getBody();

        self::assertStringContainsString('Chess club', $content);
        self::assertMatchesRegularExpression('/<input[^>]*type="submit"[^>]*value="Activate club"/', $content);
        self::assertStringContainsString('name="' . self::PLUGIN_NAMESPACE . '[clubUid]" value="' . $club['uid'] . '"', $content);

        $this->request('activate', [], [
            self::PLUGIN_NAMESPACE => [
                'clubUid' => (string)$club['uid'],
                'moderationToken' => $moderationToken,
            ],
        ]);

        self::assertSame(0, (int)$this->getNewClub()['hidden']);
        $sessionData = $this->getConnectionPool()
            ->getConnectionForTable('fe_sessions')
            ->select(['ses_data'], 'fe_sessions')
            ->fetchFirstColumn();
        self::assertStringContainsString(
            'The club was activated and is now visible in frontend',
            implode('', array_map('strval', $sessionData)),
        );
    }
}
