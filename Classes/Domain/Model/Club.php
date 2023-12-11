<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Domain\Model;

use JWeiland\Clubdirectory\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Domain model which represents a Club
 */
class Club extends AbstractEntity
{
    protected bool $hidden = false;

    /**
     * @Extbase\Validate("NotEmpty")
     */
    protected string $title = '';

    protected string $pathSegment = '';

    protected string $activity = '';

    protected string $contactPerson = '';

    protected string $contactTimes = '';

    /**
     * @Extbase\Validate("EmailAddress")
     */
    protected string $email = '';

    protected string $website = '';

    protected string $members = '';

    protected string $clubHome = '';

    protected string $description = '';

    /**
     * @var ObjectStorage<FrontendUser>
     */
    protected ObjectStorage $feUsers;

    /**
     * @var ObjectStorage<FileReference>
     */
    protected ObjectStorage $logo;

    /**
     * @var ObjectStorage<FileReference>
     */
    protected ObjectStorage $images;

    protected string $facebook = '';

    protected string $twitter = '';

    protected string $instagram = '';

    protected string $tags = '';

    protected District $district;

    /**
     * @var ObjectStorage<Address>
     * @Extbase\ORM\Cascade("remove")
     */
    protected ObjectStorage $addresses;

    /**
     * @var ObjectStorage<Category>
     * @Extbase\ORM\Lazy
     */
    protected ObjectStorage $categories;

    public function __construct()
    {
        $this->feUsers = new ObjectStorage();
        $this->logo = new ObjectStorage();
        $this->images = new ObjectStorage();
        $this->addresses = new ObjectStorage();
        $this->categories = new ObjectStorage();
    }

    /**
     * SF: The "target" property is not part of persistence and will therefore not be filled by DataMapper
     * with an ObjectStorage. Further DataMapper prevents calling the constructor of domain models, that's why we
     * have to initialize the target property manually here.
     */
    public function initializeObject(): void
    {
        $this->feUsers = $this->feUsers ?? new ObjectStorage();
        $this->logo = $this->logo ?? new ObjectStorage();
        $this->images = $this->images ?? new ObjectStorage();
        $this->addresses = $this->addresses ?? new ObjectStorage();
        $this->categories = $this->categories ?? new ObjectStorage();
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

    public function getPathSegment(): string
    {
        return $this->pathSegment;
    }

    public function setPathSegment(string $pathSegment): void
    {
        $this->pathSegment = $pathSegment;
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
        $frontendUserRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);
        if ($frontendUserRepository->getCurrentFrontendUserRecord() !== []) {
            foreach ($this->getFeUsers() as $feUser) {
                if ($feUser->getUid() === $frontendUserRepository->getCurrentFrontendUserUid()) {
                    $currentUserCanEditThisClub = true;
                    break;
                }
            }
        }
        return $currentUserCanEditThisClub;
    }

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

    public function getImages(): array
    {
        $references = [];
        foreach ($this->images as $image) {
            $references[] = $image;
        }

        return $references;
    }

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

    public function getInstagram(): string
    {
        return $this->instagram;
    }

    public function setInstagram(string $instagram): void
    {
        $this->instagram = $instagram;
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

    /**
     * Helper method to build a baseRecord for path_segment
     * Needed in PathSegmentHelper
     */
    public function getBaseRecordForPathSegment(): array
    {
        return [
            'uid' => $this->getUid(),
            'pid' => $this->getPid(),
            'title' => $this->getTitle(),
        ];
    }
}
