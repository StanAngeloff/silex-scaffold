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
    const PROPERTY_APP_NAME = 'app_name';

    /**
     * The default application name.
     *
     * @var string
     */
    const DEFAULT_APP_NAME = 'scaffold_app';

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
        if (isset ($env)) {
            $values = $values + compact('env');
        }
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
        if (isset ($this[self::PROPERTY_APP_NAME])) {
            return $this[self::PROPERTY_APP_NAME];
        }
        return self::DEFAULT_APP_NAME;
    }

    /**
     * Set the application name.
     *
     * @param string $appName
     */
    public function setName($appName)
    {
        $this[self::PROPERTY_APP_NAME] = $appName;
    }

    # }}}
}
