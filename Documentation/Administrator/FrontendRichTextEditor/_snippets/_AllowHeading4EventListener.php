<?php

declare(strict_types=1);

namespace MyVendor\MySitepackage\EventListener;

use JWeiland\Clubdirectory\Event\ModifyRichTextSanitizerConfigurationEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;

#[AsEventListener(identifier: 'my-sitepackage/allow-heading-4')]
final class AllowHeading4EventListener
{
    public function __invoke(ModifyRichTextSanitizerConfigurationEvent $event): void
    {
        if ($event->getTable() !== 'tx_clubdirectory_domain_model_club'
            || $event->getColumn() !== 'description'
        ) {
            return;
        }

        // Keep <h4> instead of converting it to <p>
        $event->setAllowedTags([...$event->getAllowedTags(), 'h4']);
    }
}
