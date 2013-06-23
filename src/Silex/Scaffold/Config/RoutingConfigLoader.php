<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\Config;

use Silex\Scaffold\Loader\YamlFileLoader;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;

class RoutingConfigLoader extends DelegatingLoader
{
    /**
     * Initialize a new RoutingConfigLoader instance.
     */
    public function __construct()
    {
        parent::__construct(
            $this->getLoaderResolver()
        );
    }

    /**
     * Get a LoaderResolver interface.
     *
     * @return LoaderResolver
     */
    private function getLoaderResolver()
    {
        return new LoaderResolver(
            $this->getLoaders()
        );
    }

    /**
     * Get an array of file loaders.
     *
     * @return \Symfony\Component\Config\Loader\FileLoader[]
     */
    private function getLoaders()
    {
        $locator = $this->getLocator();
        return array(
            new YamlFileLoader($locator),
        );
    }

    /**
     * Get a FileLocator interface.
     *
     * @return FileLocator
     */
    private function getLocator()
    {
        return new FileLocator();
    }
}
