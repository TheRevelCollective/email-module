<?php

namespace Rbg\Email\Factory;

use Rbg\Contracts\Locator\FactoryInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Rbg\Utilities\ApplicationHelper;
use Twig\Extension\DebugExtension;
use Twig\Extra\Inky\InkyExtension;
use Twig\Extra\CssInliner\CssInlinerExtension;
use Twig\Extension\StringLoaderExtension;

class TwigRendererFactory implements FactoryInterface
{
    private string $path;
    private array $options;

    public function __construct(string $path, array $options)
    {
        $this->path = $path;
        $this->options = $options;
    }

    public function createInstance(): Environment
    {
        $loader = new FilesystemLoader([
            ApplicationHelper::getApplicationRoot() . '/' . $this->path,
            ApplicationHelper::getApplicationRoot() . '/vendor/rbg/email/views',
        ]);

        $twig = new Environment($loader, $this->options);
        $twig->addExtension(new InkyExtension());
        $twig->addExtension(new CssInlinerExtension());
        $twig->addExtension(new DebugExtension());
        $twig->addExtension(new StringLoaderExtension());
        return $twig;
    }
}
