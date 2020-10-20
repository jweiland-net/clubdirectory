<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Configuration;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class, which contains the configuration from ExtensionManager
 */
class ExtConf implements SingletonInterface
{
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
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('clubdirectory');
        if (is_array($extConf) && count($extConf)) {
            // call setter method foreach configuration entry
            foreach ($extConf as $key => $value) {
                $methodName = 'set' . ucfirst($key);
                if (method_exists($this, $methodName)) {
                    $this->$methodName((string)$value);
                }
            }
        }
    }

    public function getUserGroup(): int
    {
        return $this->userGroup;
    }

    public function setUserGroup(string $userGroup): void
    {
        $this->userGroup = (int)$userGroup;
    }

    public function getPoiCollectionPid(): int
    {
        return $this->poiCollectionPid;
    }

    public function setPoiCollectionPid(string $poiCollectionPid): void
    {
        $this->poiCollectionPid = (int)$poiCollectionPid;
    }

    public function getRootCategory(): int
    {
        return $this->rootCategory;
    }

    public function setRootCategory(string $rootCategory): void
    {
        $this->rootCategory = (int)$rootCategory;
    }

    public function getEmailFromAddress(): string
    {
        if (empty($this->emailFromAddress)) {
            $senderMail = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'];
            if (empty($senderMail)) {
                throw new \Exception(
                    'You have forgotten to set a sender email address in extension configuration or in install tool'
                );
            }
            return $senderMail;
        }
        return $this->emailFromAddress;
    }

    public function setEmailFromAddress(string $emailFromAddress): void
    {
        $this->emailFromAddress = $emailFromAddress;
    }

    public function getEmailFromName(): string
    {
        if (empty($this->emailFromName)) {
            $senderName = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'];
            if (empty($senderName)) {
                throw new \Exception('You have forgotten to set a sender name in extension configuration or in install tool');
            }
            return $senderName;
        }
        return $this->emailFromName;
    }

    public function setEmailFromName(string $emailFromName): void
    {
        $this->emailFromName = $emailFromName;
    }

    public function getEmailToAddress(): string
    {
        if (empty($this->emailToAddress)) {
            throw new \Exception('You have forgotten to set a receiver email address in extension configuration of clubdirectory');
        }
        return $this->emailToAddress;
    }

    public function setEmailToAddress(string $emailToAddress): void
    {
        $this->emailToAddress = $emailToAddress;
    }

    public function getEmailToName(): string
    {
        return $this->emailToName;
    }

    public function setEmailToName(string $emailToName): void
    {
        $this->emailToName = $emailToName;
    }
}
