<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solvingmagento\BundleExtended\Block\Catalog\Product\View\Type\Bundle\Option;

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option;
/**
 * Bundle option checkbox type renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Checkbox extends Option
{
 protected $_template = 'catalog/product/view/type/bundle/option/checkbox.phtml';
  public function getSelectionQtyTitlePrice($selection, $includeContainer = true)
    {        
        $isCanChangeQty = $selection->getData("selection_can_change_qty");
        $productId = $selection->getProductId();
        $selectionOptionId = $selection->getOptionId();
        $this->setFormatProduct($selection);
        if($isCanChangeQty == 1){
            $qtyInput = '<input class="bundle-option-' .
                $selectionOptionId . '-' .
                $selection->getSelectionId() . ' checkbox_quantity" type="number" style="width:50px;" id="bundle-option-' .
                $selectionOptionId . '-' .$productId . '-qty-input" name="bundle_option_qty[' . 
                $selectionOptionId . '][' . $productId . ']" value ="' . $selection->getSelectionQty() * 1 . '">';
        }else{
            $qtyInput = $selection->getSelectionQty() * 1 . ' x ';
        }
        
        $priceTitle = '<span class="product-name">' . $qtyInput . $this->escapeHtml($selection->getName()) . '</span>';

        $priceTitle .= ' &nbsp; ' . ($includeContainer ? '<span class="price-notice">' : '') . '=' .
            $this->renderPriceString($selection, $includeContainer) . ($includeContainer ? 'for one item</span>' : '');

        return $priceTitle;
    }
   
}
