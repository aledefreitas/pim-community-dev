<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessageQueueBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessageQueueBundle\Transport\GooglePubSub\GooglePubSubTransportFactory;
use PhpSpec\ObjectBehavior;

class GooglePubSubTransportFactorySpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(GooglePubSubTransportFactory::class);
    }

    public function it_supports_the_gps_dsn(): void
    {
        // Supports
        $this->supports('gps:', [])->shouldBe(true);

        // Doesn't support
        $this->supports('', [])->shouldBe(false);
    }
}
