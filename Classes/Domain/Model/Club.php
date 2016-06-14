<?php

namespace JWeiland\Clubdirectory\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <projects@jweiland.net>, jweiland.net
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
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
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Sets the hidden.
     *
     * @param bool $hidden
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title.
     *
     * @param string $title
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
    public function getSortTitle()
    {
        return $this->sortTitle;
    }

    /**
     * Sets the sortTitle.
     *
     * @param string $sortTitle
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
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Sets the activity.
     *
     * @param string $activity
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
    public function getContactPerson()
    {
        return $this->contactPerson;
    }

    /**
     * Sets the contactPerson.
     *
     * @param string $contactPerson
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
    public function getContactTimes()
    {
        return $this->contactTimes;
    }

    /**
     * Sets the contactTimes.
     *
     * @param string $contactTimes
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
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the email.
     *
     * @param string $email
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
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Sets the website.
     *
     * @param string $website
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
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Sets the members.
     *
     * @param string $members
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
    public function getClubHome()
    {
        return $this->clubHome;
    }

    /**
     * Sets the clubHome.
     *
     * @param string $clubHome
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the description.
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = (string) $description;
    }

    /**
     * Adds a feUser.
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $feUser
     */
    public function addFeUser(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser $feUser)
    {
        $this->feUsers->attach($feUser);
    }

    /**
     * Removes a feUser.
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $feUser
     */
    public function removeFeUser(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser $feUser)
    {
        $this->feUsers->detach($feUser);
    }

    /**
     * Returns the feUsers.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage $feUsers
     */
    public function getFeUsers()
    {
        return $this->feUsers;
    }

    /**
     * Sets the feUsers.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $feUsers
     */
    public function setFeUser(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $feUsers)
    {
        $this->feUsers = $feUsers;
    }

    /**
     * Returns the logo.
     *
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference $logo
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Sets the logo.
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $logo
     */
    public function setLogo(\TYPO3\CMS\Extbase\Domain\Model\FileReference $logo = null)
    {
        $this->logo = $logo;
    }

    /**
     * Returns the images.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage $images
     */
    public function getImages()
    {
        $references = array();
        foreach ($this->images as $image) {
            $references[] = $image;
        }

        return $references;
    }

    /**
     * Sets the images.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $images
     */
    public function setImages(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $images)
    {
        $this->images = $images;
    }

    /**
     * Returns the facebook.
     *
     * @return string $facebook
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Sets the facebook.
     *
     * @param string $facebook
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
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * Sets the twitter.
     *
     * @param string $twitter
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
    public function getGoogle()
    {
        return $this->google;
    }

    /**
     * Sets the google.
     *
     * @param string $google
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
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Sets the tags.
     *
     * @param string $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * Returns the district.
     *
     * @return \JWeiland\Clubdirectory\Domain\Model\District $district
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * Sets the district.
     *
     * @param \JWeiland\Clubdirectory\Domain\Model\District $district
     */
    public function setDistrict(\JWeiland\Clubdirectory\Domain\Model\District $district)
    {
        $this->district = $district;
    }

    /**
     * Returns the addresses.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage $addresses
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * Sets the addresses.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $addresses
     */
    public function setAddresses(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $addresses)
    {
        $this->addresses = $addresses;
    }

    /**
     * add a new address.
     *
     * @param \JWeiland\Clubdirectory\Domain\Model\Address $address
     */
    public function addAddress(\JWeiland\Clubdirectory\Domain\Model\Address $address)
    {
        $this->addresses->attach($address);
    }

    /**
     * remove an address.
     *
     * @param \JWeiland\Clubdirectory\Domain\Model\Address $address
     */
    public function removeAddress(\JWeiland\Clubdirectory\Domain\Model\Address $address)
    {
        $this->addresses->detach($address);
    }

    /**
     * Returns the categories.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Sets the categories.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories
     */
    public function setCategories(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories)
    {
        $this->categories = $categories;
    }
}
