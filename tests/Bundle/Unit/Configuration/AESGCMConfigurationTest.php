<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Unit\Configuration;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use WebPush\Payload\AESGCM;

/**
 * @internal
 */
final class AESGCMConfigurationTest extends AbstractConfigurationTest
{
    /**
     * @test
     *
     * @dataProvider invalidPaddings
     */
    public function invalidIfThePaddingDoesNotFulfillTheConstraints(string|int|bool $padding): void
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'payload' => [
                        'aesgcm' => [
                            'padding' => $padding,
                        ],
                    ],
                ],
            ],
            'Invalid configuration for path "webpush.payload.aesgcm.padding": The padding must have one of the following value: none, recommended, max or an integer between 0 and 4078'
        );
    }

    public function invalidPaddings(): array
    {
        return [['min'], [-1], [AESGCM::PADDING_MAX + 1]];
    }

    /**
     * @test
     *
     * @dataProvider validPaddings
     */
    public function validPadding(string|int|bool $padding): void
    {
        $this->assertProcessedConfigurationEquals(
            [
                [
                    'payload' => [
                        'aesgcm' => [
                            'padding' => $padding,
                        ],
                    ],
                ],
            ],
            [
                'logger' => null,
                'http_client' => ClientInterface::class,
                'request_factory' => RequestFactoryInterface::class,
                'doctrine_mapping' => false,
                'vapid' => [
                    'enabled' => false,
                    'token_lifetime' => 'now +1hour',
                    'web_token' => [
                        'enabled' => false,
                    ],
                    'lcobucci' => [
                        'enabled' => false,
                    ],
                    'custom' => [
                        'enabled' => false,
                    ],
                ],
                'payload' => [
                    'aes128gcm' => [
                        'padding' => 'recommended',
                        'cache' => null,
                        'cache_lifetime' => 'now + 30min',
                    ],
                    'aesgcm' => [
                        'padding' => $padding,
                        'cache' => null,
                        'cache_lifetime' => 'now + 30min',
                    ],
                ],
            ]
        );
    }

    public function validPaddings(): array
    {
        return [['none'], ['recommended'], ['max'], [0], [AESGCM::PADDING_MAX]];
    }
}
