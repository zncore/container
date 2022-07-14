<?php

namespace ZnCore\Container\Libs\BundleLoaders;

use ZnCore\Arr\Helpers\ArrayHelper;
use ZnCore\Bundle\Base\BaseLoader;
use ZnCore\Instance\Libs\Resolvers\InstanceResolver;
use ZnCore\Instance\Libs\Resolvers\MethodParametersResolver;
use ZnCore\Container\Interfaces\ContainerConfiguratorInterface;
use ZnCore\Container\Libs\ContainerConfigurators\ArrayContainerConfigurator;
use ZnDomain\EntityManager\Interfaces\EntityManagerConfiguratorInterface;

class ContainerLoader extends BaseLoader
{

    public function loadAll(array $bundles): void
    {
        $config = [];
        foreach ($bundles as $bundle) {
            $containerConfigList = $this->load($bundle);
            foreach ($containerConfigList as $containerConfig) {
                $config = $this->importFromConfig([$containerConfig], $config);
            }
        }
    }

    private function importFromConfig($fileList, array $config = []): array
    {
        foreach ($fileList as $configFile) {
            $toKey = null;
            if (is_array($configFile)) {
                $toKey = $configFile[1];
                $configFile = $configFile[0];
            }
            if ($toKey) {
                $sourceConfig = ArrayHelper::getValue($config, $toKey);
            } else {
                $sourceConfig = $config;
            }
            $requiredConfig = require($configFile);

            if (is_array($requiredConfig)) {
                /*$this->loadFromArray($requiredConfig);

                $mergedConfig = ArrayHelper::merge($sourceConfig, $requiredConfig);
                ArrayHelper::setValue($config, $toKey, $mergedConfig);*/

            } elseif (is_callable($requiredConfig)) {
                $requiredConfig = $this->loadFromCallback($requiredConfig);

                /*$this->loadFromArray($requiredConfig);

                $mergedConfig = ArrayHelper::merge($sourceConfig, $requiredConfig);
                ArrayHelper::setValue($config, $toKey, $mergedConfig);*/
            }

            if ($requiredConfig) {
                $this->loadFromArray($requiredConfig);
                if (isset($requiredConfig['entities'])) {
                    unset($requiredConfig['entities']);
                }
                if (isset($requiredConfig['singletons'])) {
                    unset($requiredConfig['singletons']);
                }
                $mergedConfig = ArrayHelper::merge($sourceConfig, $requiredConfig);
                ArrayHelper::setValue($config, $toKey, $mergedConfig);
            }
        }
        return $config;
    }

    private function loadFromArray(array $requiredConfig): void
    {
        /** @var ContainerConfiguratorInterface $containerConfigurator */
        $containerConfigurator = $this
            ->getContainer()
            ->get(ContainerConfiguratorInterface::class);

        /** @var EntityManagerConfiguratorInterface $entityManagerConfigurator */
        $entityManagerConfigurator = $this
            ->getContainer()
            ->get(EntityManagerConfiguratorInterface::class);

        if (!empty($requiredConfig['singletons'])) {
            foreach ($requiredConfig['singletons'] as $abstract => $concrete) {
                $containerConfigurator->singleton($abstract, $concrete);
            }
        }

        if (!empty($requiredConfig['definitions'])) {
            foreach ($requiredConfig['definitions'] as $abstract => $concrete) {
                $containerConfigurator->bind($abstract, $concrete);
            }
        }

        if (!empty($requiredConfig['entities'])) {
            foreach ($requiredConfig['entities'] as $entityClass => $repositoryInterface) {
                $entityManagerConfigurator->bindEntity($entityClass, $repositoryInterface);
            }
        }
    }

    private function loadFromCallback(callable $requiredConfig): array
    {
        $instanceResolver = new InstanceResolver($this->getContainer());
        /** @var ArrayContainerConfigurator $containerConfigurator */
        $containerConfigurator = $instanceResolver->create(ArrayContainerConfigurator::class);
        /** @var EntityManagerConfiguratorInterface $entityManagerConfigurator */
        $entityManagerConfigurator = $this->getContainer()->get(EntityManagerConfiguratorInterface::class);

//        $instanceResolver = new InstanceResolver($this->getContainer());

        $methodParametersResolver = new MethodParametersResolver($this->getContainer());
        $params = $methodParametersResolver->resolveClosure($requiredConfig, [
            $containerConfigurator,
            $entityManagerConfigurator
        ]);

        call_user_func_array($requiredConfig, $params);

//        dd($params);
//        $requiredConfig($containerConfigurator, $entityManagerConfigurator);

        $config = $containerConfigurator->getConfig();
        $entities = ArrayHelper::getValue($config, 'entities', []);

        /*$emConfig = $entityManagerConfigurator->getConfig();
        if ($emConfig) {
            $entities = ArrayHelper::merge($entities, $emConfig);
            $config['entities'] = $entities;
//            ArrayHelper::set($config, 'entities', $entities);
        }*/
        return $config;

        /*$this
            ->getContainer()
            ->call($requiredConfig);*/
    }
}
