<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\Tests\Provider;

use Silex\Scaffold\Tests\AbstractTestCase;

use Silex\Scaffold\Provider\RouteControllerFactoryProvider;

final class RouteControllerFactoryProviderTest extends AbstractTestCase
{
    /**
     * @expectedException \Silex\Scaffold\Exception\InvalidArgumentException
     * @expectedExceptionCode 1371973949
     */
    public function testRoutingFailsIfRouteOptionsIsNotAnArray()
    {
        $this->newRouteControllerFactoryProvider()
            ->createController(
                $this->createApplication(),
                'not an array'
            );
    }

    /**
     * @expectedException \Silex\Scaffold\Exception\InvalidArgumentException
     * @expectedExceptionMessage [defaults][_controller]
     * @expectedExceptionCode 1371982396
     */
    public function testRoutingFailsIfRouteOptionsIsMissingRequiredProperties()
    {
        $this->newRouteControllerFactoryProvider()
            ->createController(
                $this->createApplication(),
                array(
                    'pattern' => '/',
                    'defaults' => array(),
                )
            );
    }

    /**
     * @expectedException \Silex\Scaffold\Exception\InvalidArgumentException
     * @expectedExceptionCode 1371983353
     */
    public function testRoutingFailsIfRouteControllerIsBroken()
    {
        $this->newRouteControllerFactoryProvider()
            ->createController(
                $this->createApplication(),
                array(
                    'pattern' => '/',
                    'defaults' => array(
                        '_controller' => 'brokenResource',
                    ),
                )
            );
    }

    public function testRouteControllerFactoryCreatesController()
    {
        $provider = $this->newRouteControllerFactoryProvider();
        $provider->createController(
            $this->app,
            array(
                'pattern' => '/',
                'defaults' => array(
                    '_controller' => 'Index:index',
                    '_method' => 'GET',
                )
            )
        );
        $provider->createController(
            $this->app,
            array(
                'pattern' => '/post',
                'methods' => array('POST'),
                'defaults' => array(
                    '_controller' => 'Index:index',
                )
            )
        );
        $this->app->register($provider);
        $this->app->register($this->getMock('\\Silex\\Scaffold\\Provider\\RoutingProvider'));

        $client = $this->createClient();

        $client->request('GET', '/');
        $this->assertTrue(
            $client->getResponse()->isOk(),
            'expect routing factory to create controller to handle GET request'
        );

        $client->request('POST', '/post');
        $this->assertTrue(
            $client->getResponse()->isOk(),
            'expect routing factory to create controller to handle POST request'
        );
    }

    private function newRouteControllerFactoryProvider()
    {
        return new RouteControllerFactoryProvider();
    }
}
