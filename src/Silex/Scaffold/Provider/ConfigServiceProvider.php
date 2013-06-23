<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\Provider;

use Silex\Scaffold\Application as ScaffoldApplication;
use Silex\Scaffold\Config\YamlConfigDriver;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Igorw\Silex\ConfigServiceProvider as BaseConfigServiceProvider;

class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * The name of the configuration file.
     *
     * @var string
     */
    private static $configFilename = 'config.yml';

    /**
     * The absolute path to the configuration directory.
     *
     * @var string
     */
    private $configPath;

    /**
     * The configuration replacements.
     *
     * @var array
     */
    private $replacements;

    /**
     * Initialize a new ConfigServiceProvider instance.
     *
     * @param string $configPath The absolute path to the configuration directory.
     * @param array $replacements
     */
    public function __construct($configPath = null, array $replacements = array())
    {
        if (isset ($configPath)) {
            $this->setConfigPath($configPath);
        }
        $this->setReplacements($replacements);
    }

    # {{{ Getters/Setters

    /**
     * Get the absolute path to the configuration directory.
     *
     * @return string
     */
    public function getConfigPath()
    {
        return $this->configPath;
    }

    /**
     * Set the absolute path to the configuration directory.
     *
     * @param string $configPath
     * @return void
     */
    public function setConfigPath($configPath)
    {
        $this->configPath = $configPath;
    }

    /**
     * Get the configuration replacements.
     *
     * @return array
     */
    public function getReplacements()
    {
        return $this->replacements;
    }

    /**
     * Set the configuration replacements.
     *
     * @param array $replacements
     * @return void
     */
    public function setReplacements(array $replacements)
    {
        $this->replacements = $replacements;
    }

    # }}}

    # {{{ ServiceProviderInterface

    /** {@inheritDoc} */
    public function register(Application $app)
    {
        $configFile = rtrim($this->configPath, '\\/')
            . DIRECTORY_SEPARATOR
            . self::$configFilename;
        $replacements = $this->replacements + array(
            'name' => ($app instanceof ScaffoldApplication ? $app->getName() : null),
            'env' => (isset ($app['env']) ? (string) $app['env'] : null),
        );
        $service = new BaseConfigServiceProvider(
            $configFile,
            $replacements,
            new YamlConfigDriver($replacements)
        );
        $service->register($app);
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        $app; // unused
    }

    # }}}
}
