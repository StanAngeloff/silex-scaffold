<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\Tests;

abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Create a \Silex\Scaffold\Application object for testing.
     *
     * @param mixed ... The Application constructor arguments.
     * @return \Silex\Scaffold\Application
     *
     * @see http://silex.sensiolabs.org/doc/testing.html
     */
    protected function createApplication()/* ...$arguments */
    {
        $reflect = new \ReflectionClass('\\Silex\\Scaffold\\Application');
        $app = $reflect->newInstanceArgs(func_get_args());

        $app['debug'] = true;
        $app['exception_handler']->disable();

        return $app;
    }
}
