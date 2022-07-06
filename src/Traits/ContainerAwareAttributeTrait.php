<?php

namespace ZnCore\Container\Traits;

use Psr\Container\ContainerInterface;

trait ContainerAwareAttributeTrait
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    protected function ensureContainer(ContainerInterface $container = null): ?ContainerInterface
    {
        return $container ?: $this->container;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
