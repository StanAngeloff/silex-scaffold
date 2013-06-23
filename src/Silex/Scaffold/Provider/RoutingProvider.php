<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\Provider;

use Silex\Scaffold\Config\RoutingConfigLoader;
use Silex\Scaffold\Exception\InvalidArgumentException;
use Silex\Scaffold\Exception\InvalidConfigurationException;
use Silex\Scaffold\Exception\RuntimeException;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Symfony\Component\PropertyAccess\PropertyAccess;

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
     * The template used to create the controller class name from the route options.
     *
     * @var string
     */
    private static $controllerClassTemplate = '\\{namespace}\\Controller\\{controller}Controller';

    /**
     * The template for invoking a controller action using the ServiceControllerServiceProvider.
     *
     * @var string
     */
    private static $controllerActionTemplate = '{controller}:{action}Action';

    /**
     * The absolute path to the configuration directory.
     *
     * @var string
     */
    private $routingPath;

    /**
     * A cached instance of a PropertyAccessor.
     *
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $accessor;

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
        foreach ($config as $routeName => $routeOptions) {
            try {
                $this->ensureRouteOptions($routeOptions);
                $controller = $this->applyRoute($app, $routeOptions);
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
     * Apply the route to the Silex application.
     *
     * @param Application $app
     * @param array $route
     *
     * @return void
     */
    private function applyRoute(Application $app, array $route)
    {
        list ($controllerName, $actionName) = $this->splitControllerAction($route['defaults']['_controller']);

        $controllerKey = str_replace('/', '.', strtolower($controllerName) . '.controller');
        $app[$controllerKey] = $app->share(
            function (Application $app) use ($controllerName) {
                $reflect = new \ReflectionClass($app);
                $controllerClass = strtr(
                    self::$controllerClassTemplate,
                    array(
                        '{namespace}' => $reflect->getNamespaceName(),
                        '{controller}' => str_replace('/', '\\', $controllerName),
                    )
                );
                return new $controllerClass();
            }
        );

        $methods = $this->getRouteMethods($route);
        $firstController = null;
        foreach ($methods as $method) {
            $controller = $app->$method(
                $route['pattern'],
                strtr(
                    self::$controllerActionTemplate,
                    array(
                        '{controller}' => $controllerKey,
                        '{action}' => $actionName,
                    )
                )
            );
            if ($firstController === null) {
                $firstController = $controller;
            }
        }

        return $firstController;
    }

    /**
     * Get the configured HTTP methods for a route.
     *
     * @param array $route
     * @return string[]
     */
    private function getRouteMethods(array $route)
    {
        if (isset ($route['methods'])) {
            return (array) $route['methods'];
        } elseif (isset ($route['defaults']['_method'])) {
            return (array) $route['defaults']['_method'];
        }
        return array('match');
    }

    /**
     * Get the controller and action name from a resource.
     *
     * @param string $resource
     * @return string[]
     *
     * @throws InvalidArgumentException
     */
    private function splitControllerAction($resource)
    {
        if (strpos($resource, ':') === false) {
            throw new InvalidArgumentException(
                'The controller resource must be in the format "Controller:action".',
                1371983353
            );
        }
        return explode(':', $resource, 2);
    }

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

    /**
     * Get an instance of a PropertyAccessor.
     *
     * @return \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private function getPropertyAccessor()
    {
        if ($this->accessor === null) {
            $this->accessor = PropertyAccess::createPropertyAccessor();
        }
        return $this->accessor;
    }

    /**
     * Ensure the routing opting are properly configured.
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private function ensureRouteOptions($routeOptions)
    {
        if (( ! is_array($routeOptions))) {
            throw new InvalidArgumentException(
                'The route options must be an array.',
                1371973949
            );
        }
        $accessor = $this->getPropertyAccessor();
        foreach (array('[pattern]', '[defaults][_controller]') as $property) {
            $value = $accessor->getValue($routeOptions, $property);
            if ($value === null) {
                throw new InvalidArgumentException(
                    strtr(
                        'The route options must define property "{property}".',
                        array('{property}' => $property)
                    ),
                    1371982396
                );
            }
        }
    }
}
