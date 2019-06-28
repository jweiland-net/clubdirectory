<?php
declare(strict_types=1);
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

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Domain model which represents a District
 */
class District extends AbstractEntity
{
    /**
     * @var string
     * @validate NotEmpty
     */
    protected $district = '';

    /**
     * @return string $district
     */
    public function getDistrict(): string
    {
        return $this->district;
    }

    /**
     * @param string $district
     */
    public function setDistrict(string $district)
    {
        $this->district = $district;
    }
}
