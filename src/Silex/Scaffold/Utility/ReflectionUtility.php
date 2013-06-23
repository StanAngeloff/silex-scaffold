<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\Utility;

final class ReflectionUtility
{
    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Find the first namespace for an object.
     *
     * When using mock objects, we need to look up the inheritance tree
     * until we find a valid namespace.
     *
     * @param object $object
     * @return string
     */
    public static function getNamespace($object)
    {
        $reflect = new \ReflectionClass($object);
        while ($reflect) {
            $namespaceName = $reflect->getNamespaceName();
            if ($namespaceName) {
                return $namespaceName;
            }
            $reflect = $reflect->getParentClass();
        }
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd
}
