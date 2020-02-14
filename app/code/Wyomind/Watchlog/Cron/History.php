<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Watchlog\Cron;

class History
{

    protected $_watchlogHelper;
    protected $_datetime;
    protected $_attemptsCollectionFactory;
    protected $_logger;
    protected $_transportBuilder;

    public function __construct(
        \Wyomind\Watchlog\Helper\Data $watchlogHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Wyomind\Watchlog\Model\ResourceModel\Attempts\CollectionFactory $attemptsCollectionFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Wyomind\Watchlog\Logger\LoggerCron $logger
    ) {
        $this->_watchlogHelper = $watchlogHelper;
        $this->_datetime = $datetime;
        $this->_attemptsCollectionFactory = $attemptsCollectionFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_logger = $logger;
    }

    public function purge(\Magento\Cron\Model\Schedule $schedule = null)
    {
        try {
            $timestamp = $this->_datetime->gmtTimestamp();
            $histolength = $this->_watchlogHelper->getDefaultConfig("watchlog/settings/history");
            $deleteBefore = $timestamp - $histolength * 60 * 60 * 24;

            if ($histolength != 0) {
                $this->_logger->notice("-------------------- PURGE PROCESS --------------------");
                $this->_logger->notice("-- current date : " . $this->_datetime->gmtDate('Y-m-d H:i:s', $timestamp));
                $this->_logger->notice("-- deleting row before : " . $this->_datetime->gmtDate('Y-m-d H:i:s', $deleteBefore));
                $nbDeleted = $this->_attemptsCollectionFactory->create()->purge($deleteBefore);
                $this->_logger->notice("-- $nbDeleted rows deleted");
            }
        } catch (\Exception $e) {
            if ($schedule) {
                $schedule->setStatus('failed');
                $schedule->setMessage($e->getMessage());
                $schedule->save();
            }
            $this->_logger->notice("MASSIVE ERROR ! ");
            $this->_logger->notice($e->getMessage());
        }
    }
}
