<?php
/**
 * Mail Transport
 */
namespace Intesols\Office365\Model;

class Transport extends \Zend_Mail_Transport_Smtp implements \Magento\Framework\Mail\TransportInterface {
	/**
	 * @var \Magento\Framework\Mail\MessageInterface
	 */
	protected $_message;

	/**
	 * @param MessageInterface $message
	 * @param null $parameters
	 * @throws \InvalidArgumentException
	 */
	public function __construct(\Magento\Framework\Mail\MessageInterface $message) {
		if (!$message instanceof \Zend_Mail) {
			throw new \InvalidArgumentException('The message should be an instance of \Zend_Mail');
		}

		$smtpHost = 'smtp.office365.com';
		$smtpConf = [
			'auth' => 'login',
			'ssl' => 'tls',
			'port' => '587',
			'username' => 'sales@alogic.co',
			'password' => 'Alogic99!',
		];

		parent::__construct($smtpHost, $smtpConf);
		$this->_message = $message;
	}

	/**
	 * @inheritdoc
	 */
	public function getMessage() {
		return $this->_message;
	}
	/**
	 * Send a mail using this transport
	 * @return void
	 * @throws \Magento\Framework\Exception\MailException
	 */
	public function sendMessage() {
		try {
			parent::send($this->_message);
		} catch (\Exception $e) {
			throw new \Magento\Framework\Exception\MailException(new \Magento\Framework\Phrase($e->getMessage()), $e);
		}
	}
}