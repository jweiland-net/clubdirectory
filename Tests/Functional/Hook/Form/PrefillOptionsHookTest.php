<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Hook\Form;

use JWeiland\Clubdirectory\Configuration\ExtConf;
use JWeiland\Clubdirectory\Hook\Form\PrefillOptionsHook;
use JWeiland\Clubdirectory\Tests\Functional\Traits\ClubFormTestTrait;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case for PrefillOptionsHook
 */
class PrefillOptionsHookTest extends FunctionalTestCase
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/ClubAccess.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/ClubForm.csv');
    }

    /**
     * @return array<int|string, mixed>
     */
    private function getOptions(string $identifier, int $rootCategory = 0, string $formOwner = 'clubdirectory'): array
    {
        $element = $this->createFormDefinition($formOwner)->getElementByIdentifier($identifier);
        self::assertNotNull($element);

        $subject = new PrefillOptionsHook(
            $this->get(ConnectionPool::class),
            $this->get(PageRepository::class),
            new ExtConf(rootCategory: $rootCategory),
        );
        $subject->initializeFormElement($element);

        return $element->getProperties()['options'] ?? [];
    }

    #[Test]
    public function initializeFormElementAddsChildrenOfRootCategorySortedByTitle(): void
    {
        self::assertSame([8 => 'Music', 7 => 'Sports'], $this->getOptions('categories'));
    }

    #[Test]
    public function initializeFormElementAddsChildrenOfConfiguredRootCategory(): void
    {
        self::assertSame([9 => 'Category of other root'], $this->getOptions('categories', 3));
    }

    #[Test]
    public function initializeFormElementAddsDistrictsSortedByTitle(): void
    {
        $element = $this->createFormDefinition()->getPageByIndex(0)->createElement('district', 'SingleSelect');
        (new PrefillOptionsHook($this->get(ConnectionPool::class), $this->get(PageRepository::class), new ExtConf()))
            ->initializeFormElement($element);

        self::assertSame([2 => 'Altstadt', 1 => 'Wiehre'], $element->getProperties()['options']);
    }

    #[Test]
    public function initializeFormElementIgnoresFormOfOtherExtension(): void
    {
        // The options of the test form definition are kept
        self::assertSame([7 => 'Sports', 8 => 'Music'], $this->getOptions('categories', 0, 'events2'));
    }

    #[Test]
    public function initializeFormElementIgnoresFormWithoutOwner(): void
    {
        self::assertSame([7 => 'Sports', 8 => 'Music'], $this->getOptions('categories', 0, ''));
    }
}
