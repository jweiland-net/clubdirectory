<?php

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\Domain\Model;

use JWeiland\Clubdirectory\Domain\Model\Address;
use JWeiland\Clubdirectory\Domain\Model\Category;
use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Domain\Model\District;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Test case for model Club
 */
class ClubTest extends UnitTestCase
{
    /**
     * @var Club
     */
    protected $subject;

    public function setUp()
    {
        $this->subject = new Club();
    }

    public function tearDown()
    {
        unset(
            $this->subject
        );
    }

    /**
     * @test
     */
    public function getHiddenInitiallyReturnsFalse()
    {
        self::assertFalse(
            $this->subject->getHidden()
        );
    }

    /**
     * @test
     */
    public function setHiddenSetsHidden()
    {
        $this->subject->setHidden(true);
        self::assertTrue(
            $this->subject->getHidden()
        );
    }

    /**
     * @test
     */
    public function setHiddenWithStringReturnsTrue()
    {
        $this->subject->setHidden('foo bar');
        self::assertTrue($this->subject->getHidden());
    }

    /**
     * @test
     */
    public function setHiddenWithZeroReturnsFalse()
    {
        $this->subject->setHidden(0);
        self::assertFalse($this->subject->getHidden());
    }

    /**
     * @test
     */
    public function getTitleInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleSetsTitle()
    {
        $this->subject->setTitle('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleWithIntegerResultsInString()
    {
        $this->subject->setTitle(123);
        self::assertSame('123', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function setTitleWithBooleanResultsInString()
    {
        $this->subject->setTitle(true);
        self::assertSame('1', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function getSortTitleInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getSortTitle()
        );
    }

    /**
     * @test
     */
    public function setSortTitleSetsSortTitle()
    {
        $this->subject->setSortTitle('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getSortTitle()
        );
    }

    /**
     * @test
     */
    public function setSortTitleWithIntegerResultsInString()
    {
        $this->subject->setSortTitle(123);
        self::assertSame('123', $this->subject->getSortTitle());
    }

    /**
     * @test
     */
    public function setSortTitleWithBooleanResultsInString()
    {
        $this->subject->setSortTitle(true);
        self::assertSame('1', $this->subject->getSortTitle());
    }

    /**
     * @test
     */
    public function getActivityInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getActivity()
        );
    }

    /**
     * @test
     */
    public function setActivitySetsActivity()
    {
        $this->subject->setActivity('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getActivity()
        );
    }

    /**
     * @test
     */
    public function setActivityWithIntegerResultsInString()
    {
        $this->subject->setActivity(123);
        self::assertSame('123', $this->subject->getActivity());
    }

    /**
     * @test
     */
    public function setActivityWithBooleanResultsInString()
    {
        $this->subject->setActivity(true);
        self::assertSame('1', $this->subject->getActivity());
    }

    /**
     * @test
     */
    public function getContactPersonInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getContactPerson()
        );
    }

    /**
     * @test
     */
    public function setContactPersonSetsContactPerson()
    {
        $this->subject->setContactPerson('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getContactPerson()
        );
    }

    /**
     * @test
     */
    public function setContactPersonWithIntegerResultsInString()
    {
        $this->subject->setContactPerson(123);
        self::assertSame('123', $this->subject->getContactPerson());
    }

    /**
     * @test
     */
    public function setContactPersonWithBooleanResultsInString()
    {
        $this->subject->setContactPerson(true);
        self::assertSame('1', $this->subject->getContactPerson());
    }

    /**
     * @test
     */
    public function getContactTimesInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getContactTimes()
        );
    }

    /**
     * @test
     */
    public function setContactTimesSetsContactTimes()
    {
        $this->subject->setContactTimes('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getContactTimes()
        );
    }

    /**
     * @test
     */
    public function setContactTimesWithIntegerResultsInString()
    {
        $this->subject->setContactTimes(123);
        self::assertSame('123', $this->subject->getContactTimes());
    }

    /**
     * @test
     */
    public function setContactTimesWithBooleanResultsInString()
    {
        $this->subject->setContactTimes(true);
        self::assertSame('1', $this->subject->getContactTimes());
    }

    /**
     * @test
     */
    public function getEmailInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getEmail()
        );
    }

    /**
     * @test
     */
    public function setEmailSetsEmail()
    {
        $this->subject->setEmail('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getEmail()
        );
    }

    /**
     * @test
     */
    public function setEmailWithIntegerResultsInString()
    {
        $this->subject->setEmail(123);
        self::assertSame('123', $this->subject->getEmail());
    }

    /**
     * @test
     */
    public function setEmailWithBooleanResultsInString()
    {
        $this->subject->setEmail(true);
        self::assertSame('1', $this->subject->getEmail());
    }

    /**
     * @test
     */
    public function getWebsiteInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getWebsite()
        );
    }

    /**
     * @test
     */
    public function setWebsiteSetsWebsite()
    {
        $this->subject->setWebsite('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getWebsite()
        );
    }

    /**
     * @test
     */
    public function setWebsiteWithIntegerResultsInString()
    {
        $this->subject->setWebsite(123);
        self::assertSame('123', $this->subject->getWebsite());
    }

    /**
     * @test
     */
    public function setWebsiteWithBooleanResultsInString()
    {
        $this->subject->setWebsite(true);
        self::assertSame('1', $this->subject->getWebsite());
    }

    /**
     * @test
     */
    public function getMembersInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getMembers()
        );
    }

    /**
     * @test
     */
    public function setMembersSetsMembers()
    {
        $this->subject->setMembers('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getMembers()
        );
    }

    /**
     * @test
     */
    public function setMembersWithIntegerResultsInString()
    {
        $this->subject->setMembers(123);
        self::assertSame('123', $this->subject->getMembers());
    }

    /**
     * @test
     */
    public function setMembersWithBooleanResultsInString()
    {
        $this->subject->setMembers(true);
        self::assertSame('1', $this->subject->getMembers());
    }

    /**
     * @test
     */
    public function getClubHomeInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getClubHome()
        );
    }

    /**
     * @test
     */
    public function setClubHomeSetsClubHome()
    {
        $this->subject->setClubHome('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getClubHome()
        );
    }

    /**
     * @test
     */
    public function setClubHomeWithIntegerResultsInString()
    {
        $this->subject->setClubHome(123);
        self::assertSame('123', $this->subject->getClubHome());
    }

    /**
     * @test
     */
    public function setClubHomeWithBooleanResultsInString()
    {
        $this->subject->setClubHome(true);
        self::assertSame('1', $this->subject->getClubHome());
    }

    /**
     * @test
     */
    public function getDescriptionInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function setDescriptionSetsDescription()
    {
        $this->subject->setDescription('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function setDescriptionWithIntegerResultsInString()
    {
        $this->subject->setDescription(123);
        self::assertSame('123', $this->subject->getDescription());
    }

    /**
     * @test
     */
    public function setDescriptionWithBooleanResultsInString()
    {
        $this->subject->setDescription(true);
        self::assertSame('1', $this->subject->getDescription());
    }

    /**
     * @test
     */
    public function getFeUsersInitiallyReturnsObjectStorage()
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getFeUsers()
        );
    }

    /**
     * @test
     */
    public function setFeUsersSetsFeUsers()
    {
        $object = new FrontendUser();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setFeUsers($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getFeUsers()
        );
    }

    /**
     * @test
     */
    public function addFrontendUserAddsOneFrontendUser()
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setFeUsers($objectStorage);

        $object = new FrontendUser();
        $this->subject->addFeUser($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getFeUsers()
        );
    }

    /**
     * @test
     */
    public function removeFrontendUserRemovesOneFrontendUser()
    {
        $object = new FrontendUser();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setFeUsers($objectStorage);

        $this->subject->removeFeUser($object);
        $objectStorage->detach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getFeUsers()
        );
    }

    /**
     * @test
     */
    public function getLogoInitiallyReturnsEmptyArray()
    {
        self::assertSame(
            [],
            $this->subject->getLogo()
        );
    }

    /**
     * @test
     */
    public function getFirstLogoInitiallyReturnsNull()
    {
        self::assertNull(
            $this->subject->getFirstLogo()
        );
    }

    /**
     * @test
     */
    public function getOriginalLogoInitiallyReturnsObjectStorage()
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getOriginalLogo()
        );
    }

    /**
     * @test
     */
    public function setLogoSetsLogo()
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setLogo($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalLogo()
        );
    }

    /**
     * @test
     */
    public function addLogoAddsOneLogo()
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setLogo($objectStorage);

        $object = new FileReference();
        $this->subject->addLogo($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalLogo()
        );
    }

    /**
     * @test
     */
    public function removeLogoRemovesOneLogo()
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setLogo($objectStorage);

        $this->subject->removeLogo($object);
        $objectStorage->detach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalLogo()
        );
    }

    /**
     * @test
     */
    public function getImagesInitiallyReturnsArray()
    {
        self::assertEquals(
            [],
            $this->subject->getImages()
        );
    }

    /**
     * @test
     */
    public function setImagesSetsImages()
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setImages($objectStorage);

        self::assertSame(
            [$object],
            $this->subject->getImages()
        );
    }

    /**
     * @test
     */
    public function addImageAddsOneImage()
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setImages($objectStorage);

        $object = new FileReference();
        $this->subject->addImage($object);

        $objectStorage->attach($object);

        self::assertSame(
            [$object],
            $this->subject->getImages()
        );
    }

    /**
     * @test
     */
    public function removeImageRemovesOneImage()
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setImages($objectStorage);

        $this->subject->removeImage($object);
        $objectStorage->detach($object);

        self::assertSame(
            [],
            $this->subject->getImages()
        );
    }

    /**
     * @test
     */
    public function getFacebookInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getFacebook()
        );
    }

    /**
     * @test
     */
    public function setFacebookSetsFacebook()
    {
        $this->subject->setFacebook('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getFacebook()
        );
    }

    /**
     * @test
     */
    public function setFacebookWithIntegerResultsInString()
    {
        $this->subject->setFacebook(123);
        self::assertSame('123', $this->subject->getFacebook());
    }

    /**
     * @test
     */
    public function setFacebookWithBooleanResultsInString()
    {
        $this->subject->setFacebook(true);
        self::assertSame('1', $this->subject->getFacebook());
    }

    /**
     * @test
     */
    public function getTwitterInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getTwitter()
        );
    }

    /**
     * @test
     */
    public function setTwitterSetsTwitter()
    {
        $this->subject->setTwitter('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getTwitter()
        );
    }

    /**
     * @test
     */
    public function setTwitterWithIntegerResultsInString()
    {
        $this->subject->setTwitter(123);
        self::assertSame('123', $this->subject->getTwitter());
    }

    /**
     * @test
     */
    public function setTwitterWithBooleanResultsInString()
    {
        $this->subject->setTwitter(true);
        self::assertSame('1', $this->subject->getTwitter());
    }

    /**
     * @test
     */
    public function getGoogleInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getGoogle()
        );
    }

    /**
     * @test
     */
    public function setGoogleSetsGoogle()
    {
        $this->subject->setGoogle('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getGoogle()
        );
    }

    /**
     * @test
     */
    public function setGoogleWithIntegerResultsInString()
    {
        $this->subject->setGoogle(123);
        self::assertSame('123', $this->subject->getGoogle());
    }

    /**
     * @test
     */
    public function setGoogleWithBooleanResultsInString()
    {
        $this->subject->setGoogle(true);
        self::assertSame('1', $this->subject->getGoogle());
    }

    /**
     * @test
     */
    public function getTagsInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getTags()
        );
    }

    /**
     * @test
     */
    public function setTagsSetsTags()
    {
        $this->subject->setTags('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getTags()
        );
    }

    /**
     * @test
     */
    public function setTagsWithIntegerResultsInString()
    {
        $this->subject->setTags(123);
        self::assertSame('123', $this->subject->getTags());
    }

    /**
     * @test
     */
    public function setTagsWithBooleanResultsInString()
    {
        $this->subject->setTags(true);
        self::assertSame('1', $this->subject->getTags());
    }

    /**
     * @test
     */
    public function getDistrictInitiallyReturnsNull()
    {
        self::assertNull($this->subject->getDistrict());
    }

    /**
     * @test
     */
    public function setDistrictSetsDistrict()
    {
        $instance = new District();
        $this->subject->setDistrict($instance);

        self::assertSame(
            $instance,
            $this->subject->getDistrict()
        );
    }

    /**
     * @test
     */
    public function getAddressesInitiallyReturnsArray()
    {
        self::assertEquals(
            [],
            $this->subject->getAddresses()
        );
    }

    /**
     * @test
     */
    public function getOriginalAddressesInitiallyReturnsObjectStorage()
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getOriginalAddresses()
        );
    }

    /**
     * @test
     */
    public function setAddressesSetsAddresses()
    {
        $object = new Address();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setAddresses($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalAddresses()
        );
    }

    /**
     * @test
     */
    public function addAddressAddsOneAddress()
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setAddresses($objectStorage);

        $object = new Address();
        $this->subject->addAddress($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalAddresses()
        );
    }

    /**
     * @test
     */
    public function removeAddressRemovesOneAddress()
    {
        $object = new Address();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setAddresses($objectStorage);

        $this->subject->removeAddress($object);
        $objectStorage->detach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalAddresses()
        );
    }

    /**
     * @test
     */
    public function getCategoriesInitiallyReturnsObjectStorage()
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getCategories()
        );
    }

    /**
     * @test
     */
    public function setCategoriesSetsCategories()
    {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setCategories($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getCategories()
        );
    }

    /**
     * @test
     */
    public function addCategoryAddsOneCategory()
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setCategories($objectStorage);

        $object = new Category();
        $this->subject->addCategory($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getCategories()
        );
    }

    /**
     * @test
     */
    public function removeCategoryRemovesOneCategory()
    {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setCategories($objectStorage);

        $this->subject->removeCategory($object);
        $objectStorage->detach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getCategories()
        );
    }
}
