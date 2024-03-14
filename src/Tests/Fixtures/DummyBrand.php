<?php

namespace Rbg\Email\Tests\Fixtures;

use Rbg\Contracts\Locations\BrandInterface;

class DummyBrand implements BrandInterface
{
    public function getId(): int
    {
        return 1;
    }

    public function getName(): string
    {
        return 'RBG';
    }

    public function getShortName(): string
    {
        return 'rbg';
    }

    public function getDefaultEmailFrom(): string
    {
        return 'noreply@revolutionbarsgroup.com';
    }
}
