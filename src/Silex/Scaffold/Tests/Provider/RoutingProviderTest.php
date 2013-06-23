<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\Tests\Provider;

use Silex\Scaffold\Tests\AbstractTestCase;

use Silex\Scaffold\Provider\RoutingProvider;

final class RoutingProviderTest extends AbstractTestCase
{
    public function testGettersSetters()
    {
        $provider = $this->newRoutingProvider();

        $this->assertNull(
            $provider->getRoutingPath(),
            'expect the routing path to be empty when initialized'
        );

        $provider->setRoutingPath($path = '/tmp');
        $this->assertEquals(
            $path,
            $provider->getRoutingPath(),
            'expect routing path to be updated after calling setter'
        );
    }

    /**
     * @expectedException \Silex\Scaffold\Exception\RuntimeException
     * @expectedExceptionCode 1371973625
     */
    public function testRoutingFailsIfFileNotFound()
    {
        $this->newRoutingProvider('not-a-directory/')
            ->boot($this->createApplication());
    }

    /**
     * @expectedException \Silex\Scaffold\Exception\InvalidConfigurationException
     * @expectedExceptionCode 1371982541
     */
    public function testRoutingFailsIfRouteOptionsIsNotAnArray()
    {
        $this->newRoutingProvider('10-not_an_array/')
            ->boot($this->createApplication());
    }

    /**
     * @expectedException \Silex\Scaffold\Exception\InvalidConfigurationException
     * @expectedExceptionCode 1371982541
     */
    public function testRoutingFailsIfRouteOptionsIsMissingRequiredProperties()
    {
        $this->newRoutingProvider('20-missing_properties/')
            ->boot($this->createApplication());
    }

    private function newRoutingProvider($routingPath = null)
    {
        return new RoutingProvider(
            (isset ($routingPath) ? __DIR__ . "/../Fixtures/config/{$routingPath}" : null)
        );
    }
}
