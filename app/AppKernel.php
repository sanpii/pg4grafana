<?php
declare(strict_types = 1);

use \Symfony\Component\Config\Loader\LoaderInterface;
use \Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\HttpKernel\Kernel;
use \Symfony\Component\Routing\RouteCollectionBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;

class AppKernel extends Kernel
{
    public function registerBundles(): array
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \PommProject\PommBundle\PommBundle(),
            new \AppBundle\AppBundle(),
        ];


        if ($this->environment === 'dev') {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

    public function getRootDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return __DIR__ . '/../var/' . $this->environment . '/cache';
    }

    public function getLogDir(): string
    {
        return __DIR__ . '/../var/' . $this->environment . '/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
    }
}
