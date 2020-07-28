<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessageQueueBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessageQueueBundle\Transport\GooglePubSub\Client;
use Akeneo\Tool\Bundle\MessageQueueBundle\Transport\GooglePubSub\GooglePubSubTransport;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class GooglePubSubTransportSpec extends ObjectBehavior
{
    public function let(Client $client, SerializerInterface $serializer): void
    {
        $this->beConstructedWith($client, $serializer);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(GooglePubSubTransport::class);
    }
}
