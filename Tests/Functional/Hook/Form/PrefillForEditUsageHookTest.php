<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Hook\Form;

use JWeiland\Clubdirectory\Hook\Form\PrefillForEditUsageHook;
use JWeiland\Clubdirectory\Service\ClubAccessService;
use JWeiland\Clubdirectory\Tests\Functional\Traits\ClubAccessTestTrait;
use JWeiland\Clubdirectory\Tests\Functional\Traits\ClubFormTestTrait;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case for PrefillForEditUsageHook
 */
class PrefillForEditUsageHookTest extends FunctionalTestCase
{
    use ClubAccessTestTrait;
    use ClubFormTestTrait;

    protected array $coreExtensionsToLoad = [
        'typo3/cms-form',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    private FormDefinition $formDefinition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/ClubAccess.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/ClubForm.csv');
        $this->getConnectionPool()
            ->getConnectionForTable('tx_clubdirectory_domain_model_club')
            ->update(
                'tx_clubdirectory_domain_model_club',
                ['description' => "<p>First line<br />Second line</p>\r\n<p>Second paragraph</p>"],
                ['uid' => self::VISIBLE_CLUB],
            );

        $this->formDefinition = $this->createFormDefinition();
        $dbMappings = [
            'title' => ['column' => 'title'],
            'description' => ['column' => 'description', 'dataType' => 'plainText'],
            'address-1-title' => ['column' => 'title', 'address' => 1],
            'address-1-zip' => ['column' => 'zip', 'address' => 1],
            'address-1-position' => ['address' => 1, 'dataType' => 'poiCollection'],
            'categories' => [
                'relation' => [
                    'table' => 'sys_category_record_mm',
                    'valueColumn' => 'uid_local',
                    'expressions' => [
                        ['column' => 'tablenames', 'expression' => 'eq', 'value' => 'tx_clubdirectory_domain_model_club'],
                        ['column' => 'fieldname', 'expression' => 'eq', 'value' => 'categories'],
                        ['column' => 'uid_foreign', 'expression' => 'eq', 'value' => '{club:uid}'],
                    ],
                ],
            ],
        ];
        foreach ($dbMappings as $identifier => $dbMapping) {
            $this->getElement($identifier)->setProperty('dbMapping', $dbMapping);
        }
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);
        parent::tearDown();
    }

    private function getElement(string $identifier): FormElementInterface
    {
        $element = $this->formDefinition->getElementByIdentifier($identifier);
        self::assertInstanceOf(FormElementInterface::class, $element);

        return $element;
    }

    private function prefill(int $frontendUserUid, int $clubUid, string $moderationToken = ''): void
    {
        $this->setUserAspects($this->get(Context::class), $frontendUserUid);
        $GLOBALS['TYPO3_REQUEST'] = $this->createFormRequest($clubUid, $moderationToken);

        $subject = $this->get(PrefillForEditUsageHook::class);
        foreach ($this->formDefinition->getRenderablesRecursively() as $renderable) {
            $subject->afterBuildingFinished($renderable);
        }
    }

    #[Test]
    public function afterBuildingFinishedPrefillsColumnsOfClub(): void
    {
        $this->prefill(self::OWNER, self::VISIBLE_CLUB);

        self::assertSame('Swimming club', $this->getElement('title')->getDefaultValue());
        self::assertSame(
            "First line\nSecond line\n\nSecond paragraph",
            $this->getElement('description')->getDefaultValue(),
        );
    }

    #[Test]
    public function afterBuildingFinishedPrefillsAddressAndPosition(): void
    {
        $this->prefill(self::OWNER, self::VISIBLE_CLUB);

        self::assertSame('organizationAddress', $this->getElement('address-1-title')->getDefaultValue());
        self::assertSame('79100', $this->getElement('address-1-zip')->getDefaultValue());
        self::assertSame(
            ['latitude' => '47.900000', 'longitude' => '7.800000'],
            $this->getElement('address-1-position')->getDefaultValue(),
        );
    }

    #[Test]
    public function afterBuildingFinishedPrefillsCategories(): void
    {
        $this->prefill(self::OWNER, self::VISIBLE_CLUB);

        self::assertSame(['8'], $this->getElement('categories')->getDefaultValue());
    }

    #[Test]
    public function afterBuildingFinishedWithForeignUserDoesNotPrefill(): void
    {
        $this->prefill(self::FOREIGN_USER, self::VISIBLE_CLUB);

        self::assertNull($this->getElement('title')->getDefaultValue());
        self::assertNull($this->getElement('address-1-position')->getDefaultValue());
    }

    #[Test]
    public function afterBuildingFinishedWithModerationTokenPrefillsHiddenClubWithoutLogin(): void
    {
        $moderationToken = $this->get(ClubAccessService::class)
            ->generateModerationToken(self::HIDDEN_CLUB);

        $this->prefill(0, self::HIDDEN_CLUB, $moderationToken);

        self::assertSame('Hidden club', $this->getElement('title')->getDefaultValue());
    }

    #[Test]
    public function afterBuildingFinishedWithoutClubDoesNotPrefill(): void
    {
        $this->prefill(self::OWNER, 0);

        self::assertNull($this->getElement('title')->getDefaultValue());
    }

    #[Test]
    public function afterBuildingFinishedIgnoresFormOfOtherExtension(): void
    {
        $this->formDefinition = $this->createFormDefinition('events2');
        $this->getElement('title')->setProperty('dbMapping', ['column' => 'title']);

        $this->prefill(self::OWNER, self::VISIBLE_CLUB);

        self::assertNull($this->getElement('title')->getDefaultValue());
    }
}
