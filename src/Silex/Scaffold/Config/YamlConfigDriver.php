<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Silex\Scaffold\Config;

use Silex\Scaffold\Exception\RuntimeException;

use Igorw\Silex\YamlConfigDriver as BaseYamlConfigDriver;

class YamlConfigDriver extends BaseYamlConfigDriver
{
    /**
     * The replacements to apply during importing.
     *
     * @var array
     */
    private $replacements;

    /**
     * Initialize a new YamlConfigDriver instance.
     *
     * @param array $replacements
     */
    public function __construct(array $replacements = array())
    {
        $this->replacements = array();
        foreach ($replacements as $key => $value) {
            $this->replacements["%{$key}%"] = $value;
        }
    }

    /** {@inheritDoc} */
    public function load($filename)
    {
        $config = parent::load($filename);
        $config = $this->loadImports($config, $filename);

        return ($config ?: array());
    }

    /**
     * Process 'imports' directives.
     *
     * @param array $config
     * @param string $filename
     *
     * @return array
     */
    private function loadImports($config, $filename)
    {
        if (isset ($config['imports'])) {
            $path = pathinfo($filename, PATHINFO_DIRNAME);
            $imports = (array) $config['imports'];
            unset ($config['imports']);

            $merged = array();
            foreach ($imports as $import) {
                $file = ($path . '/' . ltrim($import['resource'], '\\/'));
                $file = strtr($file, $this->replacements);
                if (( ! file_exists($file))) {
                    throw new RuntimeException(
                        strtr(
                            'The config file "{file}" does not exist.',
                            array(
                                '{file}' => $file,
                            )
                        ),
                        1371999104
                    );
                }
                $imported = $this->load($file);
                $merged = array_merge($merged, $imported);
            }
            $config = array_merge($merged, $config);
        }
        return $config;
    }
}
