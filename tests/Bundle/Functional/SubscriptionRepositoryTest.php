<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WebPush\Tests\Bundle\FakeApp\Entity\Subscription;
use WebPush\Tests\Bundle\FakeApp\Repository\SubscriptionRepository;

/**
 * @internal
 */
final class SubscriptionRepositoryTest extends KernelTestCase
{
    protected function setUp(): void
    {
        $kernel = static::bootKernel();
        /** @var ManagerRegistry $registry */
        $registry = $kernel->getContainer()
            ->get('doctrine')
        ;
        /** @var EntityManagerInterface $em */
        $em = $registry->getManager();
        $cmf = $em->getMetadataFactory();

        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema([
            $cmf->getMetadataFor(Subscription::class),
            $cmf->getMetadataFor(\WebPush\Subscription::class),
        ]);
    }

    /**
     * @test
     */
    public function aSubscriptionCanBeSaved(): void
    {
        $data = '{"endpoint":"https:\/\/fcm.googleapis.com\/fcm\/send\/fsTzuK_gGAE:APA91bGOo_qYwoGQoiKt6tM_GX9-jNXU9yGF4stivIeRX4cMZibjiXUAojfR_OfAT36AZ7UgfLbts011308MY7IYUljCxqEKKhwZk0yPjf9XOb-A7usa47gu1t__TfCrvQoXkrTiLuOt","contentEncoding":"aes128gcm","keys":{"p256dh":"BGx19OjV00A00o9DThFSX-q40h6FA3t_UATZLrYvJGHdruyY_6T1ug6gOczcSI2HtjV5NUGZKGmykaucnLuZgY4","auth":"gW9ZePDxvjUILvlYe3Dnug"}}';
        $subscription = Subscription::createFromBaseSubscription($data);

        /** @var SubscriptionRepository $subscriptionRepository */
        $subscriptionRepository = self::$kernel->getContainer()->get(SubscriptionRepository::class);

        $subscriptionRepository->save($subscription);
        $id = $subscription->getId();

        $fetched = $subscriptionRepository->findOneBy([
            'id' => $id,
        ]);

        static::assertNotNull($fetched, 'Unable to fetch the user');
        static::assertSame(
            'https://fcm.googleapis.com/fcm/send/fsTzuK_gGAE:APA91bGOo_qYwoGQoiKt6tM_GX9-jNXU9yGF4stivIeRX4cMZibjiXUAojfR_OfAT36AZ7UgfLbts011308MY7IYUljCxqEKKhwZk0yPjf9XOb-A7usa47gu1t__TfCrvQoXkrTiLuOt',
            $fetched->getEndpoint()
        );
        static::assertSame(['aesgcm'], $fetched->getSupportedContentEncodings());
        static::assertNull($fetched->getExpirationTime());
        static::assertSame(['p256dh', 'auth'], array_keys($fetched->getKeys()));
        static::assertSame(
            'BGx19OjV00A00o9DThFSX-q40h6FA3t_UATZLrYvJGHdruyY_6T1ug6gOczcSI2HtjV5NUGZKGmykaucnLuZgY4',
            $fetched->getKey('p256dh')
        );
        static::assertSame('gW9ZePDxvjUILvlYe3Dnug', $fetched->getKey('auth'));
    }
}
