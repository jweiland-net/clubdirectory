<?php
declare(strict_types=1);
namespace JWeiland\Clubdirectory\Domain\Model;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class Club
 *
 * @package JWeiland\Clubdirectory\Domain\Model
 */
class Club extends AbstractEntity
{
    /**
     * Hidden.
     *
     * @var bool
     */
    protected $hidden = false;

    /**
     * Title.
     *
     * @var string
     * @validate NotEmpty
     */
    protected $title = '';

    /**
     * Title.
     *
     * @var string
     * @validate NotEmpty
     */
    protected $sortTitle = '';

    /**
     * Activity.
     *
     * @var string
     */
    protected $activity = '';

    /**
     * Contact person.
     *
     * @var string
     */
    protected $contactPerson = '';

    /**
     * Contact Times.
     *
     * @var string
     */
    protected $contactTimes = '';

    /**
     * Email.
     *
     * @var string
     * @validate EmailAddress
     */
    protected $email = '';

    /**
     * Website.
     *
     * @var string
     */
    protected $website = '';

    /**
     * Members.
     *
     * @var string
     */
    protected $members = '';

    /**
     * Club home.
     *
     * @var string
     */
    protected $clubHome = '';

    /**
     * Description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * FeUsers.
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FrontendUser>
     */
    protected $feUsers;

    /**
     * Logo.
     *
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    protected $logo;

    /**
     * Images.
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $images;

    /**
     * Facebook.
     *
     * @var string
     */
    protected $facebook = '';

    /**
     * Twitter.
     *
     * @var string
     */
    protected $twitter = '';

    /**
     * Google+.
     *
     * @var string
     */
    protected $google = '';

    /**
     * tags.
     *
     * @var string
     */
    protected $tags = '';

    /**
     * District.
     *
     * @var \JWeiland\Clubdirectory\Domain\Model\District
     */
    protected $district;

    /**
     * addresses.
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Clubdirectory\Domain\Model\Address>
     */
    protected $addresses;

    /**
     * categories.
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     * @lazy
     */
    protected $categories;

    /**
     * Constructor of this object.
     */
    public function __construct()
    {
        $this->feUsers = new ObjectStorage();
        $this->images = new ObjectStorage();
        $this->addresses = new ObjectStorage();
        $this->categories = new ObjectStorage();
    }

    /**
     * Returns the hidden.
     *
     * @return bool $hidden
     */
    public function getHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * Sets the hidden.
     *
     * @param bool $hidden
     * @return void
     */
    public function setHidden($hidden)
    {
        $this->hidden = (boolean) $hidden;
    }

    /**
     * Returns the title.
     *
     * @return string $title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets the title.
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = (string) $title;
    }

    /**
     * Returns the sortTitle.
     *
     * @return string $sortTitle
     */
    public function getSortTitle(): string
    {
        return $this->sortTitle;
    }

    /**
     * Sets the sortTitle.
     *
     * @param string $sortTitle
     * @return void
     */
    public function setSortTitle($sortTitle)
    {
        $this->sortTitle = (string) $sortTitle;
    }

    /**
     * Returns the activity.
     *
     * @return string $activity
     */
    public function getActivity(): string
    {
        return $this->activity;
    }

    /**
     * Sets the activity.
     *
     * @param string $activity
     * @return void
     */
    public function setActivity($activity)
    {
        $this->activity = (string) $activity;
    }

    /**
     * Returns the contactPerson.
     *
     * @return string $contactPerson
     */
    public function getContactPerson(): string
    {
        return $this->contactPerson;
    }

    /**
     * Sets the contactPerson.
     *
     * @param string $contactPerson
     * @return void
     */
    public function setContactPerson($contactPerson)
    {
        $this->contactPerson = (string) $contactPerson;
    }

    /**
     * Returns the contactTimes.
     *
     * @return string $contactTimes
     */
    public function getContactTimes(): string
    {
        return $this->contactTimes;
    }

    /**
     * Sets the contactTimes.
     *
     * @param string $contactTimes
     * @return void
     */
    public function setContactTimes($contactTimes)
    {
        $this->contactTimes = (string) $contactTimes;
    }

    /**
     * Returns the email.
     *
     * @return string $email
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Sets the email.
     *
     * @param string $email
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = (string) $email;
    }

    /**
     * Returns the website.
     *
     * @return string $website
     */
    public function getWebsite(): string
    {
        return $this->website;
    }

    /**
     * Sets the website.
     *
     * @param string $website
     * @return void
     */
    public function setWebsite($website)
    {
        $this->website = (string) $website;
    }

    /**
     * Returns the members.
     *
     * @return string $members
     */
    public function getMembers(): string
    {
        return $this->members;
    }

    /**
     * Sets the members.
     *
     * @param string $members
     * @return void
     */
    public function setMembers($members)
    {
        $this->members = (string) $members;
    }

    /**
     * Returns the clubHome.
     *
     * @return string $clubHome
     */
    public function getClubHome(): string
    {
        return $this->clubHome;
    }

    /**
     * Sets the clubHome.
     *
     * @param string $clubHome
     * @return void
     */
    public function setClubHome($clubHome)
    {
        $this->clubHome = (string) $clubHome;
    }

    /**
     * Returns the description.
     *
     * @return string $description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Sets the description.
     *
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = (string) $description;
    }

    /**
     * Adds a feUser.
     *
     * @param FrontendUser $feUser
     * @return void
     */
    public function addFeUser(FrontendUser $feUser)
    {
        $this->feUsers->attach($feUser);
    }

    /**
     * Removes a feUser.
     *
     * @param FrontendUser $feUser
     * @return void
     */
    public function removeFeUser(FrontendUser $feUser)
    {
        $this->feUsers->detach($feUser);
    }

    /**
     * Returns the feUsers.
     *
     * @return ObjectStorage $feUsers
     */
    public function getFeUsers(): ObjectStorage
    {
        return $this->feUsers;
    }

    /**
     * Sets the feUsers.
     *
     * @param ObjectStorage $feUsers
     * @return void
     */
    public function setFeUser(ObjectStorage $feUsers)
    {
        $this->feUsers = $feUsers;
    }

    /**
     * Returns the logo.
     *
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference|null $logo
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Sets the logo.
     *
     * @param FileReference $logo
     * @return void
     */
    public function setLogo(FileReference $logo = null)
    {
        $this->logo = $logo;
    }

    /**
     * Returns the images.
     *
     * @return ObjectStorage|array $images
     */
    public function getImages()
    {
        $references = [];
        foreach ($this->images as $image) {
            $references[] = $image;
        }

        return $references;
    }

    /**
     * Sets the images.
     *
     * @param ObjectStorage $images
     * @return void
     */
    public function setImages(ObjectStorage $images)
    {
        $this->images = $images;
    }

    /**
     * Returns the facebook.
     *
     * @return string $facebook
     */
    public function getFacebook(): string
    {
        return $this->facebook;
    }

    /**
     * Sets the facebook.
     *
     * @param string $facebook
     * @return void
     */
    public function setFacebook($facebook)
    {
        $this->facebook = (string) $facebook;
    }

    /**
     * Returns the twitter.
     *
     * @return string $twitter
     */
    public function getTwitter(): string
    {
        return $this->twitter;
    }

    /**
     * Sets the twitter.
     *
     * @param string $twitter
     * @return void
     */
    public function setTwitter($twitter)
    {
        $this->twitter = (string) $twitter;
    }

    /**
     * Returns the google.
     *
     * @return string $google
     */
    public function getGoogle(): string
    {
        return $this->google;
    }

    /**
     * Sets the google.
     *
     * @param string $google
     * @return void
     */
    public function setGoogle($google)
    {
        $this->google = (string) $google;
    }

    /**
     * Returns the tags.
     *
     * @return string $tags
     */
    public function getTags(): string
    {
        return $this->tags;
    }

    /**
     * Sets the tags.
     *
     * @param string $tags
     * @return void
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * Returns the district.
     *
     * @return District $district
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * Sets the district.
     *
     * @param District $district
     * @return void
     */
    public function setDistrict(District $district)
    {
        $this->district = $district;
    }

    /**
     * Returns the addresses.
     *
     * @return ObjectStorage $addresses
     */
    public function getAddresses(): ObjectStorage
    {
        return $this->addresses;
    }

    /**
     * Sets the addresses.
     *
     * @param ObjectStorage $addresses
     * @return void
     */
    public function setAddresses(ObjectStorage $addresses)
    {
        $this->addresses = $addresses;
    }

    /**
     * add a new address.
     *
     * @param Address $address
     * @return void
     */
    public function addAddress(Address $address)
    {
        $this->addresses->attach($address);
    }

    /**
     * remove an address.
     *
     * @param Address $address
     * @return void
     */
    public function removeAddress(Address $address)
    {
        $this->addresses->detach($address);
    }

    /**
     * Returns the categories.
     *
     * @return ObjectStorage $categories
     */
    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    /**
     * Sets the categories.
     *
     * @param ObjectStorage $categories
     * @return void
     */
    public function setCategories(ObjectStorage $categories)
    {
        $this->categories = $categories;
    }
}
