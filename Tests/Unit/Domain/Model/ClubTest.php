<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\Domain\Model;

use JWeiland\Clubdirectory\Domain\Model\Address;
use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Domain\Model\District;
use JWeiland\Clubdirectory\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case for model Club
 */
class ClubTest extends UnitTestCase
{
    /**
     * @var Club
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->subject = new Club();
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
        );
    }

    /**
     * @test
     */
    public function getHiddenInitiallyReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->getHidden(),
        );
    }

    /**
     * @test
     */
    public function setHiddenSetsHidden(): void
    {
        $this->subject->setHidden(true);
        self::assertTrue(
            $this->subject->getHidden(),
        );
    }

    /**
     * @test
     */
    public function getTitleInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getTitle(),
        );
    }

    /**
     * @test
     */
    public function setTitleSetsTitle(): void
    {
        $this->subject->setTitle('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getTitle(),
        );
    }

    /**
     * @test
     */
    public function getActivityInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getActivity(),
        );
    }

    /**
     * @test
     */
    public function setActivitySetsActivity(): void
    {
        $this->subject->setActivity('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getActivity(),
        );
    }

    /**
     * @test
     */
    public function getContactPersonInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getContactPerson(),
        );
    }

    /**
     * @test
     */
    public function setContactPersonSetsContactPerson(): void
    {
        $this->subject->setContactPerson('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getContactPerson(),
        );
    }

    /**
     * @test
     */
    public function getContactTimesInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getContactTimes(),
        );
    }

    /**
     * @test
     */
    public function setContactTimesSetsContactTimes(): void
    {
        $this->subject->setContactTimes('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getContactTimes(),
        );
    }

    /**
     * @test
     */
    public function getEmailInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getEmail(),
        );
    }

    /**
     * @test
     */
    public function setEmailSetsEmail(): void
    {
        $this->subject->setEmail('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getEmail(),
        );
    }

    /**
     * @test
     */
    public function getWebsiteInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getWebsite(),
        );
    }

    /**
     * @test
     */
    public function setWebsiteSetsWebsite(): void
    {
        $this->subject->setWebsite('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getWebsite(),
        );
    }

    /**
     * @test
     */
    public function getMembersInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getMembers(),
        );
    }

    /**
     * @test
     */
    public function setMembersSetsMembers(): void
    {
        $this->subject->setMembers('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getMembers(),
        );
    }

    /**
     * @test
     */
    public function getClubHomeInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getClubHome(),
        );
    }

    /**
     * @test
     */
    public function setClubHomeSetsClubHome(): void
    {
        $this->subject->setClubHome('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getClubHome(),
        );
    }

    /**
     * @test
     */
    public function getDescriptionInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getDescription(),
        );
    }

    /**
     * @test
     */
    public function setDescriptionSetsDescription(): void
    {
        $this->subject->setDescription('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getDescription(),
        );
    }

    /**
     * @test
     */
    public function getFeUsersInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getFeUsers(),
        );
    }

    /**
     * @test
     */
    public function setFeUsersSetsFeUsers(): void
    {
        $object = new FrontendUser();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setFeUsers($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getFeUsers(),
        );
    }

    /**
     * @test
     */
    public function addFrontendUserAddsOneFrontendUser(): void
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setFeUsers($objectStorage);

        $object = new FrontendUser();
        $this->subject->addFeUser($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getFeUsers(),
        );
    }

    /**
     * @test
     */
    public function removeFrontendUserRemovesOneFrontendUser(): void
    {
        $object = new FrontendUser();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setFeUsers($objectStorage);

        $this->subject->removeFeUser($object);

        $objectStorage->detach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getFeUsers(),
        );
    }

    /**
     * @test
     */
    public function getLogoInitiallyReturnsEmptyArray(): void
    {
        self::assertSame(
            [],
            $this->subject->getLogo(),
        );
    }

    /**
     * @test
     */
    public function getFirstLogoInitiallyReturnsNull(): void
    {
        self::assertNull(
            $this->subject->getFirstLogo(),
        );
    }

    /**
     * @test
     */
    public function getOriginalLogoInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getOriginalLogo(),
        );
    }

    /**
     * @test
     */
    public function setLogoSetsLogo(): void
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setLogo($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalLogo(),
        );
    }

    /**
     * @test
     */
    public function addLogoAddsOneLogo(): void
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setLogo($objectStorage);

        $object = new FileReference();
        $this->subject->addLogo($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalLogo(),
        );
    }

    /**
     * @test
     */
    public function removeLogoRemovesOneLogo(): void
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setLogo($objectStorage);

        $this->subject->removeLogo($object);

        $objectStorage->detach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalLogo(),
        );
    }

    /**
     * @test
     */
    public function getImagesInitiallyReturnsArray(): void
    {
        self::assertEquals(
            [],
            $this->subject->getImages(),
        );
    }

    /**
     * @test
     */
    public function setImagesSetsImages(): void
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setImages($objectStorage);

        self::assertSame(
            [$object],
            $this->subject->getImages(),
        );
    }

    /**
     * @test
     */
    public function addImageAddsOneImage(): void
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setImages($objectStorage);

        $object = new FileReference();
        $this->subject->addImage($object);

        $objectStorage->attach($object);

        self::assertSame(
            [$object],
            $this->subject->getImages(),
        );
    }

    /**
     * @test
     */
    public function removeImageRemovesOneImage(): void
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setImages($objectStorage);

        $this->subject->removeImage($object);

        $objectStorage->detach($object);

        self::assertSame(
            [],
            $this->subject->getImages(),
        );
    }

    /**
     * @test
     */
    public function getFacebookInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getFacebook(),
        );
    }

    /**
     * @test
     */
    public function setFacebookSetsFacebook(): void
    {
        $this->subject->setFacebook('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getFacebook(),
        );
    }

    /**
     * @test
     */
    public function getTwitterInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getTwitter(),
        );
    }

    /**
     * @test
     */
    public function setTwitterSetsTwitter(): void
    {
        $this->subject->setTwitter('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getTwitter(),
        );
    }

    /**
     * @test
     */
    public function getInstagramInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getInstagram(),
        );
    }

    /**
     * @test
     */
    public function setInstagramSetsInstagram(): void
    {
        $this->subject->setInstagram('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getInstagram(),
        );
    }

    /**
     * @test
     */
    public function getTagsInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getTags(),
        );
    }

    /**
     * @test
     */
    public function setTagsSetsTags(): void
    {
        $this->subject->setTags('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getTags(),
        );
    }

    /**
     * @test
     */
    public function getDistrictInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getDistrict());
    }

    /**
     * @test
     */
    public function setDistrictSetsDistrict(): void
    {
        $instance = new District();
        $this->subject->setDistrict($instance);

        self::assertSame(
            $instance,
            $this->subject->getDistrict(),
        );
    }

    /**
     * @test
     */
    public function getAddressesInitiallyReturnsArray(): void
    {
        self::assertEquals(
            [],
            $this->subject->getAddresses(),
        );
    }

    /**
     * @test
     */
    public function getOriginalAddressesInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getOriginalAddresses(),
        );
    }

    /**
     * @test
     */
    public function setAddressesSetsAddresses(): void
    {
        $object = new Address();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setAddresses($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalAddresses(),
        );
    }

    /**
     * @test
     */
    public function addAddressAddsOneAddress(): void
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setAddresses($objectStorage);

        $object = new Address();
        $this->subject->addAddress($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalAddresses(),
        );
    }

    /**
     * @test
     */
    public function removeAddressRemovesOneAddress(): void
    {
        $object = new Address();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setAddresses($objectStorage);

        $this->subject->removeAddress($object);

        $objectStorage->detach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalAddresses(),
        );
    }

    /**
     * @test
     */
    public function getCategoriesInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getCategories(),
        );
    }

    /**
     * @test
     */
    public function setCategoriesSetsCategories(): void
    {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setCategories($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getCategories(),
        );
    }

    /**
     * @test
     */
    public function addCategoryAddsOneCategory(): void
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setCategories($objectStorage);

        $object = new Category();
        $this->subject->addCategory($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getCategories(),
        );
    }

    /**
     * @test
     */
    public function removeCategoryRemovesOneCategory(): void
    {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);

        $this->subject->setCategories($objectStorage);

        $this->subject->removeCategory($object);

        $objectStorage->detach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getCategories(),
        );
    }
}
