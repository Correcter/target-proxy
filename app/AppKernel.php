<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class AppKernel extends BaseKernel
{
    use MicroKernelTrait;

    const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function getCacheDir()
    {
        return $this->getProjectDir().'/var/cache/'.$this->environment;
    }

    public function getLogDir()
    {
        return $this->getProjectDir().'/var/log';
    }

    public function getLockDir()
    {
        return $this->getProjectDir().'/var/lock';
    }

    public function registerBundles()
    {
        $contents = require $this->getProjectDir().'/app/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    protected function getKernelParameters()
    {
        $params = parent::getKernelParameters();
        $params['kernel.lock_dir'] = realpath($this->getLockDir()) ?: $this->getLockDir();

        return $params;
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->addResource(new FileResource($this->getProjectDir().'/app/config/bundles.php'));
        // Feel free to remove the "container.autowiring.strict_mode" parameter
        // if you are using symfony/dependency-injection 4.0+ as it's the default behavior
        $container->setParameter('container.autowiring.strict_mode', false);
        $container->setParameter('container.dumper.inline_class_loader', false);
        $confDir = $this->getProjectDir().'/app/config';

        $loader->load($confDir.'/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{packages}/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{packages}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');

        foreach ($this->bundles as $bundle) {
            $confDir = $bundle->getPath().'/Resources/config';

            if (!is_dir($confDir)) {
                continue;
            }

            $loader->load($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');
            $loader->load($confDir.'/{services}/'.$this->environment.'/*'.self::CONFIG_EXTS, 'glob');
        }
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $confDir = $this->getProjectDir().'/app/config';

        $routes->import($confDir.'/{routes}/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, '/', 'glob');

        foreach ($this->bundles as $bundle) {
            $confDir = $bundle->getPath().'/Resources/config';

            if (!is_dir($confDir)) {
                continue;
            }

            $routes->import($confDir.'/{routes}/*'.self::CONFIG_EXTS, '/', 'glob');
            $routes->import($confDir.'/{routing}\.'.self::CONFIG_EXTS, '/', 'glob');
            $routes->import($confDir.'/{routes}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, '/', 'glob');
        }
    }
}
