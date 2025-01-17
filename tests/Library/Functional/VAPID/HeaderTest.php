<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Functional\VAPID;

use PHPUnit\Framework\TestCase;
use WebPush\VAPID\Header;

/**
 * @internal
 */
final class HeaderTest extends TestCase
{
    /**
     * @test
     */
    public function createHeader(): void
    {
        $header = Header::create('token', 'key');

        static::assertSame('token', $header->getToken());
        static::assertSame('key', $header->getKey());
    }
}
