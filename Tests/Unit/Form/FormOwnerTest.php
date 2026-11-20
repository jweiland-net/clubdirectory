<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\Form;

use JWeiland\Clubdirectory\Form\FormOwner;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case for FormOwner
 */
class FormOwnerTest extends UnitTestCase
{
    #[Test]
    public function markConfigurationSetsRenderingOptionAndKeepsOtherOptions(): void
    {
        self::assertSame(
            ['renderingOptions' => ['submitButtonLabel' => 'Submit', 'formOwner' => 'clubdirectory']],
            FormOwner::markConfiguration(['renderingOptions' => ['submitButtonLabel' => 'Submit', 'formOwner' => 'events2']]),
        );
    }

    #[Test]
    public function isClubdirectoryFormWithMarkedFormReturnsTrue(): void
    {
        $formDefinition = new FormDefinition('newClub');
        $formDefinition->setRenderingOption(FormOwner::RENDERING_OPTION, FormOwner::CLUBDIRECTORY);

        self::assertTrue(FormOwner::isClubdirectoryForm($formDefinition));
        self::assertTrue(FormOwner::isRenderableOfClubdirectoryForm($formDefinition));
    }

    #[Test]
    public function isClubdirectoryFormWithFormOfOtherOwnerReturnsFalse(): void
    {
        $formDefinition = new FormDefinition('newEvent');
        $formDefinition->setRenderingOption(FormOwner::RENDERING_OPTION, 'events2');

        self::assertFalse(FormOwner::isClubdirectoryForm($formDefinition));
    }

    #[Test]
    public function isClubdirectoryFormWithoutFormReturnsFalse(): void
    {
        self::assertFalse(FormOwner::isClubdirectoryForm(null));
        self::assertFalse(FormOwner::isClubdirectoryForm(new FormDefinition('contact')));
    }

    #[Test]
    public function isRenderableOfClubdirectoryFormWithElementWithoutFormReturnsFalse(): void
    {
        self::assertFalse(FormOwner::isRenderableOfClubdirectoryForm(new GenericFormElement('description', 'Textarea')));
    }
}
