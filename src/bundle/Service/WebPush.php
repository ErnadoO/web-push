<?php

declare(strict_types=1);

namespace WebPush\Bundle\Service;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use WebPush\ExtensionManager;
use WebPush\Loggable;
use WebPush\NotificationInterface;
use WebPush\SubscriptionInterface;
use WebPush\WebPushService;

final class WebPush implements WebPushService, Loggable
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly ExtensionManager $extensionManager
    ) {
        $this->logger = new NullLogger();
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function send(NotificationInterface $notification, SubscriptionInterface $subscription): StatusReport
    {
        $this->logger->debug('Sending notification', [
            'notification' => $notification,
            'subscription' => $subscription,
        ]);
        $requestData = $this->extensionManager->process($notification, $subscription);
        $response = $this->client->request('POST', $subscription->getEndpoint(), [
            'body' => $requestData->getBody(),
            'headers' => $requestData->getHeaders(),
        ]);
        $this->logger->debug('Response received', [
            'response' => $response,
        ]);

        return new StatusReport($subscription, $notification, $response);
    }
}
