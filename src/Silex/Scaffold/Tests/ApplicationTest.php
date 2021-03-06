<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\Tests;

use Silex\Scaffold\Application;

final class ApplicationTest extends AbstractTestCase
{
    public function testAppName()
    {
        $this->assertEquals(
            'scaffold_app',
            $this->app->getName(),
            'expect a default app name to be set'
        );

        $this->app->setName($appName = 'my_app');
        $this->assertEquals($appName, $this->app->getName(), 'expect the app name to be changed');
    }

    public function testBoot()
    {
        $this->assertEquals(
            array(),
            $this->app->getProviders(),
            'expect no providers before application is booted'
        );

        $this->app->boot();

        $this->assertGreaterThan(
            0,
            sizeof($this->app->getProviders()),
            'expect at least one provider to be registered after application boot'
        );
    }

    public function testRegisterSingleton()
    {
        $this->assertEquals(
            array(),
            $this->app->getProviders(),
            'expect no providers before application is booted'
        );

        $provider = $this->getMockForInterface('\\Silex\\ServiceProviderInterface');

        $this->app->register($provider, array(), true);
        $this->app->register($provider, array(), true);

        $this->assertEquals(
            array($provider),
            $this->app->getProviders(),
            'expect the provider to be registered only once'
        );
    }
}
