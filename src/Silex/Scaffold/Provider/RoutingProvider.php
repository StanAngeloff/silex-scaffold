<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\Provider;

use Silex\Scaffold\Config\RoutingConfigLoader;
use Silex\Scaffold\Exception\InvalidConfigurationException;
use Silex\Scaffold\Exception\RuntimeException;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * This class provides routing support to Silex applications.
 */
class RoutingProvider implements ServiceProviderInterface
{
    /**
     * The name of the routing file.
     *
     * @var string
     */
    private static $routingFilename = 'routing.yml';

    /**
     * The absolute path to the configuration directory.
     *
     * @var string
     */
    private $routingPath;

    /**
     * Initialize a new RoutingProvider instance.
     *
     * @param string $routingPath The absolute path to the configuration directory.
     */
    public function __construct($routingPath = null)
    {
        if (isset ($routingPath)) {
            $this->setRoutingPath($routingPath);
        }
    }

    # {{{ Getters/Setters

    /**
     * Get the absolute path to the configuration directory.
     *
     * @return string
     */
    public function getRoutingPath()
    {
        return $this->routingPath;
    }

    /**
     * Set the absolute path to the configuration directory.
     *
     * @param string $routingPath
     * @return void
     */
    public function setRoutingPath($routingPath)
    {
        $this->routingPath = $routingPath;
    }

    # }}}

    # {{{ ServiceProviderInterface

    /** {@inheritDoc} */
    public function register(Application $app)
    {
        $app; // unused
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidConfigurationException
     */
    public function boot(Application $app)
    {
        $config = $this->getRoutingConfig();
        foreach ($config as $routeName => $route) {
            try {
                $controller = $app['route_controller_factory']->createController($app, $route);
            } catch (\Exception $previous) {
                throw new InvalidConfigurationException(
                    strtr(
                        'Cannot process routing options for route "{route}" .',
                        array('{route}' => $routeName)
                    ),
                    1371982541,
                    $previous
                );
            }
            $controller->bind($routeName);
        }
    }

    # }}}

    /**
     * Get the routing configuration as an array.
     *
     * @return array
     *
     * @throws RuntimeException
     */
    private function getRoutingConfig()
    {
        $routingFile = rtrim($this->routingPath, '\\/')
            . DIRECTORY_SEPARATOR
            . self::$routingFilename;
        $loader = new RoutingConfigLoader();
        if (( ! is_file($routingFile))) {
            throw new RuntimeException(
                strtr(
                    'Cannot find required routing file "{file}". '
                    . 'You must create this file and define at least one route.',
                    array(
                        '{file}' => $routingFile,
                    )
                ),
                1371973625
            );
        }
        $config = $loader->load($routingFile);
        return ($config ?: array());
    }
}
