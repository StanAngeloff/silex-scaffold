<?php
/**
 * (c) PSP UK Group Ltd. <hello@psp-group.co.uk>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Examples\HelloWorld\Controller;

use Psr\Log\LoggerInterface;

class HelloWorldController
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Initialize a new HelloWorldController instance.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }

    public function indexAction()
    {
        $this->logger->debug(
            __CLASS__ . ': {function} called',
            array('{function}' => __FUNCTION__)
        );

        return 'Hello World!';
    }

    # {{{ Getters/Setters

    /**
     * Get the LoggerInterface associated with this instance.
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Set the LoggerInterface associated with this instance.
     *
     * @param LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    # }}}
}
