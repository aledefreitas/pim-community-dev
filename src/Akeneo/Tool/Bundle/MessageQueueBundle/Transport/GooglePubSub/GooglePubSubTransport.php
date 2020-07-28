<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessageQueueBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessageQueueBundle\Stamp\NativeMessageStamp;
use Google\Cloud\PubSub\Message;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GooglePubSubTransport implements TransportInterface
{
    /** @var SerializerInterface */
    private $serializer;

    /** @var Client */
    private $client;

    public function __construct(Client $client, SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->client = $client;
    }

    public function get(): iterable
    {
        $messages = $this->client->getSubscription()->pull([
            'maxMessages' => 1,
            'returnImmediately' => true,
        ]);
        if (0 === count($messages)) {
            return [];
        }

        $message = $messages[0];
        $envelope = $this->serializer->decode([
            'body' => $message->data(),
        ]);

        return [
            $envelope
                ->with(new TransportMessageIdStamp($message->id()))
                ->with(new NativeMessageStamp($message))
        ];
    }

    public function ack(Envelope $envelope): void
    {
        $this->client->getSubscription()->acknowledge($this->getNativeMessage($envelope));
    }

    public function reject(Envelope $envelope): void
    {
        $this->client->getSubscription()->acknowledge($this->getNativeMessage($envelope));
    }

    public function send(Envelope $envelope): Envelope
    {
        $encodedMessage = $this->serializer->encode($envelope);

        $this->client->getTopic()->publish(['data' => $encodedMessage['body']]);

        return $envelope;
    }

    private function getNativeMessage(Envelope $envelope): Message
    {
        /** @var NativeMessageStamp */
        if (null === $nativeMessageStamp = $envelope->last(NativeMessageStamp::class)) {
            throw new \LogicException('NativeMessageStamp should be present on the Envelope.');
        }

        return $nativeMessageStamp->getNativeMessage();
    }
}
