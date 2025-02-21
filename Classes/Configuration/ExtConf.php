<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Configuration;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

/**
 * Class, which contains the configuration from ExtensionManager
 */
#[Autoconfigure(constructor: 'create')]
final readonly class ExtConf
{
    private const EXT_KEY = 'clubdirectory';

    private const DEFAULT_SETTINGS = [
        // General Settings
        'userGroup' => 0,
        'poiCollectionPid' => 0,
        'rootCategory' => 0,

        // Email Settings
        'emailFromAddress' => '',
        'emailFromName' => '',
        'emailToAddress' => '',
        'emailToName' => '',
    ];

    public function __construct(
        private int $userGroup = self::DEFAULT_SETTINGS['userGroup'],
        private int $poiCollectionPid = self::DEFAULT_SETTINGS['poiCollectionPid'],
        private int $rootCategory = self::DEFAULT_SETTINGS['rootCategory'],
        private string $emailFromAddress = self::DEFAULT_SETTINGS['emailFromAddress'],
        private string $emailFromName = self::DEFAULT_SETTINGS['emailFromName'],
        private string $emailToAddress = self::DEFAULT_SETTINGS['emailToAddress'],
        private string $emailToName = self::DEFAULT_SETTINGS['emailToName'],
    ) {}

    public static function create(ExtensionConfiguration $extensionConfiguration): self
    {
        $extensionSettings = self::DEFAULT_SETTINGS;

        // Overwrite default extension settings with values from EXT_CONF
        try {
            $extensionSettings = array_merge(
                $extensionSettings,
                $extensionConfiguration->get(self::EXT_KEY),
            );
        } catch (ExtensionConfigurationExtensionNotConfiguredException|ExtensionConfigurationPathDoesNotExistException) {
        }
    }

    public function getUserGroup(): int
    {
        return $this->userGroup;
    }

    public function getPoiCollectionPid(): int
    {
        return $this->poiCollectionPid;
    }

    public function getRootCategory(): int
    {
        return $this->rootCategory;
    }

    public function getEmailFromAddress(): string
    {
        if ($this->emailFromAddress === '') {
            $senderMail = (string)($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] ?? '');
            if ($senderMail === '') {
                throw new \InvalidArgumentException(
                    'You have forgotten to set a sender email address in extension configuration or in install tool',
                );
            }

            return $senderMail;
        }

        return $this->emailFromAddress;
    }

    public function getEmailFromName(): string
    {
        if ($this->emailFromName === '') {
            $senderName = (string)($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] ?? '');
            if ($senderName === '') {
                throw new \InvalidArgumentException('You have forgotten to set a sender name in extension configuration or in install tool');
            }

            return $senderName;
        }

        return $this->emailFromName;
    }

    public function getEmailToAddress(): string
    {
        if ($this->emailToAddress === '') {
            throw new \InvalidArgumentException('You have forgotten to set a receiver email address in extension configuration of clubdirectory');
        }

        return $this->emailToAddress;
    }

    public function getEmailToName(): string
    {
        return $this->emailToName;
    }
}
