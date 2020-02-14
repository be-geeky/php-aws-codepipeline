<?php
namespace Intesols\Browsecatalogs\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Media path to extension images
     *
     * @var string
     */
    const MEDIA_PATH    = 'intesols/browsecatalogs';

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $fileUploaderFactory;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $backendUrl;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->backendUrl = $backendUrl;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }
    
    /**
     * Upload image and return uploaded image file name or false
     *
     * @param string $scope the request key for file
     * @return bool|string
     */
    public function uploadFile($scope, $model)
    {
        try {
            $uploader = $this->fileUploaderFactory->create(['fileId' => $scope]);
            $uploader->setAllowedExtensions($this->getAllowedExt());
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $uploader->setAllowCreateFolders(true);
            
            if ($uploader->save($this->getBaseDir())) {
                $model->setFile($uploader->getUploadedFileName());
                $model->setFileExt($uploader->getFileExtension());
            }
        } catch (\Exception $e) {

        }

        return $model;
    }

    /**
     * Look on the file system if this file is present, according to the dispersion principle
     * @param $fileName
     * @return bool
     */
    public function checkIfFileExists($fileName){
        return file_exists($this->getDispersionFolderAbsolutePath($fileName)."/".$fileName);
    }

    /**
     * Save file-content to the file on the file-system
     * @param $filename
     * @param $fileContent
     * @return string
     */
    public function saveFile($filename, $fileContent){
        if ($fileContent != "") {
            try {
                $folderAbsolutePath = $this->getDispersionFolderAbsolutePath($filename);
                if (!file_exists($folderAbsolutePath)) {
                    //create folder
                    mkdir($folderAbsolutePath, 0777, true);
                }
                // create file
                file_put_contents($folderAbsolutePath."/".$filename, base64_decode($fileContent));
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * Return the base media directory for Productattach Item images
     *
     * @return string
     */
    public function getBaseDir()
    {
        $path = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(self::MEDIA_PATH);
        return $path;
    }
    
    /**
     * Return the Base URL for Productattach Item images
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ) . self::MEDIA_PATH;
    }

    /**
     * Return current store Id
     *
     * @return Int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Return stores
     */
    public function getStores($store)
    {
        $store = implode(',', $store);
        return $store;
    }

    /**
     * Return the path pof the file that will be save in the database
     * @param $fileName filename with file-extension
     * @return string
     */
    public function getFilePathForDB($fileName)
    {
        return $this->getFileDispersionPath($fileName) ."/". $fileName;
    }

    /**
     * Return the path to the file acording to the dispersion principle (first and second letter)
     * Example file.tyt => f/i/file.txt
     * @param $fileName
     * @return string
     */
    public function getFileDispersionPath($fileName)
    {
        return \Magento\MediaStorage\Model\File\Uploader::getDispretionPath($fileName);
    }

    /**
     * Delete the file in the folder media folder of product attachment
     * @param $fileName filename that will be used to generate the full abosulte path (dispersion)
     */
    public function deleteFile($filepathInMediaFolder)
    {
        $exts = explode('.', $filepathInMediaFolder);
        $ext = "";
        if(count($exts)){
            $ext = $exts[count($exts)-1];
        }
        if( in_array($ext, $this->getAllowedExt()) &&
            strpos($filepathInMediaFolder,"..") === false ) {
            $finalPath = $this->getProductAttachMediaFolderAbsolutePath()."/".$filepathInMediaFolder;
            if(file_exists($finalPath)){
                unlink($finalPath);
            }
        }
    }

    /**
     * Return the media folder absolute path
     * @return string
     */
    private function getProductAttachMediaFolderAbsolutePath()
    {
        $mediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        return $mediaPath . self::MEDIA_PATH;
    }

    /**
     * Return the dispersion folder absoluite path for the given filename
     * @param $filename
     * @return string
     */
    public function getDispersionFolderAbsolutePath($filename)
    {
        return $this->getProductAttachMediaFolderAbsolutePath()."/".$this->getFileDispersionPath($filename);
    }
    
    /**
     * Return the allowed file extensions
     * @return array
     */
    public function getAllowedExt()
    {
        return ['pdf'];
    }

    /**
     * Return mediaurl
     * @return string
     */
    public function getMediaUrl()
    {
        $mediaUrl = $this->storeManager-> getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
        return $mediaUrl;
    }

    /**
     * Retrive file size by attachment
     *
     * @return number
     */
    public function getFileSize($file)
    {
        $fileSize = $this->mediaDirectory->stat($file)['size'];
        $readableSize = $this->convertToReadableSize($fileSize);
        return $readableSize;
    }

    /**
     * Convert size into readable format
     */
    public function convertToReadableSize($size)
    {
        $base = log($size) / log(10024);
        $suffix = ["", " KB", " MB", " GB", " TB"];
        $f_base = floor($base);
        return round(pow(10024, $base - floor($base)), 1) . $suffix[$f_base];
    }

}