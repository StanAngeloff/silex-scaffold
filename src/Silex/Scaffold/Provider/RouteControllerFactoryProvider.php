<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\Provider;

use Silex\Scaffold\DependencyInjection\Injector;
use Silex\Scaffold\Exception\InvalidArgumentException;
use Silex\Scaffold\Utility\ReflectionUtility;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Symfony\Component\PropertyAccess\PropertyAccess;

class RouteControllerFactoryProvider implements ServiceProviderInterface
{
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
     * A cached instance of a PropertyAccessor.
     *
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $accessor;

    # {{{ ServiceProviderInterface

    /** {@inheritDoc} */
    public function register(Application $app)
    {
        $app['route_controller_factory'] = $this;
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        $app; // unused
    }

    /**
     * Create a controller for the given route in the Silex application.
     *
     * @param Application $app
     * @param array $route
     *
     * @return \Silex\Controller
     */
    public function createController(Application $app, $route)
    {
        $this->ensureRouteOptions($route);

        list ($controllerName, $actionName) = $this->splitControllerAction($route['defaults']['_controller']);

        $controllerKey = str_replace('/', '.', strtolower($controllerName) . '.controller');
        $app[$controllerKey] = $app->share(
            function (Application $app) use ($controllerName) {
                $controllerClass = strtr(
                    self::$controllerClassTemplate,
                    array(
                        '{namespace}' => ReflectionUtility::getNamespace($app),
                        '{controller}' => str_replace('/', '\\', $controllerName),
                    )
                );
                return Injector::create($app)->createInstance($controllerClass);
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
     * Ensure the routing opting are properly configured.
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private function ensureRouteOptions($route)
    {
        if (( ! is_array($route))) {
            throw new InvalidArgumentException(
                'The route options must be an array.',
                1371973949
            );
        }
        $accessor = $this->getPropertyAccessor();
        foreach (array('[pattern]', '[defaults][_controller]') as $property) {
            $value = $accessor->getValue($route, $property);
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
}
