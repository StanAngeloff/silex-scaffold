<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\Tests\Fixtures;

class Injectee
{
    /**
     * The constructor arguments.
     *
     * @var array
     */
    private $arguments;

    /**
     * The calls made on the class.
     *
     * @var array
     */
    private $calls;

    /**
     * The properties set on the class.
     *
     * @var array
     */
    private $properties;

    public function __construct()
    {
        $this->arguments = func_get_args();
        $this->calls = array();
        $this->properties = array();
    }

    /**
     * Get the constructor arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Get the calls made on the class.
     *
     * @return array
     */
    public function getCalls()
    {
        return $this->calls;
    }

    /**
     * Get the properties set on the class.
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /** {@inheritDoc} */
    public function __call($name, array $arguments)
    {
        $this->calls[] = array($name, $arguments);
    }

    /** {@inheritDoc} */
    public function __set($name, $value)
    {
        $this->properties[$name] = $value;
    }
}
