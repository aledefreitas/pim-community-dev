<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessageQueueBundle\TestCommand;

use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TestMessageHandler implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        yield TestMessage::class;
    }

    public function __invoke(TestMessage $message)
    {
    }
}
