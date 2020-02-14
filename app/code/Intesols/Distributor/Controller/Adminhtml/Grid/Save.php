<?php

namespace Intesols\Distributor\Controller\Adminhtml\Grid;
use Magento\Framework\App\Filesystem\DirectoryList;
class Save extends \Magento\Backend\App\Action
{	
    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
	/**
	* @var \Magento\Framework\Image\AdapterFactory
	*/
	protected $_adapterFactory;
	/**
	* @var \Magento\MediaStorage\Model\File\UploaderFactory
	*/
	protected $_uploaderFactory;
	/**
	* @var \Magento\Framework\Filesystem
	*/
	protected $filesystem;
	/**
	* @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
	*/	
	protected $timezoneInterface;
	protected $_dateFactory;	
	public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploader,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
    )
	{
		$this->_adapterFactory = $adapterFactory;
		$this->_uploaderFactory = $uploader;
		$this->filesystem = $filesystem;
		$this->_dateFactory = $dateFactory;
		parent::__construct($context);
	}
    public function execute()
    {		
        $data = $this->getRequest()->getPostValue();
		$imageRequest = $this->getRequest()->getFiles('logo');		
		$data['country'] = implode(',', $data['country']);	
		//start block upload image
		
		if ($imageRequest) {
			if (isset($imageRequest['name'])) {
				$fileName = $imageRequest['name'];
			} else {
				$fileName = '';
			}
		} else {
			$fileName = '';
		}		
		if ($imageRequest && strlen($fileName)) {			
			/*
			 * Save image upload
			 */
			try {				
				$uploader = $this->_uploaderFactory->create(['fileId' => 'logo']);
				$uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);

				/** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
				$imageAdapter = $this->_adapterFactory->create();

				$uploader->addValidateCallback('validate', $imageAdapter, 'validateUploadFile');

				$uploader->setAllowRenameFiles(true);
				$uploader->setFilesDispersion(true);

				/** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
				$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
					->getDirectoryRead(DirectoryList::MEDIA);			
				$result = $uploader->save(
					$mediaDirectory->getAbsolutePath('Intesols/Distributor')
				);
				$data['logo'] = 'Intesols/Distributor'.$result['file'];				
			} catch (\Exception $e) {
				if ($e->getCode() == 0) {					
					$this->messageManager->addError($e->getMessage());
					
				}
			}
		} else {
			if (isset($data['logo']) && isset($data['logo']['value'])) {
				if (isset($data['logo']['delete'])) {
					$data['logo'] = null;
					$data['delete_image'] = true;
				} elseif (isset($data['logo']['value'])) {
					$data['logo'] = $data['logo']['value'];
				} else {
					$data['logo'] = null;
				}
			}
		}
        if (!$data) {
            $this->_redirect('distributor/grid/addrow');
            return;
        }	
		
        try {
            $rowData = $this->_objectManager->create('Intesols\Distributor\Model\Distributor');
            $rowData->setData($data);			
            if (isset($data['id'])) {				
                $rowData->setId($data['id']);
            }else{				
				$rowData->setCreatedAt($this->_dateFactory->create()->gmtDate());	
			}			
            $rowData->save();
            $this->messageManager->addSuccess(__('Row data has been successfully saved.'));
        } catch (Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('distributor/grid/index');
    }
 
    /**
     * Check Category Map permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Intesols_Distributor::add_auction');
    }
}