<?php
/**
 *
 * @author Raj KB<magepsycho@gmail.com>
 * @website http://www.magepsycho.com
 * @extension MassImporterPro: Pricing - http://www.magepsycho.com/mass-importer-pro-price-importer-regular-special-tier-group.html
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Capture warning / notice as exception
set_error_handler('ctv_exceptions_error_handler');
function ctv_exceptions_error_handler($severity, $message, $filename, $lineno) {
    if (error_reporting() == 0) {
        return;
    }
    if (error_reporting() & $severity) {
        throw new ErrorException($message, 0, $severity, $filename, $lineno);
    }
}
require __DIR__ . '/../app/bootstrap.php';
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('adminhtml');
/**************************************************************************************************/
// UTILITY FUNCTIONS - START
/**************************************************************************************************/
function _mpLog($data, $includeSep = false)
{
    $fileName = BP . '/var/log/m2-magepsycho-import-prices.log';
    if ($includeSep) {
        $separator = str_repeat('=', 70);
        file_put_contents($fileName, $separator . '<br />' . PHP_EOL,  FILE_APPEND | LOCK_EX);
    }
    file_put_contents($fileName, $data . '<br />' .PHP_EOL,  FILE_APPEND | LOCK_EX);
}
function mpLogAndPrint($message, $separator = false)
{
    _mpLog($message, $separator);
    if (is_array($message) || is_object($message)) {
        print_r($message);
    } else {
        echo $message . '<br />' . PHP_EOL;
    }
    if ($separator) {
        echo str_repeat('=', 70) . '<br />' . PHP_EOL;
    }
}
function getIndex($field)
{
    global $headers;
    $index = array_search($field, $headers);
    if ( !strlen($index)) {
        $index = -1;
    }
    return $index;
}
function readCsvRows($csvFile)
{
    $rows = [];
    $fileHandle = fopen($csvFile, 'r');
    while(($row = fgetcsv($fileHandle, 0, ',', '"', '"')) !== false) {
        $rows[] = $row;
    }
    fclose($fileHandle);
    return $rows;
}
function _getResourceConnection()
{
    global $obj;
    return $obj->get('Magento\Framework\App\ResourceConnection');
}
function _getReadConnection()
{
    return _getConnection('core_read');
}
function _getWriteConnection()
{
    return _getConnection('core_write');
}
function _getConnection($type = 'core_read')
{
    return _getResourceConnection()->getConnection($type);
}
function _getTableName($tableName)
{
    return _getResourceConnection()->getTableName($tableName);
}
function _getAttributeId($attributeCode)
{
    $connection = _getReadConnection();
    $sql = "SELECT attribute_id FROM " . _getTableName('eav_attribute') . " WHERE entity_type_id = ? AND attribute_code = ?";
    return $connection->fetchOne(
        $sql,
        [
            _getEntityTypeId('catalog_product'),
            $attributeCode
        ]
    );
}
function _getEntityTypeId($entityTypeCode)
{
    $connection = _getConnection('core_read');
    $sql        = "SELECT entity_type_id FROM " . _getTableName('eav_entity_type') . " WHERE entity_type_code = ?";
    return $connection->fetchOne(
        $sql,
        [
            $entityTypeCode
        ]
    );
}
function _getIdFromSku($sku)
{
    $connection = _getConnection('core_read');
    $sql        = "SELECT entity_id FROM " . _getTableName('catalog_product_entity') . " WHERE sku = ?";
    return $connection->fetchOne(
        $sql,
        [
            $sku
        ]
    );
}
function checkIfSkuExists($sku)
{
    $connection = _getConnection('core_read');
    $sql        = "SELECT COUNT(*) AS count_no FROM " . _getTableName('catalog_product_entity') . " WHERE sku = ?";
    return $connection->fetchOne($sql, [$sku]);
}

function updateStocks($sku, $stock){
    $connection     = _getWriteConnection();
	$entityId       = _getIdFromSku($sku);
    $attributeId    = _getAttributeId();
 
    $sql            = "UPDATE " . _getTableName('cataloginventory_stock_item') . " csi,
                       " . _getTableName('cataloginventory_stock_status') . " css
                       SET
                       csi.qty = ?,
                       csi.is_in_stock = ?,
                       css.qty = ?,
                       css.stock_status = ?
                       WHERE
                       csi.product_id = ?
                       AND csi.product_id = css.product_id";
    $isInStock      = $stock > 0 ? 1 : 0;
    $stockStatus    = $stock > 0 ? 1 : 0;
    $connection->query($sql, array($newQty, $isInStock, $newQty, $stockStatus, $productId));
	$connection->query(
        $sql,
        [
            $stock,
            $isInStock,
			$stock,
			$stockStatus
            $entityId
        ]
    );
}

/**************************************************************************************************/
// UTILITY FUNCTIONS - END
/**************************************************************************************************/
try {
    $csvFile        = 'var/stockimport/stocks.csv'; #EDIT - The path to import CSV file (Relative to Magento2 Root)
    $csvData        = readCsvRows(BP . '/' . $csvFile);
    $headers        = array_shift($csvData);
    $count   = 0;
    foreach($csvData as $_data) {
        $count++;
        $sku   = $_data[getIndex('sku')];
        $stock = $_data[getIndex('qty')];
        if ( ! checkIfSkuExists($sku)) {
            $message =  $count .'. FAILURE:: Product with SKU (' . $sku . ') doesn\'t exist.';
            mpLogAndPrint($message);
            continue;
        }
        try {
            updateStocks($sku, $stock);
            $message = $count . '. SUCCESS:: Updated SKU (' . $sku . ') with stock (' . $stock . ')';
            mpLogAndPrint($message);
        } catch(Exception $e) {
            $message =  $count . '. ERROR:: While updating  SKU (' . $sku . ') with Stock (' . $stock . ') => ' . $e->getMessage();
            mpLogAndPrint($message);
        }
    }
} catch (Exception $e) {
    mpLogAndPrint(
        'EXCEPTION::' . $e->getTraceAsString()
    );
}