<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

use Examples\HelloWorld\Application;

# Load the Composer autoload file.
$loader = require __DIR__ . '/../vendor/autoload.php';

$app = new Application();

# Run the Silex application.
$app->run();
