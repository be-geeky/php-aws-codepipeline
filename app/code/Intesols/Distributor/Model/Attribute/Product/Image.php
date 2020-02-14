<?php
/**
* 
* Do not edit or add to this file if you wish to upgrade the module to newer
* versions in the future. If you wish to customize the module for your 
* needs please contact us to https://www.milople.com/contact-us.html
* 
* @category    Ecommerce
* @package     Milople_Sizechartpopup
* @copyright   Copyright (c) 2016 Milople Technologies Pvt. Ltd. All Rights Reserved.
* @url         https://www.milople.com/magento2/size-chart-popup.html
*
**/
namespace Milople\Sizechartpopup\Model\Attribute\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
class Image extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend {	
	protected $_uploaderFactory;	
	protected $_filesystem;
	protected $_fileUploaderFactory;
	protected $_logger;
	public function __construct(\Psr\Log\LoggerInterface $logger, \Magento\Framework\Filesystem $filesystem, \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory) {
		$this -> _filesystem = $filesystem;
		$this -> _fileUploaderFactory = $fileUploaderFactory;
		$this -> _logger = $logger;
	}
	/**
	 *  This function Work as a backend Model for size chart image in product tab. 
	 **/
	public function afterSave($object) {
		$value = $object -> getData($this -> getAttribute() -> getName() . '_additional_data');

		/**
		 *  If no image was set - nothing to do 
		 **/
		if (empty($value) && empty($_FILES)) {
			return $this;
		}
		if (is_array($value) && !empty($value['delete'])) {
			$object -> setData($this -> getAttribute() -> getName(), '');
			$this -> getAttribute() -> getEntity() -> saveAttribute($object, $this -> getAttribute() -> getName());
			return $this;
		}
		$path = $this -> _filesystem -> getDirectoryRead(DirectoryList::MEDIA) -> getAbsolutePath('catalog/product/');
		try {			
			$uploader = $this -> _fileUploaderFactory -> create(['fileId' => $this -> getAttribute() -> getName()]);
			$uploader -> setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png','svg']);
			$uploader -> setAllowRenameFiles(true);
			$result = $uploader -> save($path);

			$object -> setData($this -> getAttribute() -> getName(), $result['file']);
			$this -> getAttribute() -> getEntity() -> saveAttribute($object, $this -> getAttribute() -> getName());
		} catch (\Exception $e) {
			if ($e -> getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
				$this -> _logger -> critical($e);
			}
		}
		return $this;
	}
}