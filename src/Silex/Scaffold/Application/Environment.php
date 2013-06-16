<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\Application;

use Silex\Scaffold\Application;

/**
 * The Environment class dynamically loads the environment name.
 */
final class Environment
{
    const ENV_PRODUCTION = 'prod';
    const ENV_DEVELOPMENT = 'dev';
    const ENV_TESTING = 'test';

    /**
     * The environment key template used to look up a customised value.
     *
     * @var string
     */
    private static $envKeyTemplate = '{app_name}_ENV';

    /**
     * A list of development domains that would always trigger the 'dev' environment.
     *
     * @var string[]
     */
    private static $devDomains = array(
        'localhost',
        'xip.io',
    );

    /**
     * The default environment.
     *
     * @var string
     */
    private $default;

    /**
     * Initialize a new Environment instance.
     *
     * @param string $default
     */
    public function __construct($default = null)
    {
        if (isset ($default)) {
            $this->setDefault($default);
        }
    }

    /**
     * Get the environment name for the given container.
     *
     * @param Application $app
     * @return string
     */
    public function __invoke(Application $app)
    {
        $envKey = strtr(
            self::$envKeyTemplate,
            array('{app_name}' => strtoupper($app->getName()))
        );
        # Look for CLI environment options.
        $envValue = getenv($envKey);
        if ($envValue !== false && strlen($envValue)) {
            return $envValue;
        }
        # Look for Nginx or Apache (+mod_rewrite) options.
        foreach (array($envKey, "REDIRECT_{$envKey}") as $tryKey) {
            if (isset ($_SERVER[$tryKey])) {
                return $_SERVER[$tryKey];
            }
        }
        # Are we in development mode?
        if (isset ($_SERVER['HTTP_HOST'])) {
            foreach (self::$devDomains as $tryDomain) {
                if (strstr($_SERVER['HTTP_HOST'], $tryDomain)) {
                    return self::ENV_DEVELOPMENT;
                }
            }
        }
        return $this->default;
    }

    # {{{

    /**
     * Get the default environment.
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Set the default environment.
     *
     * @param string $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    # }}}
}
