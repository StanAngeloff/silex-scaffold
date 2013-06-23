<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\Tests;

use Silex\WebTestCase;

abstract class AbstractTestCase extends WebTestCase
{
    /**
     * Create a \Silex\Scaffold\Application object for testing.
     *
     * @param array $methods The method names to mock.
     * @param array $arguments The arguments to the Application constructor.
     * @return \Silex\Scaffold\Application
     *
     * @see http://silex.sensiolabs.org/doc/testing.html
     */
    public function createApplication(array $methods = null, array $arguments = null)
    {
        $app = $this->getMock(
            '\\Silex\\Scaffold\\Application',
            $methods,
            (array) $arguments
        );

        $app['debug'] = true;
        $app['exception_handler']->disable();

        return $app;
    }

    /**
     * Get a mock object for an interface.
     *
     * @param string $interfaceName
     * @return object
     */
    protected function getMockForInterface($interfaceName)
    {
        $reflect = new \ReflectionClass($interfaceName);
        $instance = $this->getMockBuilder($reflect->getName())
            ->setMethods(
                array_map(
                    function (\ReflectionMethod $method) {
                        return $method->getName();
                    },
                    $reflect->getMethods()
                )
            );
        return $instance->getMock();
    }
}
