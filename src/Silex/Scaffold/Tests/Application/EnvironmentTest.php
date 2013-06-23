<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\Tests\Application;

use Silex\Scaffold\Tests\AbstractTestCase;

use Silex\Scaffold\Application\Environment;

final class EnvironmentTest extends AbstractTestCase
{
    public function testEnv()
    {
        putenv('SCAFFOLD_APP_ENV=');
        unset ($_SERVER['SCAFFOLD_APP_ENV']);
        unset ($_SERVER['REDIRECT_SCAFFOLD_APP_ENV']);
        $_SERVER['HTTP_HOST'] = 'scaffold.app';

        $this->assertEquals(
            Environment::ENV_PRODUCTION,
            $this->app['env'],
            'expect the default environment to be production'
        );

        putenv('SCAFFOLD_APP_ENV=' . ($env = Environment::ENV_TESTING));
        $this->assertEquals(
            $env,
            $this->app['env'],
            'expect the environment to be loaded from $_ENV'
        );
        putenv('SCAFFOLD_APP_ENV=');

        $_SERVER['SCAFFOLD_APP_ENV'] = $env;
        $this->assertEquals(
            $env,
            $this->app['env'],
            'expect the environment to be loaded from $_SERVER'
        );
        unset ($_SERVER['SCAFFOLD_APP_ENV']);

        $_SERVER['REDIRECT_SCAFFOLD_APP_ENV'] = $env;
        $this->assertEquals(
            $env,
            $this->app['env'],
            'expect the environment to be loaded from $_SERVER when mod_rewrite is activated'
        );
        unset ($_SERVER['REDIRECT_SCAFFOLD_APP_ENV']);

        $_SERVER['HTTP_HOST'] = 'hello.xip.io';
        $this->assertEquals(
            Environment::ENV_DEVELOPMENT,
            $this->app['env'],
            'expect the fallback environment on a xip.io domain to be development'
        );
    }

    public function testEnvironmentFromConstructor()
    {
        $env = Environment::ENV_TESTING;
        $app = $this->createApplication(null, array(array(), $env));
        $this->assertEquals(
            $env,
            $app['env'],
            'expect the environment to be set from the __constructor'
        );
    }
}
