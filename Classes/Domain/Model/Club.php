<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Domain\Model;

use TYPO3\CMS\Extbase\Annotation as Extbase;
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
     * @Extbase\Validate("NotEmpty")
     */
    protected $title = '';

    /**
     * @var string
     * @Extbase\Validate("NotEmpty")
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
     * @Extbase\Validate("EmailAddress")
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
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
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
     * @Extbase\ORM\Cascade("remove")
     */
    protected $addresses;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Clubdirectory\Domain\Model\Category>
     * @Extbase\ORM\Lazy
     */
    protected $categories;

    public function __construct()
    {
        $this->feUsers = new ObjectStorage();
        $this->logo = new ObjectStorage();
        $this->images = new ObjectStorage();
        $this->addresses = new ObjectStorage();
        $this->categories = new ObjectStorage();
    }

    public function getHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSortTitle(): string
    {
        return $this->sortTitle;
    }

    public function setSortTitle(string $sortTitle): void
    {
        $this->sortTitle = $sortTitle;
    }

    public function getActivity(): string
    {
        return $this->activity;
    }

    public function setActivity(string $activity): void
    {
        $this->activity = $activity;
    }

    public function getContactPerson(): string
    {
        return $this->contactPerson;
    }

    public function setContactPerson(string $contactPerson): void
    {
        $this->contactPerson = $contactPerson;
    }

    public function getContactTimes(): string
    {
        return $this->contactTimes;
    }

    public function setContactTimes(string $contactTimes): void
    {
        $this->contactTimes = $contactTimes;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getWebsite(): string
    {
        return $this->website;
    }

    public function setWebsite(string $website): void
    {
        $this->website = $website;
    }

    public function getMembers(): string
    {
        return $this->members;
    }

    public function setMembers(string $members): void
    {
        $this->members = $members;
    }

    public function getClubHome(): string
    {
        return $this->clubHome;
    }

    public function setClubHome(string $clubHome): void
    {
        $this->clubHome = $clubHome;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getFeUsers(): ObjectStorage
    {
        return $this->feUsers;
    }

    public function setFeUsers(ObjectStorage $feUsers): void
    {
        $this->feUsers = $feUsers;
    }

    public function addFeUser(FrontendUser $feUser): void
    {
        $this->feUsers->attach($feUser);
    }

    public function removeFeUser(FrontendUser $feUser): void
    {
        $this->feUsers->detach($feUser);
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
     * @return FileReference[]
     */
    public function getLogo(): array
    {
        return $this->logo->toArray();
    }

    public function getFirstLogo(): ?FileReference
    {
        $this->logo->rewind();
        return $this->logo->current();
    }

    public function getOriginalLogo(): ObjectStorage
    {
        return $this->logo;
    }

    public function setLogo(ObjectStorage $logo): void
    {
        $this->logo = $logo;
    }

    public function addLogo(FileReference $logo): void
    {
        $this->logo->attach($logo);
    }

    public function removeLogo(FileReference $logo): void
    {
        $this->logo->detach($logo);
    }

    /**
     * @return array|FileReference[]
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

    public function setImages(ObjectStorage $images): void
    {
        $this->images = $images;
    }

    public function addImage(FileReference $image): void
    {
        $this->images->attach($image);
    }

    public function removeImage(FileReference $image): void
    {
        $this->images->detach($image);
    }

    public function getFacebook(): string
    {
        return $this->facebook;
    }

    public function setFacebook(string $facebook): void
    {
        $this->facebook = $facebook;
    }

    public function getTwitter(): string
    {
        return $this->twitter;
    }

    public function setTwitter(string $twitter): void
    {
        $this->twitter = $twitter;
    }

    public function getGoogle(): string
    {
        return $this->google;
    }

    public function setGoogle(string $google): void
    {
        $this->google = $google;
    }

    public function getTags(): string
    {
        return $this->tags;
    }

    public function setTags(string $tags): void
    {
        $this->tags = $tags;
    }

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(District $district): void
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

    public function setAddresses(ObjectStorage $addresses): void
    {
        $this->addresses = $addresses;
    }

    public function addAddress(Address $address): void
    {
        $this->addresses->attach($address);
    }

    public function removeAddress(Address $address): void
    {
        $this->addresses->detach($address);
    }

    /**
     * @return ObjectStorage|Category[]
     */
    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    public function getFirstCategory(): ?Category
    {
        $this->categories->rewind();
        return $this->categories->current();
    }

    public function setCategories(ObjectStorage $categories): void
    {
        $this->categories = $categories;
    }

    public function addCategory(Category $category): void
    {
        $this->categories->attach($category);
    }

    public function removeCategory(Category $category): void
    {
        $this->categories->detach($category);
    }
}
