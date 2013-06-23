<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold;

use Silex\Scaffold\Application\Environment;
use Silex\Scaffold\Provider\ConfigServiceProvider;
use Silex\Scaffold\Provider\RouteControllerFactoryProvider;
use Silex\Scaffold\Provider\RoutingProvider;

use Silex\Application as BaseApplication;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\ServiceProviderInterface;

/**
 * The Silex Scaffold application.
 */
class Application extends BaseApplication
{
    /**
     * The property where the application name is stored.
     *
     * @var string
     */
    private static $propertyAppName = 'app_name';

    /**
     * The default application name.
     *
     * @var string
     */
    private static $defaultAppName = 'scaffold_app';

    /**
     * The relative path from an Application class to the configuration directory.
     *
     * @var string
     */
    private static $configurationPath = '/../app/config';

    /**
     * The relative path from an Application class to the logs directory.
     *
     * @var string
     */
    private static $logsPath = '/../app/logs';

    /**
     * {@inheritDoc}
     *
     * @param string $env The application environment.
     */
    public function __construct(array $values = array(), $env = null)
    {
        parent::__construct();

        $this['env'] = new Environment(Environment::ENV_PRODUCTION);

        # Apply overrides after setting up defaults above.
        $values = $values + array_filter(
            array(
                'env' => $env,

                'monolog.logfile' => function () {
                    return $this->getMonologLogfile();
                }
            )
        );
        foreach ($values as $key => $value) {
            $this[$key] = $value;
        }
    }

    # {{{ Getters/Setters

    /**
     * Get the application name.
     *
     * @return string
     */
    public function getName()
    {
        if (isset ($this[self::$propertyAppName])) {
            return $this[self::$propertyAppName];
        }
        return self::$defaultAppName;
    }

    /**
     * Set the application name.
     *
     * @param string $appName
     * @return void
     */
    public function setName($appName)
    {
        $this[self::$propertyAppName] = $appName;
    }

    /**
     * Get the providers registered with this Application object.
     *
     * @return \Silex\ServiceProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    # }}}

    /** {@inheritDoc} */
    public function boot()
    {
        if (( ! $this->booted)) {

            # {{{ ConfigServiceProvider

            $this->register(
                new ConfigServiceProvider(
                    $this->getConfigurationPath(),
                    array(
                        'root_dir' => $this->getRelativePath(),
                    )
                ),
                /* $values = */ array(),
                /* $singleton = */ true
            );

            # }}}

            # {{{ ServiceControllerServiceProvider

            $this->register(
                new ServiceControllerServiceProvider(),
                /* $values = */ array(),
                /* $singleton = */ true
            );

            # }}}

            # {{{ RouteControllerFactoryProvider

            $this->register(
                new RouteControllerFactoryProvider(),
                /* $values = */ array(),
                /* $singleton = */ true
            );

            # }}}

            # {{{ RoutingProvider

            $this->register(
                new RoutingProvider($this->getConfigurationPath()),
                /* $values = */ array(),
                /* $singleton = */ true
            );

            # }}}

            # {{{ MonologServiceProvider

            # Register the log handling facility.
            $this->register(
                new MonologServiceProvider(),
                /* $values = */ array(),
                /* $singleton = */ true
            );

            # }}}
        }
        return parent::boot();
    }

    /**
     * Get the path to the configuration files for this application.
     *
     * @return string
     */
    public function getConfigurationPath()
    {
        return $this->getRelativePath(self::$configurationPath);
    }

    /**
     * Get the path to the log files for this application.
     *
     * @return string
     */
    public function getLogsPath()
    {
        return $this->getRelativePath(self::$logsPath);
    }

    /**
     * Get a relative path from the application file.
     *
     * @param string $append
     * @return string
     */
    private function getRelativePath($append = null)
    {
        $reflect = new \ReflectionClass($this);
        return (pathinfo($reflect->getFileName(), PATHINFO_DIRNAME) . $append);
    }

    /**
     * Get the default path to the Monolog log file.
     *
     * @return string
     */
    public function getMonologLogfile()
    {
        return rtrim($this->getLogsPath(), '\\/')
            . DIRECTORY_SEPARATOR
            . $this['env'] . '.log';
    }

    /**
     * Registers a service provider.
     *
     * @param ServiceProviderInterface $provider A ServiceProviderInterface instance.
     * @param array $values An array of values that customizes the provider.
     * @param bool $singleton A flag indicating whether only one instance of the provider should be registered.
     *
     * @return self
     */
    public function register(ServiceProviderInterface $provider, array $values = array(), $singleton = false)
    {
        if ($singleton) {
            foreach ($this->providers as $existing) {
                if ($existing instanceof $provider) {
                    return $this;
                }
            }
        }
        return parent::register($provider, $values);
    }
}
