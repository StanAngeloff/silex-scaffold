<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\Loader;

use Symfony\Component\Config\Loader\FileLoader;

use Symfony\Component\Yaml\Yaml;

/**
 * YamlFileLoader loads configuration from .yml files.
 */
class YamlFileLoader extends FileLoader
{
    /**
     * {@inheritDoc}
     */
    public function load($resource, $type = null)
    {
        return Yaml::parse($resource);
        $type; // unused
    }

    /**
     * {@inheritDoc}
     */
    public function supports($resource, $type = null)
    {
        return (
            is_string($resource)
            && pathinfo($resource, PATHINFO_EXTENSION) === 'yml'
        );
        $type; // unused
    }
}
