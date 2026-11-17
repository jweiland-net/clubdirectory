<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Templates;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Parses the mail templates of the EmailToReceiver finisher. So unknown
 * ViewHelpers and syntax errors are found without sending a mail.
 */
class EmailFinisherTemplatesTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'typo3/cms-form',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    public static function templateDataProvider(): array
    {
        return [
            'HTML' => ['Default.html'],
            'plain text' => ['Default.txt'],
        ];
    }

    #[Test]
    #[DataProvider('templateDataProvider')]
    public function templateCanBeParsed(string $templateName): void
    {
        $templateSource = (string)file_get_contents(
            __DIR__ . '/../../../Resources/Private/FormFramework/Templates/Finishers/Email/' . $templateName,
        );

        $parsedTemplate = $this->get(RenderingContextFactory::class)
            ->create()
            ->getTemplateParser()
            ->parse($templateSource, $templateName);

        self::assertSame('SystemEmail', $parsedTemplate->getLayoutName($this->get(RenderingContextFactory::class)->create()));
    }
}
