<?php

namespace Wyomind\Elasticsearch\Block\Adminhtml\System\Config\Form\Field;

class Version extends \Magento\Config\Block\System\Config\Form\Field
{

//    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
//    {
//        $element->setReadonly(true);
//        $element->setDisabled('disabled');
//        return parent::_getElementHtml($element);
//    }
    
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($element->getTooltip()) {
            $html = '<td class="value with-tooltip">';
            $html .= $element->getEscapedValue();
            $html .= '<div class="tooltip"><span class="help"><span></span></span>';
            $html .= '<div class="tooltip-content">' . $element->getTooltip() . '</div></div>';
        } else {
            $html = '<td class="value">';
            $html .= $element->getEscapedValue();
        }
        if ($element->getComment()) {
            $html .= '<p class="note"><span>' . $element->getComment() . '</span></p>';
        }
        $html .= '</td>';
        return $html;
    }

}
