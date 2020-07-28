<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessageQueueBundle\Stamp;

use Akeneo\Tool\Bundle\MessageQueueBundle\Stamp\NativeMessageStamp;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Stamp\StampInterface;

class NativeMessageStampSpec extends ObjectBehavior
{
    public function let(): void
    {
        $nativeMessage = new \stdClass();
        $this->beConstructedWith($nativeMessage);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(NativeMessageStamp::class);
    }

    public function it_is_a_stamp(): void
    {
        $this->shouldImplement(StampInterface::class);
    }

    public function it_returns_the_native_message(): void
    {
        $nativeMessage = new \stdClass();
        $this->beConstructedWith($nativeMessage);

        $this->getNativeMessage()->shouldReturn($nativeMessage);
    }
}
