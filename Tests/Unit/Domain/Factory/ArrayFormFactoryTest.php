<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\Domain\Factory;

use JWeiland\Clubdirectory\Configuration\ExtConf;
use JWeiland\Clubdirectory\Domain\Factory\ArrayFormFactory;
use JWeiland\Clubdirectory\Service\ClubAccessService;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case for the modifications of the form configuration in ArrayFormFactory
 */
class ArrayFormFactoryTest extends UnitTestCase
{
    private function createSubject(int $clubUid, string $moderationToken, ExtConf $extConf = new ExtConf()): ArrayFormFactory
    {
        $clubAccessService = self::createStub(ClubAccessService::class);
        $clubAccessService->method('getClubUidFromQuery')->willReturn($clubUid);
        $clubAccessService->method('getModerationTokenFromQuery')->willReturn($moderationToken);

        return new class ($extConf, $clubAccessService) extends ArrayFormFactory {
            /**
             * @param array<string, mixed> $configuration
             * @return array<string, mixed>
             */
            public function callAddClubToFormAction(array $configuration, ServerRequestInterface $request): array
            {
                return $this->addClubToFormAction($configuration, $request);
            }

            public function callAddEmailAddressesToFinisher(mixed $finisher): mixed
            {
                return $this->addEmailAddressesToFinisher($finisher);
            }
        };
    }

    #[Test]
    public function addClubToFormActionKeepsClubAndModerationToken(): void
    {
        $configuration = $this->createSubject(12, 'token')->callAddClubToFormAction([], new ServerRequest());

        self::assertSame(
            ['club' => 12, 'moderationToken' => 'token'],
            $configuration['renderingOptions']['additionalParams']['tx_clubdirectory_management'],
        );
    }

    #[Test]
    public function addClubToFormActionWithoutClubKeepsConfiguration(): void
    {
        self::assertSame(['foo' => 'bar'], $this->createSubject(0, 'token')->callAddClubToFormAction(['foo' => 'bar'], new ServerRequest()));
    }

    #[Test]
    public function addEmailAddressesToFinisherAddsAddressesOfExtensionSettings(): void
    {
        $subject = $this->createSubject(0, '', new ExtConf(
            emailFromAddress: 'sender@example.com',
            emailFromName: 'Sender',
            emailToAddress: 'moderator@example.com',
            emailToName: 'Moderator',
        ));

        self::assertSame(
            [
                'identifier' => 'EmailToReceiver',
                'options' => [
                    'senderName' => 'Sender',
                    'senderAddress' => 'sender@example.com',
                    'recipients' => ['moderator@example.com' => 'Moderator'],
                ],
            ],
            $subject->callAddEmailAddressesToFinisher(['identifier' => 'EmailToReceiver']),
        );
    }

    #[Test]
    public function addEmailAddressesToFinisherKeepsConfiguredAddresses(): void
    {
        $finisher = [
            'identifier' => 'EmailToReceiver',
            'options' => [
                'senderName' => 'Form sender',
                'senderAddress' => 'form@example.com',
                'recipients' => ['form-moderator@example.com' => 'Form moderator'],
            ],
        ];

        self::assertSame($finisher, $this->createSubject(0, '')->callAddEmailAddressesToFinisher($finisher));
    }

    #[Test]
    public function addEmailAddressesToFinisherWithoutExtensionSettingsKeepsFinisher(): void
    {
        self::assertSame(
            ['identifier' => 'EmailToReceiver', 'options' => ['senderName' => 'Sender', 'senderAddress' => 'sender@example.com']],
            $this->createSubject(0, '', new ExtConf(emailFromAddress: 'sender@example.com', emailFromName: 'Sender'))
                ->callAddEmailAddressesToFinisher(['identifier' => 'EmailToReceiver']),
        );
    }
}
