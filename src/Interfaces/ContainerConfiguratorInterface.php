<?php

namespace ZnCore\Container\Interfaces;

interface ContainerConfiguratorInterface
{

    public function importFromDir(array $dirs): void;

    public function singleton($abstract, $concrete): void;

    public function bind($abstract, $concrete, bool $shared = false): void;

    public function bindContainerSingleton(): void;

    public function alias($abstract, $alias): void;
}
