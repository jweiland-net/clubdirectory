<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\Hook\Form;

use JWeiland\Clubdirectory\Event\ModifyRichTextSanitizerConfigurationEvent;
use JWeiland\Clubdirectory\Form\FormOwner;
use JWeiland\Clubdirectory\Form\RichTextEditorOptions;
use JWeiland\Clubdirectory\Hook\Form\RichTextEditorHook;
use JWeiland\Clubdirectory\Sanitizer\RichTextSanitizer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 */
class RichTextEditorHookTest extends UnitTestCase
{
    private RichTextEditorOptions $richTextEditorOptions;

    private FormRuntime $formRuntime;

    private RichTextEditorHook $subject;

    /**
     * @var ModifyRichTextSanitizerConfigurationEvent[]
     */
    private array $dispatchedEvents = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->richTextEditorOptions = $this->createMock(RichTextEditorOptions::class);
        $this->richTextEditorOptions
            ->method('getEditorOptions')
            ->willReturnOnConsecutiveCalls(['language' => 'de'], ['language' => 'en']);

        $this->formRuntime = $this->createFormRuntime(FormOwner::CLUBDIRECTORY);

        $this->subject = new RichTextEditorHook($this->richTextEditorOptions, new RichTextSanitizer($this->createEventDispatcher()));
    }

    private function createEventDispatcher(): EventDispatcherInterface
    {
        $dispatchedEvents = &$this->dispatchedEvents;

        return new class ($dispatchedEvents) implements EventDispatcherInterface {
            /**
             * @param ModifyRichTextSanitizerConfigurationEvent[] $dispatchedEvents
             */
            public function __construct(private array &$dispatchedEvents) {}

            public function dispatch(object $event): object
            {
                if ($event instanceof ModifyRichTextSanitizerConfigurationEvent) {
                    $event->setAllowedTags([...$event->getAllowedTags(), 'h4']);
                    $this->dispatchedEvents[] = $event;
                }

                return $event;
            }
        };
    }

    #[Test]
    public function beforeRenderingPassesConfigurationOfColumnToEditorOptions(): void
    {
        $element = $this->buildElement('description', 'richText');
        $this->richTextEditorOptions
            ->expects(self::once())
            ->method('getEditorOptions')
            ->with(
                self::anything(),
                self::callback(static fn($configuration): bool => in_array('h4', $configuration->allowedTags, true)),
                'EXT:my_sitepackage/Resources/Public/CKEditor/',
            );

        $this->subject->beforeRendering($this->formRuntime, $element);

        self::assertCount(1, $this->dispatchedEvents);
        self::assertSame('tx_clubdirectory_domain_model_club', $this->dispatchedEvents[0]->getTable());
        self::assertSame('description', $this->dispatchedEvents[0]->getColumn());
    }

    #[Test]
    public function beforeRenderingAddsEditorToRichTextElement(): void
    {
        $element = $this->buildElement('description', 'richText');

        $this->subject->beforeRendering($this->formRuntime, $element);

        self::assertSame(
            ['class' => 'x', 'data-clubdirectory-rich-text' => '{"language":"de"}'],
            $element->getProperties()['fluidAdditionalAttributes'],
        );
    }

    #[Test]
    public function beforeRenderingAddsEditorToEachRichTextElement(): void
    {
        $firstElement = $this->buildElement('description', 'richText');
        $secondElement = $this->buildElement('program', 'richText');

        $this->subject->beforeRendering($this->formRuntime, $firstElement);
        $this->subject->beforeRendering($this->formRuntime, $secondElement);

        self::assertSame('{"language":"de"}', $firstElement->getProperties()['fluidAdditionalAttributes']['data-clubdirectory-rich-text']);
        self::assertSame('{"language":"en"}', $secondElement->getProperties()['fluidAdditionalAttributes']['data-clubdirectory-rich-text']);
    }

    #[Test]
    public function beforeRenderingIgnoresPlainTextElement(): void
    {
        $element = $this->buildElement('short-description', 'plainText');
        $this->richTextEditorOptions->expects(self::never())->method('getEditorOptions');

        $this->subject->beforeRendering($this->formRuntime, $element);

        self::assertSame(['class' => 'x'], $element->getProperties()['fluidAdditionalAttributes']);
    }

    #[Test]
    public function beforeRenderingIgnoresFormDefinition(): void
    {
        $this->richTextEditorOptions->expects(self::never())->method('getEditorOptions');

        $this->subject->beforeRendering($this->formRuntime, new FormDefinition('newClub'));
    }

    #[Test]
    public function beforeRenderingIgnoresRichTextElementOfForeignForm(): void
    {
        $element = $this->buildElement('description', 'richText');
        $this->richTextEditorOptions->expects(self::never())->method('getEditorOptions');

        $this->subject->beforeRendering($this->createFormRuntime('events2'), $element);

        self::assertSame(['class' => 'x'], $element->getProperties()['fluidAdditionalAttributes']);
    }

    #[Test]
    public function beforeRenderingIgnoresRichTextElementOfFormWithoutOwner(): void
    {
        $element = $this->buildElement('description', 'richText');
        $this->richTextEditorOptions->expects(self::never())->method('getEditorOptions');

        $this->subject->beforeRendering($this->createFormRuntime(''), $element);

        self::assertSame(['class' => 'x'], $element->getProperties()['fluidAdditionalAttributes']);
    }

    private function createFormRuntime(string $formOwner): FormRuntime&MockObject
    {
        $formDefinition = new FormDefinition('newClub');
        if ($formOwner !== '') {
            $formDefinition->setRenderingOption(FormOwner::RENDERING_OPTION, $formOwner);
        }

        $formRuntime = $this->createMock(FormRuntime::class);
        $formRuntime->method('getRequest')->willReturn($this->createMock(Request::class));
        $formRuntime->method('getFormDefinition')->willReturn($formDefinition);

        return $formRuntime;
    }

    private function buildElement(string $identifier, string $dataType): GenericFormElement
    {
        $element = new GenericFormElement($identifier, 'Textarea');
        $element->setProperty('dbMapping', ['column' => 'description', 'dataType' => $dataType]);
        $element->setProperty('richTextEditor', ['translationsPath' => 'EXT:my_sitepackage/Resources/Public/CKEditor/']);
        $element->setProperty('fluidAdditionalAttributes', ['class' => 'x']);

        return $element;
    }
}
