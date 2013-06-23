<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold;

use Silex\Scaffold\Application\Environment;

use Silex\Application as BaseApplication;
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
    /**
     * Registers a service provider.
     *
     * @param ServiceProviderInterface $provider A ServiceProviderInterface instance.
     * @param array $values An array of values that customizes the provider.
     * @param bool $singleton A flag indicating whether only one instance of the provider should be registered.
     *
     * @return Application
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
