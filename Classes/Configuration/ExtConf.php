<?php

namespace JWeiland\Clubdirectory\Configuration;

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

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class ExtConf
 */
class ExtConf implements SingletonInterface
{
    /**
     * fallback icon path
     *
     * @var string
     */
    protected $fallbackIconPath = '';

    /**
     * usergroup which is allowed to create new clubs.
     *
     * @var int
     */
    protected $userGroup = 0;

    /**
     * pid of poi collection.
     *
     * @var int
     */
    protected $poiCollectionPid = 0;

    /**
     * root category.
     *
     * @var int
     */
    protected $rootCategory = 0;

    /**
     * email from address.
     *
     * @var string
     */
    protected $emailFromAddress = '';

    /**
     * email from name.
     *
     * @var string
     */
    protected $emailFromName = '';

    /**
     * email to address.
     *
     * @var string
     */
    protected $emailToAddress = '';

    /**
     * email to name.
     *
     * @var string
     */
    protected $emailToName = '';

    /**
     * constructor of this class
     * This method reads the global configuration and calls the setter methods.
     */
    public function __construct()
    {
        // get global configuration
        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['clubdirectory']);
        if (is_array($extConf) && count($extConf)) {
            // call setter method foreach configuration entry
            foreach ($extConf as $key => $value) {
                $methodName = 'set'.ucfirst($key);
                if (method_exists($this, $methodName)) {
                    $this->$methodName($value);
                }
            }
        }
    }

    /**
     * Gets FallbackIconPath
     *
     * @return string
     */
    public function getFallbackIconPath(): string
    {
        if (!$this->fallbackIconPath) {
            $this->fallbackIconPath = '/uploads/tx_clubdirectory/';
        }
        return $this->fallbackIconPath;
    }

    /**
     * Sets FallbackIconPath
     *
     * @param string $fallbackIconPath
     * @return void
     */
    public function setFallbackIconPath(string $fallbackIconPath)
    {
        $this->fallbackIconPath = $fallbackIconPath;
    }

    /**
     * getter for userGroup.
     *
     * @return int
     */
    public function getUserGroup(): int
    {
        return $this->userGroup;
    }

    /**
     * setter for userGroup.
     *
     * @param int $userGroup
     * @return void
     */
    public function setUserGroup($userGroup)
    {
        $this->userGroup = (int)$userGroup;
    }

    /**
     * getter for poiCollectionPid.
     *
     * @return int
     */
    public function getPoiCollectionPid(): int
    {
        return $this->poiCollectionPid;
    }

    /**
     * setter for poiCollectionPid.
     *
     * @param int $poiCollectionPid
     * @return void
     */
    public function setPoiCollectionPid($poiCollectionPid)
    {
        $this->poiCollectionPid = (int)$poiCollectionPid;
    }

    /**
     * getter for rootCategory.
     *
     * @return int
     */
    public function getRootCategory(): int
    {
        return $this->rootCategory;
    }

    /**
     * setter for rootCategory.
     *
     * @param int $rootCategory
     * @return void
     */
    public function setRootCategory($rootCategory)
    {
        $this->rootCategory = (int)$rootCategory;
    }

    /**
     * getter for email from address.
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getEmailFromAddress(): string
    {
        if (empty($this->emailFromAddress)) {
            $senderMail = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'];
            if (empty($senderMail)) {
                throw new \Exception(
                    'You have forgotten to set a sender email address in extension configuration or in install tool'
                );
            } else {
                return $senderMail;
            }
        } else {
            return $this->emailFromAddress;
        }
    }

    /**
     * setter for email from address.
     *
     * @param string $emailFromAddress
     * @return void
     */
    public function setEmailFromAddress($emailFromAddress)
    {
        $this->emailFromAddress = (string)$emailFromAddress;
    }

    /**
     * getter for email from name.
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getEmailFromName(): string
    {
        if (empty($this->emailFromName)) {
            $senderName = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'];
            if (empty($senderName)) {
                throw new \Exception('You have forgotten to set a sender name in extension configuration or in install tool');
            } else {
                return $senderName;
            }
        } else {
            return $this->emailFromName;
        }
    }

    /**
     * setter for emailFromName.
     *
     * @param string $emailFromName
     * @return void
     */
    public function setEmailFromName($emailFromName)
    {
        $this->emailFromName = (string)$emailFromName;
    }

    /**
     * getter for email to address.
     *
     * @return string
     */
    public function getEmailToAddress(): string
    {
        return $this->emailToAddress;
    }

    /**
     * setter for email to address.
     *
     * @param string $emailToAddress
     * @return void
     */
    public function setEmailToAddress($emailToAddress)
    {
        $this->emailToAddress = (string)$emailToAddress;
    }

    /**
     * getter for email to name.
     *
     * @return string
     */
    public function getEmailToName(): string
    {
        return $this->emailToName;
    }

    /**
     * setter for emailToName.
     *
     * @param string $emailToName
     * @return void
     */
    public function setEmailToName($emailToName)
    {
        $this->emailToName = (string)$emailToName;
    }
}
