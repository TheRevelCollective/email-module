<?php

namespace Rbg\Email\Factory;

use Rbg\Contracts\Locator\FactoryInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventDispatcher;

class MailerFactory implements FactoryInterface
{
    private string $dsn;
    private EventDispatcher $dispatcher;

    public function __construct(string $dsn, EventDispatcher $dispatcher)
    {
        $this->dsn = $dsn;
        $this->dispatcher = $dispatcher;
    }

    public function createInstance(): Mailer
    {
        $transport = Transport::fromDsn($this->dsn);
        return new Mailer($transport, null, $this->dispatcher);
    }
}
