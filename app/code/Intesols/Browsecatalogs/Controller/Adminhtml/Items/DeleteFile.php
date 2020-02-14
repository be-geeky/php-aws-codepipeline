<?php
namespace Intesols\Browsecatalogs\Controller\Adminhtml\Items;

use Magento\Backend\App\Action;

class DeleteFile extends \Magento\Backend\App\Action
{
    /**
     * @var \Intesols\Browsecatalogs\Model\Browsecatalogs
     */
    private $attachModel;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $file;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $fileSystem;

    /**
     * @param \Magento\Backend\App\Action $context
     * @param \Intesols\Browsecatalogs\Model\Browsecatalogs $attachModel
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\Filesystem $fileSystem
     */
    public function __construct(
        Action\Context $context,
        \Intesols\Browsecatalogs\Model\Browsecatalogs $attachModel,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\Filesystem $fileSystem
    ) {
        $this->attachModel = $attachModel;
        $this->file = $file;
        $this->fileSystem = $fileSystem;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Intesols_Browsecatalogs::delete');
    }

    /**
     * Delete action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
				$model = $this->_objectManager->create('Intesols\Browsecatalogs\Model\Browsecatalogs');
                $model->load($id);
                $currentFile = $model->getFile();
                $mediaDirectory = $this->fileSystem
                    ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
                $fileRootDir = $mediaDirectory->getAbsolutePath().'intesols/browsecatalogs';
                if ($this->file->isExists($fileRootDir . $currentFile)) {
                    $this->file->deleteFile($fileRootDir . $currentFile);
                    $model->setFile('');
                    $model->save();
                    $this->messageManager->addSuccess(__('The file has been deleted.'));
                }
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
    }
}