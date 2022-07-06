<?php

namespace ZnCore\Container\Helpers;

use Psr\Container\ContainerInterface;
use ZnCore\Container\Interfaces\ContainerConfiguratorInterface;
use ZnCore\Container\Libs\ContainerConfigurator;
use ZnCore\Container\Traits\ContainerAwareStaticAttributeTrait;

class ContainerHelper
{

    use ContainerAwareStaticAttributeTrait;

    public static function getContainerConfiguratorByContainer(ContainerInterface $container): ContainerConfiguratorInterface
    {
        return new ContainerConfigurator($container);
    }
}
