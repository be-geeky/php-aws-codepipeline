<?php

/*
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Watchlog\Model;

class Auth
{

    protected $_datetime = null;
    protected $_request = null;
    protected $_attemptsFactory = null;
    protected $_coreHelper = null;
    protected $_watchlogHelper = null;
    protected $_auth = null;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\HTTP\PhpEnvironment\Request $request,
        \Wyomind\Watchlog\Model\AttemptsFactory $attemptsFactory,
        \Wyomind\Core\Helper\Data $coreHelper,
        \Wyomind\Watchlog\Helper\Data $watchlogHelper
    ) {
        $this->_datetime = $datetime;
        $this->_request = $request;
        $this->_attemptsFactory = $attemptsFactory;
        $this->_coreHelper = $coreHelper;
        $this->_watchlogHelper = $watchlogHelper;
    }
    
    public function throwException($ex)
    {
        $this->_auth->throwException($ex);
    }

    public function aroundLogin(
        \Magento\Backend\Model\Auth $auth,
        \Closure $closure,
        $login,
        $password
    ) {
        $this->_auth = $auth;
        $exception = null;
        try {
            $closure($login, $password);
        } catch (\Magento\Backend\Model\Auth\PluginAuthenticationException $e) {
            $exception = $e;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $exception = $e;
        } catch (\Magento\Backend\Model\Auth\AuthenticationException $e) {
            $exception = $e;
        }

        $this->addAttempt($login, $password, $exception);
        if ($exception != null) {
            throw $exception;
        }
        
        return null;
    }

    public function addAttempt(
        $login,
        $password,
        $e = null
    ) {
        $data = [
            "login" => $login,
            "password" => $password,
            "ip" => strtok($this->_request->getClientIp(), ','),
            "date" => $this->_datetime->gmtDate('Y-m-d H:i:s'),
            "status" => \Wyomind\Watchlog\Helper\Data::SUCCESS,
            "message" => "",
            "url" => $this->_request->getRequestUri()
        ];

        if ($e != null) { // failed
            $data['password'] = $password;
            $data['status'] = \Wyomind\Watchlog\Helper\Data::FAILURE;
            $data['message'] = $e->getMessage();
        } else { // success
            $data['password'] = "***";
            $this->_watchlogHelper->checkNotification();
        }

        $attempt = $this->_attemptsFactory->create()->load(0);
        $attempt->setData($data);
        $attempt->save();
    }
}
