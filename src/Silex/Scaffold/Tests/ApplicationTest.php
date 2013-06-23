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
}
