<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessageQueueBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessageQueueBundle\Transport\GooglePubSub\Client;
use Akeneo\Tool\Bundle\MessageQueueBundle\Transport\GooglePubSub\GooglePubSubClientFactory;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;
use PhpSpec\ObjectBehavior;

class ClientSpec extends ObjectBehavior
{
    const PROJECT_ID = 'project-id';
    const TOPIC_NAME = 'topic-name';
    const SUBSCRIPTION_NAME = 'subscription-name';

    public function let(GooglePubSubClientFactory $pubSubClientFactory, PubSubClient $pubSubClient): void
    {
        $pubSubClientFactory->createClient(['projectId' => self::PROJECT_ID])
            ->willReturn($pubSubClient);
        $pubSubClient->createTopic(self::TOPIC_NAME)
            ->willReturn(null);
        $pubSubClient->subscribe(self::SUBSCRIPTION_NAME, self::TOPIC_NAME)
            ->willReturn(null);

        $this->beConstructedWith(
            self::PROJECT_ID,
            self::TOPIC_NAME,
            self::SUBSCRIPTION_NAME,
            $pubSubClientFactory
        );
    }

    public function it_is_initializable($pubSubClient): void
    {
        $this->shouldHaveType(Client::class);
        $pubSubClient->createTopic(self::TOPIC_NAME)
            ->shouldBeCalled();
        $pubSubClient->subscribe(self::SUBSCRIPTION_NAME, self::TOPIC_NAME)
            ->shouldBeCalled();
    }

    public function it_is_initializable_from_a_dsn(
        GooglePubSubClientFactory $pubSubClientFactory,
        PubSubClient $pubSubClient
    ): void {
        $dsn = "gps:?project_id={self::PROJECT_ID}&topic_name={self::TOPIC_NAME}";
        $options = ['transport_name' => self::SUBSCRIPTION_NAME];


        $this->beConstructedThrough('fromDsn', [$dsn, $options, $pubSubClientFactory]);
        $pubSubClientFactory->createClient(['projectId' => self::PROJECT_ID])
            ->willReturn($pubSubClient);
        $pubSubClient->createTopic(self::TOPIC_NAME)
            ->willReturn(null);
        $pubSubClient->subscribe(self::SUBSCRIPTION_NAME, self::TOPIC_NAME)
            ->willReturn(null);

        $this->shouldHaveType(Client::class);
    }

    public function it_returns_the_topic($pubSubClient, Topic $topic): void
    {
        $pubSubClient->topic(self::TOPIC_NAME)
            ->willReturn($topic);

        $this->getTopic()
            ->shouldReturn($topic);
    }

    public function it_returns_the_subscription($pubSubClient, Subscription $subscription): void
    {
        $pubSubClient->subscription(self::SUBSCRIPTION_NAME)
            ->willReturn($subscription);

        $this->getSubscription()
            ->shouldReturn($subscription);
    }
}
