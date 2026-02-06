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
    protected array $source = [];

    protected int $key = 0;

    protected ?UploadedFile $uploadedFile = null;

    protected ?FileReference $alreadyPersistedImage = null;

    protected ?Error $error = null;

    public function __construct(
        array $source,
        int $key,
        ?UploadedFile $uploadedFile = null,
        ?FileReference $alreadyPersistedImage = null,
    ) {
        $this->source = $source;
        $this->key = $key;
        $this->uploadedFile = $uploadedFile;
        $this->alreadyPersistedImage = $alreadyPersistedImage;
    }

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
