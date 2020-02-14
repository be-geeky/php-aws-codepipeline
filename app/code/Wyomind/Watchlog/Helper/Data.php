<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Watchlog\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const SUCCESS = 1;
    const FAILURE = 0;
    
    protected $_datetime;
    protected $_coreHelper;
    protected $_messageManager;
    protected $_attemptsModelFactory;
    protected $_backendHelper;
    protected $_inboxFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Wyomind\Core\Helper\Data $coreHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Wyomind\Watchlog\Model\AttemptsFactory $attemptsModelFactory,
        \Magento\AdminNotification\Model\InboxFactory $inboxFactory,
        \Magento\Backend\Helper\Data $backendHelper
    ) {
        parent::__construct($context);
        $this->_datetime = $datetime;
        $this->_coreHelper = $coreHelper;
        $this->_messageManager = $messageManager;
        $this->_attemptsModelFactory = $attemptsModelFactory;
        $this->_backendHelper = $backendHelper;
        $this->_inboxFactory = $inboxFactory;
    }
    
    public function getDefaultConfig($key)
    {
        return $this->_coreHelper->getDefaultConfig($key);
    }
    public function setDefaultConfig($key, $value)
    {
        $this->_coreHelper->setDefaultConfig($key, $value);
    }

    public function checkWarning()
    {
        $failedLimit = $this->getDefaultConfig("watchlog/settings/failed_limit");
        $percent = $this->_attemptsModelFactory->create()->getFailedPercentFromDate();
        $notificationDetails = $this->getDefaultConfig("watchlog/settings/notification_details");
        if ($percent > $failedLimit) {
            $this->_messageManager->addError(sprintf(__($notificationDetails), number_format($percent * 100, 2, ".", "")));
        }
    }

    public function checkNotification()
    {
        $lastNotification = $this->getDefaultConfig("watchlog/settings/last_notification");
        $failedLimit = $this->getDefaultConfig("watchlog/settings/failed_limit");


        $percent = $this->_attemptsModelFactory->create()->getCollection()->getFailedPercentFromDate($lastNotification);

        if ($percent > $failedLimit) {
            // add notif in inbox
            $notificationTitle = $this->getDefaultConfig("watchlog/settings/notification_title");
            $notificationDescription = $this->getDefaultConfig("watchlog/settings/notification_description");
            $notificationLink = $this->_backendHelper->getUrl("/watchlog/basic/index");

            $date = $this->_datetime->gmtDate('Y-m-d H:i:s');

            $notify = $this->_inboxFactory->create();
            $item = $notify->getCollection()->addFieldToFilter('title', ["eq" => "Watchlog security warning"])->addFieldToFilter('is_remove', ["eq" => 0]);
            $data = $item->getLastItem()->getData();

            if (isset($data["notification_id"])) {
                $notify->load($data["notification_id"]);
                $notify->setUrl($notificationLink);
                $notify->setDescription(sprintf(__($notificationDescription), number_format($percent * 100, 2, ".", ""), $notificationLink));
                $notify->setData('is_read', 0)->save();
            } else {
                $notify->setTitle(__($notificationTitle));
                $notify->setUrl($notificationLink);
                $notify->setDescription(sprintf(__($notificationDescription), number_format($percent * 100, 2, ".", ""), $notificationLink));
                $notify->setSeverity(1);
                $notify->save();
            }
            $this->setDefaultConfig("watchlog/settings/last_notification", $date);
        }
    }
}
