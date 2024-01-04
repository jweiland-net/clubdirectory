<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Helper;

use JWeiland\Clubdirectory\Configuration\ExtConf;
use Symfony\Component\Mime\Address as MailAddress;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/*
 * Helper class to send a mail.
 */
class MailHelper
{
    protected ExtConf $extConf;

    public function __construct(ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

    public function sendMail(string $mailContent, string $subject): void
    {
        $fluidEmail = GeneralUtility::makeInstance(FluidEmail::class);
        $fluidEmail
            ->to(new MailAddress($this->extConf->getEmailToAddress(), $this->extConf->getEmailToName()))
            ->from(new MailAddress($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName()))
            ->subject($subject)
            ->format('html') // only HTML mail
            ->setTemplate('SendNotification')
            ->assignMultiple([
                'subject' => $subject,
                'content' => $mailContent,
            ]);

        GeneralUtility::makeInstance(Mailer::class)->send($fluidEmail);
    }
}
