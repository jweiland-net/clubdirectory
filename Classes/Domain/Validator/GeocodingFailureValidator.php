<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Domain\Validator;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validator of EXT:form, which always fails with the given message.
 * GeocodeAddressesHook adds it to an address element, if the address could
 * not be geocoded. So the user stays on the page of the address.
 */
final class GeocodingFailureValidator extends AbstractValidator
{
    /**
     * The value of the element is not relevant. The validator has to fail always.
     *
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    /**
     * @var array<string, mixed>
     */
    protected $supportedOptions = [
        'message' => ['', 'The translated message of the geocoding failure', 'string'],
    ];

    public function isValid(mixed $value): void
    {
        $this->addError((string)$this->options['message'], 1763337601);
    }
}
