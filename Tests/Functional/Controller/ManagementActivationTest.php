<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Controller;

use JWeiland\Clubdirectory\Service\ClubAccessService;
use JWeiland\Clubdirectory\Tests\Functional\Traits\ClubAccessTestTrait;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Mail security scanners open the activation link of the mail. So a GET
 * request must never activate a club, only the POST of the confirmation.
 */
class ManagementActivationTest extends FunctionalTestCase
{
    use ClubAccessTestTrait;

    private const PLUGIN_NAMESPACE = 'tx_clubdirectory_management';

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/ClubAccess.csv');
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

    /**
     * @param array<string, mixed> $pluginArguments
     */
    private function requestAction(string $actionName, array $pluginArguments, string $method = 'GET'): ResponseInterface
    {
        $queryParameters = [
            'id' => 1,
            self::PLUGIN_NAMESPACE . '[action]' => $actionName,
            self::PLUGIN_NAMESPACE . '[controller]' => 'Management',
        ];
        $request = new InternalRequest('https://www.example.com/');

        if ($method === 'POST') {
            $request = $request->withMethod('POST')->withParsedBody([self::PLUGIN_NAMESPACE => $pluginArguments]);
        } else {
            foreach ($pluginArguments as $argumentName => $value) {
                $queryParameters[self::PLUGIN_NAMESPACE . '[' . $argumentName . ']'] = $value;
            }
        }

        $queryParameters['cHash'] = $this->get(CacheHashCalculator::class)
            ->generateForParameters(HttpUtility::buildQueryString($queryParameters));

        return $this->executeFrontendSubRequest($request->withQueryParameters($queryParameters));
    }

    /**
     * @return array<string, string>
     */
    private function getActivationArguments(): array
    {
        return [
            'club' => (string)self::HIDDEN_CLUB,
            'moderationToken' => $this->get(ClubAccessService::class)->generateModerationToken(self::HIDDEN_CLUB),
        ];
    }

    private function isClubHidden(int $clubUid): bool
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_clubdirectory_domain_model_club');
        $queryBuilder->getRestrictions()->removeAll();
        $hidden = $queryBuilder
            ->select('hidden')
            ->from('tx_clubdirectory_domain_model_club')
            ->where($queryBuilder->expr()->eq('uid', $clubUid))
            ->executeQuery()
            ->fetchOne();

        return (int)$hidden === 1;
    }

    #[Test]
    public function getConfirmActivationShowsFormAndKeepsClubHidden(): void
    {
        $arguments = $this->getActivationArguments();

        $response = $this->requestAction('confirmActivation', $arguments);
        $content = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Hidden club', $content);
        self::assertStringContainsString('method="post"', $content);
        self::assertStringContainsString('value="' . $arguments['moderationToken'] . '"', $content);
        self::assertTrue($this->isClubHidden(self::HIDDEN_CLUB));
    }

    /**
     * Extbase sends the status code of redirects with header(), so the
     * response of the sub request can not be checked for the redirect.
     */
    #[Test]
    public function getActivateKeepsClubHidden(): void
    {
        $this->requestAction('activate', $this->getActivationArguments());

        self::assertTrue($this->isClubHidden(self::HIDDEN_CLUB));
    }

    #[Test]
    public function postActivateActivatesClub(): void
    {
        $this->requestAction('activate', $this->getActivationArguments(), 'POST');

        self::assertFalse($this->isClubHidden(self::HIDDEN_CLUB));
    }

    #[Test]
    public function postActivateWithoutModerationTokenDeniesAccess(): void
    {
        $response = $this->requestAction('activate', ['club' => (string)self::HIDDEN_CLUB], 'POST');

        self::assertSame(403, $response->getStatusCode());
        self::assertTrue($this->isClubHidden(self::HIDDEN_CLUB));
    }

    #[Test]
    public function getConfirmActivationWithoutModerationTokenDeniesAccess(): void
    {
        $response = $this->requestAction('confirmActivation', ['club' => (string)self::HIDDEN_CLUB]);

        self::assertSame(403, $response->getStatusCode());
    }
}
