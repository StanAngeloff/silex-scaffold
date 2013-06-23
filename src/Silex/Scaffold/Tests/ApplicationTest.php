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
        $app = $this->createApplication();
        $this->assertEquals(
            'scaffold_app',
            $app->getName(),
            'expect a default app name to be set'
        );

        $app->setName($appName = 'my_app');
        $this->assertEquals($appName, $app->getName(), 'expect the app name to be changed');
    }

    public function testRegisterSingleton()
    {
        $app = $this->createApplication();

        $this->assertEquals(
            array(),
            $app->getProviders(),
            'expect no providers before application is booted'
        );

        $provider = $this->getMockForInterface('\\Silex\\ServiceProviderInterface');

        $app->register($provider, array(), true);
        $app->register($provider, array(), true);

        $this->assertEquals(
            array($provider),
            $app->getProviders(),
            'expect the provider to be registered only once'
        );
    }
}
