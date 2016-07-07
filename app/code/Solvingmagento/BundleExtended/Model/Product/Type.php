<?php

namespace Solvingmagento\BundleExtended\Model\Product;

class Type extends \Magento\Bundle\Model\Product\Type {
    
        protected function getQty($selection, $qtys, $selectionOptionId) {
            
        if ($selection->getSelectionCanChangeQty() && isset($qtys[$selectionOptionId])) {
            if (is_array($qtys[$selectionOptionId])) {

                        $qty = $qtys[$selectionOptionId][$selection->getProductId()];
                    } else {
                        $qty = (float) $qtys[$selectionOptionId] > 0 ? $qtys[$selectionOptionId] : 1;
                    }           
        } else {
            $qty = (float) $selection->getSelectionQty() ? $selection->getSelectionQty() : 1;
        }
        $qty = (float) $qty;

        return $qty;
    }
}
