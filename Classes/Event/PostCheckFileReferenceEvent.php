<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Event;

use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Error\Error;

class PostCheckFileReferenceEvent
{
    protected ?Error $error = null;

    public function __construct(
        protected readonly array $source,
        protected readonly int $key,
        protected readonly ?UploadedFile $uploadedFile = null,
        protected readonly ?FileReference $alreadyPersistedImage = null,
    ) {}

    public function getSource(): array
    {
        return $this->source;
    }

    public function getKey(): int
    {
        return $this->key;
    }

    public function getUploadedFile(): UploadedFile
    {
        return $this->uploadedFile;
    }

    public function getAlreadyPersistedImage(): ?FileReference
    {
        return $this->alreadyPersistedImage;
    }

    public function getError(): ?Error
    {
        return $this->error;
    }

    public function setError(Error $error): void
    {
        $this->error = $error;
    }
}
