<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\DependencyInjection;

use Silex\Scaffold\Exception\InjectorException;
use Silex\Scaffold\Exception\InvalidArgumentException;

use Symfony\Component\PropertyAccess\PropertyAccess;

class Injector
{
    /**
     * The context of the Injector.
     *
     * @var array|\ArrayAccess
     */
    private $context;

    /**
     * A cached instance of a PropertyAccessor.
     *
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $accessor;

    /**
     * Initialize a new instance of Injector.
     *
     * @param array|\ArrayAccess $context The context of the Injector.
     */
    public function __construct($context = null)
    {
        if (isset ($context)) {
            $this->setContext($context);
        }
    }

    /**
     * Create a new instance of an Injector.
     *
     * @param array|\ArrayAccess $context The context of the Injector.
     * @return static
     */
    public static function create($context = null)
    {
        return new static($context);
    }

    /**
     * Create a new instance of the specified class.
     *
     * @param array $arguments The arguments to pass to the constructor.
     * @param array $calls The calls to invoke on the new instance.
     * @param array $properties The properties to set on the new instance.
     *
     * @return mixed
     *
     * @throws InjectorException
     */
    public function createInstance(
        $klass,
        array $arguments = null,
        array $calls = null,
        array $properties = null
    ) {
        if (strpos($klass, '\\') !== 0) {
            $klass = '\\' . $klass;
        }
        if (( ! class_exists($klass, /* $autoload = */ true))) {
            throw new InvalidArgumentException(
                strtr(
                    'Cannot create instance of non-existing class "{class}".',
                    array(
                        '{class}' => $klass,
                    )
                ),
                1371990450
            );
        }

        $instance = null;
        try {
            $targets = array(&$arguments, &$calls, &$properties);
            foreach ($targets as &$target) {
                $target = (array) $target;
                $this->replaceParameters($target);
                $this->replaceServices($target);
            }

            $reflect = new \ReflectionClass($klass);
            $instance = $reflect->newInstanceArgs($arguments);

            foreach ($calls as $call) {
                $this->addMethodCall($reflect, $instance, (array) $call);
            }
        } catch (\Exception $previous) {
            throw new InjectorException(
                strtr(
                    'Cannot create an instance of class "{class}".',
                    array(
                        '{class}' => $klass,
                    )
                ),
                1371994399,
                $previous
            );
        }

        return $instance;
    }

    /**
     * Invoke the given method on the specified instance.
     *
     * @param \ReflectionClass $reflect
     * @param object $instance
     * @param array $call
     */
    private function addMethodCall(\ReflectionClass $reflect, $instance, array $options)
    {
        reset($options);
        $method = current($options);
        $arguments = (array) next($options);
        if (( ! ($reflect->hasMethod($method)
              || $reflect->hasMethod('__call')))) {
            throw new InvalidArgumentException(
                strtr(
                    'The method "{method}" does not exist.',
                    array(
                        '{method}' => $method,
                    )
                ),
                1371995679
            );
        }
        call_user_func_array(array($instance, $method), $arguments);
    }

    # {{{ Getters/Setters

    /**
     * Get the context of the Injector.
     *
     * @return array|\ArrayAccess
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set the context of the Injector.
     *
     * @param array|\ArrayAccess $context
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function setContext($context)
    {
        if (( ! (is_array($context) || $context instanceof \ArrayAccess))) {
            throw new InvalidArgumentException(
                strtr(
                    'Cannot set Injector context, expected "array|\\ArrayAccess" got "{actual}" instead.',
                    array(
                        '{actual}' => (is_object($context) ? get_class($context) : gettype($context)),
                    )
                ),
                1371990005
            );
        }
        $this->context = $context;
    }

    # }}}

    /**
     * Get an instance of a PropertyAccessor.
     *
     * @return \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private function getPropertyAccessor()
    {
        if ($this->accessor === null) {
            $this->accessor = PropertyAccess::createPropertyAccessor();
        }
        return $this->accessor;
    }

    private function replaceParameters(array &$target)
    {
        array_walk_recursive(
            $target,
            function (&$value) {
                if (is_string($value)
                    && strpos($value, '%') === 0
                    && strrpos($value, '%') === strlen($value) - 1
                ) {
                    $value = $this->getParameterValue($value);
                }
            }
        );
    }

    private function replaceServices(array &$target)
    {
        array_walk_recursive(
            $target,
            function (&$value) {
                if (is_string($value)
                    && strpos($value, '@') === 0
                ) {
                    $value = $this->getServiceValue($value);
                }
            }
        );
    }

    private function getParameterValue($parameter)
    {
        $accessor = $this->getPropertyAccessor();
        $path = ('[' . trim($parameter, '%') . ']');
        $value = $accessor->getValue($this->context, $path);
        if ($value === null) {
            $value = $accessor->getValue(
                $this->context,
                str_replace('.', '][', $path)
            );
        }
        return $value;
    }

    private function getServiceValue($service)
    {
        $path = ('[' . ltrim($service, '@') . ']');
        $value = $this->getPropertyAccessor()->getValue($this->context, $path);
        if ($value === null) {
            throw new InvalidArgumentException(
                strtr(
                    'The service "{service}" cannot be found in the context.',
                    array(
                        '{service}' => $service,
                    )
                ),
                1371995009
            );
        }
        return $value;
    }
}
