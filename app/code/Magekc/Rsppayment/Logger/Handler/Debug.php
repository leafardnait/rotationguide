<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @category Magekc
 * @package  Magekc_Rsppayment
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)  
 *
 * @author   Kristian Claridad<kristianrafael.claridad@gmail.com>
 */
namespace Magekc\Rsppayment\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Debug class
 */
class Debug extends Base
{
    /** @var */
    protected static $timezone;

    /** @var string */
    protected $name = 'Magekc_Rsppayment';

    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * File name
     *
     * @var string
     */
    protected $fileName = '/var/log/rsppayment.log';


    public function customLog($message, array $context = array())
    {
        // check if any handler will handle this message so we can return early and save cycles
        $handlerKey = null;
        $level = $this->loggerType;

        if (!self::$timezone) {
            self::$timezone = new \DateTimeZone(date_default_timezone_get() ?: 'UTC');
        }

        $record = array(
            'message'    => (string)$message,
            'context'    => $context,
            'level'      => $level,
            'level_name' => 'DEBUG',
            'channel'    => $this->name,
            'datetime'   => \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)),
                static::$timezone)->setTimezone(static::$timezone),
            'extra'      => array(),
        );

        $this->handle($record);
    }
}
