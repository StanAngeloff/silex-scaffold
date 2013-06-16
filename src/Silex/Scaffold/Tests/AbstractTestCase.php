<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\Tests;

use Silex\Scaffold\Application;

abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Create a \Silex\Scaffold\Application object for testing.
     *
     * @return \Silex\Scaffold\Application
     *
     * @see http://silex.sensiolabs.org/doc/testing.html
     */
    protected function createApplication()
    {
        $app = new Application();

        $app['debug'] = true;
        $app['exception_handler']->disable();

        return $app;
    }
}
