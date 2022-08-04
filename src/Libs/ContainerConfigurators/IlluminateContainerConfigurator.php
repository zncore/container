<?php

namespace ZnCore\Container\Libs\ContainerConfigurators;

use Psr\Container\ContainerInterface;
use ZnCore\Container\Interfaces\ContainerConfiguratorInterface;
use ZnCore\Container\Libs\Container;

class IlluminateContainerConfigurator implements ContainerConfiguratorInterface
{

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function importFromDir(array $dirs): void {
        foreach ($dirs as &$dir) {
            $dir = realpath($dir);
        }
//        dd($dirs);
    }

    public function singleton($abstract, $concrete): void
    {
        $this->container->singleton($abstract, $concrete);
    }

    public function bind($abstract, $concrete, bool $shared = false): void
    {
        $this->container->bind($abstract, $concrete, $shared);
    }

    public function bindContainerSingleton(): void
    {
//        $this->container->singleton(ContainerInterface::class, Container::class);
        $this->container->singleton(Container::class, function () {
            return $this->container;
        });
    }

    public function alias($abstract, $alias): void
    {
        $this->container->alias($abstract, $alias);
    }
}
