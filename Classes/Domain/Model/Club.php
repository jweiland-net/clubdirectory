<?php
declare(strict_types = 1);
namespace JWeiland\Clubdirectory\Domain\Model;

/*
 * This file is part of the clubdirectory project.
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

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Domain model which represents a Club
 */
class Club extends AbstractEntity
{
    /**
     * @var bool
     */
    protected $hidden = false;

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $title = '';

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $sortTitle = '';

    /**
     * @var string
     */
    protected $activity = '';

    /**
     * @var string
     */
    protected $contactPerson = '';

    /**
     * @var string
     */
    protected $contactTimes = '';

    /**
     * @var string
     * @validate EmailAddress
     */
    protected $email = '';

    /**
     * @var string
     */
    protected $website = '';

    /**
     * @var string
     */
    protected $members = '';

    /**
     * @var string
     */
    protected $clubHome = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FrontendUser>
     */
    protected $feUsers;

    /**
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    protected $logo;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $images;

    /**
     * @var string
     */
    protected $facebook = '';

    /**
     * @var string
     */
    protected $twitter = '';

    /**
     * @var string
     */
    protected $google = '';

    /**
     * @var string
     */
    protected $tags = '';

    /**
     * @var \JWeiland\Clubdirectory\Domain\Model\District
     */
    protected $district;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Clubdirectory\Domain\Model\Address>
     * @cascade remove
     */
    protected $addresses;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Clubdirectory\Domain\Model\Category>
     * @lazy
     */
    protected $categories;

    public function __construct()
    {
        $this->feUsers = new ObjectStorage();
        $this->images = new ObjectStorage();
        $this->addresses = new ObjectStorage();
        $this->categories = new ObjectStorage();
    }

    /**
     * @return bool
     */
    public function getHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden(bool $hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getSortTitle(): string
    {
        return $this->sortTitle;
    }

    /**
     * @param string $sortTitle
     */
    public function setSortTitle(string $sortTitle)
    {
        $this->sortTitle = $sortTitle;
    }

    /**
     * @return string
     */
    public function getActivity(): string
    {
        return $this->activity;
    }

    /**
     * @param string $activity
     */
    public function setActivity(string $activity)
    {
        $this->activity = $activity;
    }

    /**
     * @return string
     */
    public function getContactPerson(): string
    {
        return $this->contactPerson;
    }

    /**
     * @param string $contactPerson
     */
    public function setContactPerson(string $contactPerson)
    {
        $this->contactPerson = $contactPerson;
    }

    /**
     * @return string
     */
    public function getContactTimes(): string
    {
        return $this->contactTimes;
    }

    /**
     * @param string $contactTimes
     */
    public function setContactTimes(string $contactTimes)
    {
        $this->contactTimes = $contactTimes;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getWebsite(): string
    {
        return $this->website;
    }

    /**
     * @param string $website
     */
    public function setWebsite(string $website)
    {
        $this->website = $website;
    }

    /**
     * @return string
     */
    public function getMembers(): string
    {
        return $this->members;
    }

    /**
     * @param string $members
     */
    public function setMembers(string $members)
    {
        $this->members = $members;
    }

    /**
     * @return string
     */
    public function getClubHome(): string
    {
        return $this->clubHome;
    }

    /**
     * @param string $clubHome
     */
    public function setClubHome(string $clubHome)
    {
        $this->clubHome = $clubHome;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @param FrontendUser $feUser
     */
    public function addFeUser(FrontendUser $feUser)
    {
        $this->feUsers->attach($feUser);
    }

    /**
     * @param FrontendUser $feUser
     */
    public function removeFeUser(FrontendUser $feUser)
    {
        $this->feUsers->detach($feUser);
    }

    /**
     * @return ObjectStorage|\TYPO3\CMS\Extbase\Domain\Model\FrontendUser
     */
    public function getFeUsers(): ObjectStorage
    {
        return $this->feUsers;
    }

    /**
     * @param ObjectStorage $feUsers
     */
    public function setFeUsers(ObjectStorage $feUsers)
    {
        $this->feUsers = $feUsers;
    }

    public function getCurrentUserCanEditClub(): bool
    {
        $currentUserCanEditThisClub = false;
        if (
            is_array($GLOBALS['TSFE']->fe_user->user)
            && $this->getFeUsers()->count()
        ) {
            foreach ($this->getFeUsers() as $feUser) {
                if ($feUser->getUid() === (int)$GLOBALS['TSFE']->fe_user->user['uid']) {
                    $currentUserCanEditThisClub = true;
                    break;
                }
            }
        }
        return $currentUserCanEditThisClub;
    }

    /**
     * @return FileReference|null
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param FileReference $logo
     */
    public function setLogo(FileReference $logo = null)
    {
        $this->logo = $logo;
    }

    /**
     * @return array|\TYPO3\CMS\Core\Resource\FileReference[]
     */
    public function getImages(): array
    {
        $references = [];
        foreach ($this->images as $image) {
            $references[] = $image;
        }

        return $references;
    }

    /**
     * @return ObjectStorage|\TYPO3\CMS\Core\Resource\FileReference[]
     */
    public function getOriginalImages(): ObjectStorage
    {
        return $this->images;
    }

    /**
     * @param ObjectStorage $images
     */
    public function setImages(ObjectStorage $images)
    {
        $this->images = $images;
    }

    /**
     * @param FileReference $image
     */
    public function addImage(FileReference $image)
    {
        $this->images->attach($image);
    }

    /**
     * @param FileReference $image
     */
    public function removeImage(FileReference $image)
    {
        $this->images->detach($image);
    }

    /**
     * @return string
     */
    public function getFacebook(): string
    {
        return $this->facebook;
    }

    /**
     * @param string $facebook
     */
    public function setFacebook(string $facebook)
    {
        $this->facebook = $facebook;
    }

    /**
     * @return string
     */
    public function getTwitter(): string
    {
        return $this->twitter;
    }

    /**
     * @param string $twitter
     */
    public function setTwitter(string $twitter)
    {
        $this->twitter = $twitter;
    }

    /**
     * @return string
     */
    public function getGoogle(): string
    {
        return $this->google;
    }

    /**
     * @param string $google
     */
    public function setGoogle(string $google)
    {
        $this->google = $google;
    }

    /**
     * @return string
     */
    public function getTags(): string
    {
        return $this->tags;
    }

    /**
     * @param string $tags
     */
    public function setTags(string $tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return District
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * @param District $district
     */
    public function setDistrict(District $district)
    {
        $this->district = $district;
    }

    /**
     * @return array|Address[]
     */
    public function getAddresses(): array
    {
        $addresses = [];
        foreach ($this->addresses as $address) {
            $addresses[] = $address;
        }
        return $addresses;
    }

    /**
     * @return ObjectStorage|Address[]
     */
    public function getOriginalAddresses(): ObjectStorage
    {
        return $this->addresses;
    }

    /**
     * @param ObjectStorage $addresses
     */
    public function setAddresses(ObjectStorage $addresses)
    {
        $this->addresses = $addresses;
    }

    /**
     * @param Address $address
     */
    public function addAddress(Address $address)
    {
        $this->addresses->attach($address);
    }

    /**
     * @param Address $address
     */
    public function removeAddress(Address $address)
    {
        $this->addresses->detach($address);
    }

    /**
     * @return ObjectStorage
     */
    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    /**
     * @param ObjectStorage $categories
     */
    public function setCategories(ObjectStorage $categories)
    {
        $this->categories = $categories;
    }

    /**
     * @param Category $category
     */
    public function addCategory(Category $category)
    {
        $this->categories->attach($category);
    }

    /**
     * @param Category $category
     */
    public function removeCategory(Category $category)
    {
        $this->categories->detach($category);
    }
}
