<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Message;

class ProductUpdated
{
    private $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
