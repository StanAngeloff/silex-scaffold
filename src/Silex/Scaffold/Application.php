<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold;

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
     * The default application name when one was not provided by the User.
     *
     * @var string
     */
    const DEFAULT_APP_NAME = 'scaffold_app';

    /** {@inheritDoc} */
    public function __construct(array $values = array(), $environment = null)
    {
        parent::__construct($values);
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
