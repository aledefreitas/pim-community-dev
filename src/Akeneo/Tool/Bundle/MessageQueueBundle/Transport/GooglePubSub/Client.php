<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessageQueueBundle\Transport\GooglePubSub;

use Google\Cloud\Core\Exception\ConflictException;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;

class Client
{
    /** @var string */
    private $projectId;

    /** @var string */
    private $topicName;

    /** @var string */
    private $subscriptionName;

    /** @var GooglePubSubClientFactory */
    private $pubSubClientFactory;

    /** @var PubSubClient */
    private $pubSubClient;

    /**
     * @param string $dsn `gps:?project_id=PROJECT_ID&topic_name=TOPIC_NAME`,
     *                    optionally `&subscription_name=SUBSCRIPTION_NAME` (default is `$options['transport_name']`)
     * @param array{transport_name: string} $options
     */
    public static function fromDsn(
        string $dsn,
        array $options = [],
        GooglePubSubClientFactory $pubSubClientFactory = null
    ): self {
        ['scheme' => $scheme, 'query' => $query] = parse_url($dsn);
        if ('gps' !== $scheme) {
            throw new \InvalidArgumentException(sprintf('The given DSN scheme "%s" is invalid.', $scheme));
        }

        parse_str($query ?? '', $parsedQuery);
        foreach (['project_id', 'topic_name'] as $queryParam) {
            if (!isset($parsedQuery[$queryParam])) {
                throw new \InvalidArgumentException(
                    sprintf('The required DSN query parameter "%s" is missing.', $queryParam)
                );
            }
        }

        $client = new self(
            $parsedQuery['project_id'],
            $parsedQuery['topic_name'],
            $parsedQuery['subscription_name'] ?? $options['transport_name'],
            $pubSubClientFactory
        );

        return $client;
    }

    public function __construct(
        string $projectId,
        string $topicName,
        string $subscriptionName,
        GooglePubSubClientFactory $pubSubClientFactory = null
    ) {
        $this->projectId = $projectId;
        $this->topicName = $topicName;
        $this->subscriptionName = $subscriptionName;
        $this->pubSubClientFactory = $pubSubClientFactory ?? new GooglePubSubClientFactory();

        $this->setup();
    }

    private function setup(): void
    {
        $this->pubSubClient = $this->pubSubClientFactory->createClient([
            'projectId' => $this->projectId
        ]);

        try {
            $this->pubSubClient->createTopic($this->topicName);
        } catch (ConflictException $e) {
            // Ignore conflict if the Topic already exist.
        }

        try {
            $this->pubSubClient->subscribe($this->subscriptionName, $this->topicName);
        } catch (ConflictException $e) {
            // Ignore conflict if the Subscription already exist.
        }
    }

    public function getTopic(): Topic
    {
        return $this->pubSubClient->topic($this->topicName);
    }

    public function getSubscription(): Subscription
    {
        return $this->pubSubClient->subscription($this->subscriptionName);
    }
}
