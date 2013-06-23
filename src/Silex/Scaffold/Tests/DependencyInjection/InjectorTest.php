<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\Tests\DependencyInjection;

use Silex\Scaffold\DependencyInjection\Injector;

final class InjectorTest extends \PHPUnit_Framework_TestCase
{
    private static $injecteeClass = '\\Silex\\Scaffold\\Tests\\Fixtures\\Injectee';

    /**
     * @expectedException \Silex\Scaffold\Exception\InvalidArgumentException
     * @expectedExceptionMessage got "string"
     * @expectedExceptionCode 1371990005
     */
    public function testContextFailsWhenNotAnArrayOrArrayAccess()
    {
        $this->newInjector('string');
    }

    public function testGettersSetters()
    {
        $context = new \ArrayObject(array('key' => 'value'));
        $injector = $this->newInjector();

        $this->assertNull(
            $injector->getContext(),
            'expect context to be null when initialized'
        );

        $injector->setContext($context);
        $this->assertSame(
            $context,
            $injector->getContext(),
            'expect context to be set after call to setter'
        );
    }

    /**
     * @expectedException \Silex\Scaffold\Exception\InvalidArgumentException
     * @expectedExceptionMessage class "\A\B\C"
     * @expectedExceptionCode 1371990450
     */
    public function testInjectorFailsWithNonExistingClasses()
    {
        $this->newInjector()->createInstance('A\\B\\C');
    }

    public function testInjectorCreatesInstanceWithNoArguments()
    {
        $instance = $this->newInjectorInstance();
        $this->assertInstanceOf(
            self::$injecteeClass,
            $instance,
            'expect to create instance of specified class'
        );
    }

    public function testInjectorCreatesInstanceWithArguments()
    {
        $instance = $this->newInjectorInstance(null, $arguments = array(1, 'string', true));
        $this->assertEquals(
            $arguments,
            $instance->getArguments(),
            'expect Injector to pass arguments to constructor'
        );
    }

    public function testInjectorExtractsParametersFromContext()
    {
        $context = array('key' => $value = 'value');
        $instance = $this->newInjectorInstance($context, array('%key%'));
        $this->assertEquals(
            array($value),
            $instance->getArguments(),
            'expect Injector to extract parameters and replace in arguments to constructor'
        );

        $context = array('key1' => array('key2' => array('key3' => $value = 'value3')));
        $instance = $this->newInjectorInstance($context, array('%key1.key2.key3%'));
        $this->assertEquals(
            array($value),
            $instance->getArguments(),
            'expect Injector to extract parameters recursively'
        );

        $context = array('key.key1' => $value = 'value1');
        $instance = $this->newInjectorInstance($context, array('%key.key1%'));
        $this->assertEquals(
            array($value),
            $instance->getArguments(),
            'expect Injector to extract literal parameters'
        );

        $context = array('keyA.keyB' => $value = 'valueB');
        $instance = $this->newInjectorInstance($context, array(array('key' => '%keyA.keyB%')));
        $this->assertEquals(
            array(array('key' => $value)),
            $instance->getArguments(),
            'expect Injector to inject parameters recursively'
        );
    }

    public function testInjectorSkipsParametersWhichDoNotExist()
    {
        $context = array();
        $instance = $this->newInjectorInstance($context, array('%key%'));
        $this->assertEquals(
            array(null),
            $instance->getArguments(),
            'expect Injector to skip missing parameters'
        );
    }

    public function testInjectorExtractsServicesFromContext()
    {
        $context = array('service' => $service = new \stdClass());
        $instance = $this->newInjectorInstance($context, array('@service'));
        $this->assertSame(
            array($service),
            $instance->getArguments(),
            'expect Injector to extract services and replace in arguments to constructor'
        );
    }

    /**
     * @expectedException \Silex\Scaffold\Exception\InjectorException
     * @expectedExceptionCode 1371994399
     */
    public function testInjectorFailsIfServicesDoNotExist()
    {
        $this->newInjectorInstance(array(), array('@service'));
    }

    public function testInjectorCallsMethods()
    {
        $instance = $this->newInjectorInstance(
            /* $context = */ null,
            /* $arguments = */ null,
            /* $calls = */ array(
                $value1 = array('setKey1', array('value1')),
                $value2 = array('setKey2', array('value2'))
            )
        );

        $this->assertEquals(
            array($value1, $value2),
            $instance->getCalls(),
            'expect Injector to call methods'
        );
    }

    /**
     * @expectedException \Silex\Scaffold\Exception\InjectorException
     * @expectedExceptionCode 1371994399
     */
    public function testInjectorFailsWhenMethodDoesNotExist()
    {
        $this->newInjector()->createInstance(
            get_class($this),
            /* $arguments = */ null,
            /* $calls = */ array('nonExistingMethod')
        );
    }

    private function newInjector($context = null)
    {
        return Injector::create($context);
    }

    private function newInjectorInstance($context = null, array $arguments = null, array $calls = null)
    {
        return $this->newInjector($context)
            ->createInstance(
                self::$injecteeClass,
                $arguments,
                $calls
            );
    }
}
