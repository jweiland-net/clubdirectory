<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Configuration;

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class, which contains the configuration from ExtensionManager
 */
class ExtConf implements SingletonInterface
{
    /**
     * @var string
     */
    protected $fallbackIconPath = '';

    /**
     * @var int
     */
    protected $userGroup = 0;

    /**
     * @var int
     */
    protected $poiCollectionPid = 0;

    /**
     * @var int
     */
    protected $rootCategory = 0;

    /**
     * @var string
     */
    protected $emailFromAddress = '';

    /**
     * @var string
     */
    protected $emailFromName = '';

    /**
     * @var string
     */
    protected $emailToAddress = '';

    /**
     * @var string
     */
    protected $emailToName = '';

    public function __construct()
    {
        // get global configuration
        $extConf = unserialize(
            $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['clubdirectory'],
            ['allowed_classes' => false]
        );
        if (is_array($extConf) && count($extConf)) {
            // call setter method foreach configuration entry
            foreach ($extConf as $key => $value) {
                $methodName = 'set' . ucfirst($key);
                if (method_exists($this, $methodName)) {
                    $this->$methodName($value);
                }
            }
        }
    }

    /**
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
     * @param string $fallbackIconPath
     */
    public function setFallbackIconPath(string $fallbackIconPath)
    {
        $this->fallbackIconPath = $fallbackIconPath;
    }

    /**
     * @return int
     */
    public function getUserGroup(): int
    {
        return $this->userGroup;
    }

    /**
     * @param int $userGroup
     */
    public function setUserGroup($userGroup)
    {
        $this->userGroup = (int)$userGroup;
    }

    /**
     * @return int
     */
    public function getPoiCollectionPid(): int
    {
        return $this->poiCollectionPid;
    }

    /**
     * @param int $poiCollectionPid
     */
    public function setPoiCollectionPid($poiCollectionPid)
    {
        $this->poiCollectionPid = (int)$poiCollectionPid;
    }

    /**
     * @return int
     */
    public function getRootCategory(): int
    {
        return $this->rootCategory;
    }

    /**
     * @param int $rootCategory
     */
    public function setRootCategory($rootCategory)
    {
        $this->rootCategory = (int)$rootCategory;
    }

    /**
     * @return string
     * @throws \Exception
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
     * @param string $emailFromAddress
     */
    public function setEmailFromAddress(string $emailFromAddress)
    {
        $this->emailFromAddress = $emailFromAddress;
    }

    /**
     * @return string
     * @throws \Exception
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
     * @param string $emailFromName
     */
    public function setEmailFromName(string $emailFromName)
    {
        $this->emailFromName = $emailFromName;
    }

    /**
     * @return string
     */
    public function getEmailToAddress(): string
    {
        return $this->emailToAddress;
    }

    /**
     * @param string $emailToAddress
     */
    public function setEmailToAddress(string $emailToAddress)
    {
        $this->emailToAddress = $emailToAddress;
    }

    /**
     * @return string
     */
    public function getEmailToName(): string
    {
        return $this->emailToName;
    }

    /**
     * @param string $emailToName
     */
    public function setEmailToName(string $emailToName)
    {
        $this->emailToName = (string)$emailToName;
    }
}
