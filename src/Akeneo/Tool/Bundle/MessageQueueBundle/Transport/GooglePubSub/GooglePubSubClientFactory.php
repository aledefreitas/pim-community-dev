<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessageQueueBundle\Transport\GooglePubSub;

use Google\Cloud\PubSub\PubSubClient;

class GooglePubSubClientFactory
{
    public function createClient(array $config): PubSubClient
    {
        return new PubSubClient($config);
    }
}
