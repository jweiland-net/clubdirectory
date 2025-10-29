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
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case for model Club
 */
class ClubTest extends UnitTestCase
{
    protected Club $subject;

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

    #[Test]
    public function getHiddenInitiallyReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->getHidden(),
        );
    }

    #[Test]
    public function setHiddenSetsHidden(): void
    {
        $this->subject->setHidden(true);
        self::assertTrue(
            $this->subject->getHidden(),
        );
    }

    #[Test]
    public function getTitleInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getTitle(),
        );
    }

    #[Test]
    public function setTitleSetsTitle(): void
    {
        $this->subject->setTitle('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getTitle(),
        );
    }

    #[Test]
    public function getActivityInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getActivity(),
        );
    }

    #[Test]
    public function setActivitySetsActivity(): void
    {
        $this->subject->setActivity('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getActivity(),
        );
    }

    #[Test]
    public function getContactPersonInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getContactPerson(),
        );
    }

    #[Test]
    public function setContactPersonSetsContactPerson(): void
    {
        $this->subject->setContactPerson('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getContactPerson(),
        );
    }

    #[Test]
    public function getContactTimesInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getContactTimes(),
        );
    }

    #[Test]
    public function setContactTimesSetsContactTimes(): void
    {
        $this->subject->setContactTimes('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getContactTimes(),
        );
    }

    #[Test]
    public function getEmailInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getEmail(),
        );
    }

    #[Test]
    public function setEmailSetsEmail(): void
    {
        $this->subject->setEmail('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getEmail(),
        );
    }

    #[Test]
    public function getWebsiteInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getWebsite(),
        );
    }

    #[Test]
    public function setWebsiteSetsWebsite(): void
    {
        $this->subject->setWebsite('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getWebsite(),
        );
    }

    #[Test]
    public function getMembersInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getMembers(),
        );
    }

    #[Test]
    public function setMembersSetsMembers(): void
    {
        $this->subject->setMembers('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getMembers(),
        );
    }

    #[Test]
    public function getClubHomeInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getClubHome(),
        );
    }

    #[Test]
    public function setClubHomeSetsClubHome(): void
    {
        $this->subject->setClubHome('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getClubHome(),
        );
    }

    #[Test]
    public function getDescriptionInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getDescription(),
        );
    }

    #[Test]
    public function setDescriptionSetsDescription(): void
    {
        $this->subject->setDescription('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getDescription(),
        );
    }

    #[Test]
    public function getFeUsersInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getFeUsers(),
        );
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function getLogoInitiallyReturnsEmptyArray(): void
    {
        self::assertSame(
            [],
            $this->subject->getLogo(),
        );
    }

    #[Test]
    public function getFirstLogoInitiallyReturnsNull(): void
    {
        self::assertNull(
            $this->subject->getFirstLogo(),
        );
    }

    #[Test]
    public function getOriginalLogoInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getOriginalLogo(),
        );
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function getImagesInitiallyReturnsArray(): void
    {
        self::assertEquals(
            [],
            $this->subject->getImages(),
        );
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function getFacebookInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getFacebook(),
        );
    }

    #[Test]
    public function setFacebookSetsFacebook(): void
    {
        $this->subject->setFacebook('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getFacebook(),
        );
    }

    #[Test]
    public function getTwitterInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getTwitter(),
        );
    }

    #[Test]
    public function setTwitterSetsTwitter(): void
    {
        $this->subject->setTwitter('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getTwitter(),
        );
    }

    #[Test]
    public function getInstagramInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getInstagram(),
        );
    }

    #[Test]
    public function setInstagramSetsInstagram(): void
    {
        $this->subject->setInstagram('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getInstagram(),
        );
    }

    #[Test]
    public function getTagsInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getTags(),
        );
    }

    #[Test]
    public function setTagsSetsTags(): void
    {
        $this->subject->setTags('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getTags(),
        );
    }

    #[Test]
    public function getDistrictInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getDistrict());
    }

    #[Test]
    public function setDistrictSetsDistrict(): void
    {
        $instance = new District();
        $this->subject->setDistrict($instance);

        self::assertSame(
            $instance,
            $this->subject->getDistrict(),
        );
    }

    #[Test]
    public function getAddressesInitiallyReturnsArray(): void
    {
        self::assertEquals(
            [],
            $this->subject->getAddresses(),
        );
    }

    #[Test]
    public function getOriginalAddressesInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getOriginalAddresses(),
        );
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function getCategoriesInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getCategories(),
        );
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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
