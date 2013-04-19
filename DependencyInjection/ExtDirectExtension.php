<?php
namespace Ext\DirectBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

/**
 * DirectExtension is an extension for the ExtDirect.
 *
 * @author Otavio Fernandes <otavio@neton.com.br>
 * @author Semyon Velichko <semyon@velichko.net>
 */
class ExtDirectExtension extends Extension
{
    /**
     * Loads the Direct configuration.
     *
     * @param array $config An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        $container->getDefinition('ext_direct.controller')
            ->addMethodCall('setConfig', array($config));
        // $container->getDefinition('ext_direct.controller_resolver')
        //     ->addMethodCall('setConfig', array($config));
        $container->getDefinition('ext_direct')
            ->addMethodCall('setConfig', array($config));
        $container->getDefinition('ext_direct.api')
            ->addMethodCall('setConfig', array($config));

        // $remote_attribute = str_replace('@','',$config['annotations']['remote_attribute']);
        // $form_attribute = str_replace('@','',$config['annotations']['form_attribute']);
        // \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName($remote_attribute);
        // \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName($form_attribute);
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://www.neton.com.br/schema/dic/direct';
    }
}
