<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\EventListener;

use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Event\PreProcessControllerActionEvent;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * Files will be uploaded in typeConverter automatically.
 * But, if an error occurs, we have to remove them.
 */
class DeleteUploadedFilesEventListener extends AbstractControllerEventListener
{
    private const ARGUMENT_NAME = 'club';

    protected array $allowedControllerActions = [
        'Club' => [
            'new',
        ],
    ];

    public function __invoke(PreProcessControllerActionEvent $event): void
    {
        if (
            $this->isValidRequest($event)
            && $event->getRequest()->hasArgument(self::ARGUMENT_NAME)
            && ($club = $event->getRequest()->getArgument(self::ARGUMENT_NAME))
            && $club instanceof Club
        ) {
            $this->deleteFiles($club->getLogo());
            $this->deleteFiles($club->getImages());
        }
    }

    /**
     * @param FileReference[] $fileReferences
     */
    private function deleteFiles(array $fileReferences): void
    {
        if ($fileReferences === []) {
            return;
        }

        foreach ($fileReferences as $fileReference) {
            $fileReference->getOriginalResource()->getOriginalFile()->delete();
        }
    }
}
