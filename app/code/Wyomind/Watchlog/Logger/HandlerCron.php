<?php

namespace Wyomind\Watchlog\Logger;

class HandlerCron extends \Magento\Framework\Logger\Handler\Base
{
    public $fileName = '/var/log/Watchlog-cron.log';
    public $loggerType = \Monolog\Logger::NOTICE;
}
