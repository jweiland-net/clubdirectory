<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\EventListener;

use JWeiland\Clubdirectory\Domain\Model\Search;
use JWeiland\Clubdirectory\Event\PreProcessControllerActionEvent;
use JWeiland\Clubdirectory\EventListener\AllowSearchParameterEventListener;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Property\Exception as PropertyMapperException;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Map the "search" argument with the real Extbase PropertyMapper and
 * the PropertyMappingConfiguration prepared by AllowSearchParameterEventListener.
 */
class AllowSearchParameterEventListenerTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    private function createEvent(string $controllerName, string $actionName): PreProcessControllerActionEvent
    {
        $extbaseRequestParameters = (new ExtbaseRequestParameters())
            ->setPluginName('Search')
            ->setControllerExtensionName('Clubdirectory')
            ->setControllerName($controllerName)
            ->setControllerActionName($actionName);

        $serverRequest = (new ServerRequest('https://www.example.com/', 'GET'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('extbase', $extbaseRequestParameters);

        $arguments = new Arguments();
        $arguments->addNewArgument('search', Search::class, true);

        return new PreProcessControllerActionEvent(new Request($serverRequest), $arguments, []);
    }

    private function getPropertyMappingConfiguration(
        PreProcessControllerActionEvent $event,
    ): MvcPropertyMappingConfiguration {
        return $event->getArguments()->getArgument('search')->getPropertyMappingConfiguration();
    }

    /**
     * @param array<string, mixed> $source
     */
    private function mapSearch(array $source): Search
    {
        $event = $this->createEvent('Search', 'listSearchResults');
        (new AllowSearchParameterEventListener())($event);

        $search = $this->get(PropertyMapper::class)->convert(
            $source,
            Search::class,
            $this->getPropertyMappingConfiguration($event),
        );

        self::assertInstanceOf(Search::class, $search);

        return $search;
    }

    #[Test]
    public function dispatchOfEventConfiguresPropertyMappingOfSearchArgument(): void
    {
        $event = $this->createEvent('Search', 'listSearchResults');

        $this->get(EventDispatcherInterface::class)->dispatch($event);

        $propertyMappingConfiguration = $this->getPropertyMappingConfiguration($event);
        self::assertTrue($propertyMappingConfiguration->shouldMap('searchWord'));
        self::assertTrue($propertyMappingConfiguration->shouldSkipUnknownProperties());
    }

    #[Test]
    public function invokeAllowsPropertiesOfSearch(): void
    {
        $event = $this->createEvent('Search', 'listSearchResults');

        (new AllowSearchParameterEventListener())($event);

        $propertyMappingConfiguration = $this->getPropertyMappingConfiguration($event);
        foreach (['searchWord', 'district', 'category', 'order', 'orderBy'] as $propertyName) {
            self::assertTrue(
                $propertyMappingConfiguration->shouldMap($propertyName),
                'Property "' . $propertyName . '" is not allowed to be mapped',
            );
        }
        self::assertFalse($propertyMappingConfiguration->shouldMap('subCategory'));
        self::assertFalse($propertyMappingConfiguration->shouldMap('letter'));
    }

    #[Test]
    public function invokeSkipsUnknownProperties(): void
    {
        $event = $this->createEvent('Search', 'listSearchResults');

        (new AllowSearchParameterEventListener())($event);

        self::assertTrue($this->getPropertyMappingConfiguration($event)->shouldSkipUnknownProperties());
    }

    public static function otherControllerActionDataProvider(): array
    {
        return [
            'show action of Search controller' => ['Search', 'show'],
            'listSearchResults action of Club controller' => ['Club', 'listSearchResults'],
            'list action of Club controller' => ['Club', 'list'],
            'lowercase controller name' => ['search', 'listSearchResults'],
            'lowercase action name' => ['Search', 'listsearchresults'],
        ];
    }

    #[Test]
    #[DataProvider('otherControllerActionDataProvider')]
    public function invokeWithOtherControllerActionDoesNotChangePropertyMapping(
        string $controllerName,
        string $actionName,
    ): void {
        $event = $this->createEvent($controllerName, $actionName);

        (new AllowSearchParameterEventListener())($event);

        $propertyMappingConfiguration = $this->getPropertyMappingConfiguration($event);
        self::assertFalse($propertyMappingConfiguration->shouldMap('searchWord'));
        self::assertFalse($propertyMappingConfiguration->shouldSkipUnknownProperties());
    }

    #[Test]
    public function mappingOfSearchArgumentOfOtherActionThrowsException(): void
    {
        $event = $this->createEvent('Search', 'show');
        (new AllowSearchParameterEventListener())($event);

        $this->expectException(PropertyMapperException::class);
        $this->expectExceptionCode(1297759968);

        $this->get(PropertyMapper::class)->convert(
            ['searchWord' => 'swimming'],
            Search::class,
            $this->getPropertyMappingConfiguration($event),
        );
    }

    #[Test]
    public function mappingSetsAllowedPropertiesOfSearch(): void
    {
        $search = $this->mapSearch([
            'searchWord' => 'swimming',
            'category' => '4',
            'district' => '7',
            'orderBy' => 'title',
            'order' => QueryInterface::ORDER_DESCENDING,
        ]);

        self::assertSame('swimming', $search->getSearchWord());
        self::assertSame(4, $search->getCategory());
        self::assertSame(7, $search->getDistrict());
        self::assertSame('title', $search->getOrderBy());
        self::assertSame(QueryInterface::ORDER_DESCENDING, $search->getOrder());
    }

    #[Test]
    public function mappingWithEmptySourceReturnsSearchWithDefaults(): void
    {
        $search = $this->mapSearch([]);

        self::assertSame('', $search->getSearchWord());
        self::assertSame(0, $search->getCategory());
        self::assertSame(0, $search->getDistrict());
        self::assertSame('title', $search->getOrderBy());
        self::assertSame(QueryInterface::ORDER_ASCENDING, $search->getOrder());
    }

    public static function unknownPropertyDataProvider(): array
    {
        return [
            'removed property subCategory' => ['subCategory', '12'],
            'removed property letter' => ['letter', 'a'],
            'unknown property foo' => ['foo', 'bar'],
            'nested unknown property' => ['foo', ['bar' => 'baz']],
        ];
    }

    /**
     * Old links (e.g. of the page browser) may still contain removed properties
     */
    #[Test]
    #[DataProvider('unknownPropertyDataProvider')]
    public function mappingWithUnknownPropertySkipsProperty(string $propertyName, string|array $value): void
    {
        $search = $this->mapSearch([
            'searchWord' => 'swimming',
            'category' => '4',
            $propertyName => $value,
        ]);

        self::assertSame('swimming', $search->getSearchWord());
        self::assertSame(4, $search->getCategory());
    }

    #[Test]
    public function mappingWithAllUnknownPropertiesSkipsThemAll(): void
    {
        $search = $this->mapSearch([
            'district' => '7',
            'subCategory' => '12',
            'letter' => 'a',
            'foo' => 'bar',
        ]);

        self::assertSame(7, $search->getDistrict());
        self::assertSame('', $search->getSearchWord());
        self::assertSame(0, $search->getCategory());
    }

    public static function invalidOrderingDataProvider(): array
    {
        return [
            'orderBy with property of relation' => [
                ['orderBy' => 'feUsers.password', 'order' => QueryInterface::ORDER_DESCENDING],
                'title',
                QueryInterface::ORDER_DESCENDING,
            ],
            'order with SQL fragment' => [
                ['orderBy' => 'title', 'order' => 'DESC, uid'],
                'title',
                QueryInterface::ORDER_ASCENDING,
            ],
            'orderBy and order invalid' => [
                ['orderBy' => 'uid', 'order' => 'desc'],
                'title',
                QueryInterface::ORDER_ASCENDING,
            ],
        ];
    }

    /**
     * Property mapping has to use the setters of Search, which only allow whitelisted values
     *
     * @param array<string, string> $source
     */
    #[Test]
    #[DataProvider('invalidOrderingDataProvider')]
    public function mappingRestrictsOrderingToAllowedValues(
        array $source,
        string $expectedOrderBy,
        string $expectedOrder,
    ): void {
        $search = $this->mapSearch($source);

        self::assertSame($expectedOrderBy, $search->getOrderBy());
        self::assertSame($expectedOrder, $search->getOrder());
    }
}
