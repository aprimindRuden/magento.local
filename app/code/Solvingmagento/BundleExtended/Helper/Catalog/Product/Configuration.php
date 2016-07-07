<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solvingmagento\BundleExtended\Helper\Catalog\Product;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;

/**
 * Helper for fetching properties by product configuration item
 */
class Configuration extends \Magento\Bundle\Helper\Catalog\Product\Configuration
{
       /**
     * Get bundled selections (slections-products collection)
     *
     * Returns array of options objects.
     * Each option object will contain array of selections objects
     *
     * @param ItemInterface $item
     * @return array
     */
public function getBundleOptions(ItemInterface $item) {
        $options = [];
        $product = $item->getProduct();
        $item_id = $item->getData('item_id');

        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $product->getTypeInstance();

        // get bundle options
        $optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');
        $bundleOptionsIds = $optionsQuoteItemOption ? unserialize($optionsQuoteItemOption->getValue()) : [];
        if ($bundleOptionsIds) {
            /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
            $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $product);

            // get and add bundle selections collection
            $selectionsQuoteItemOption = $item->getOptionByCode('bundle_selection_ids');

            $bundleSelectionIds = unserialize($selectionsQuoteItemOption->getValue());

            if (!empty($bundleSelectionIds)) {
                $selectionsCollection = $typeInstance->getSelectionsByIds($bundleSelectionIds, $product);

                $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
                foreach ($bundleOptions as $bundleOption) {
                    if ($bundleOption->getSelections()) {
                        $option = ['label' => $bundleOption->getTitle(), 'value' => []];

                        $option['bundle_option']['parent_id'] = $bundleOption->getData('parent_id');
                        $optionId = $bundleOption->getData('option_id');
                        $option['bundle_option']['item_id'] = $item_id;

                        $bundleSelections = $bundleOption->getSelections();

                        foreach ($bundleSelections as $bundleSelection) {
                            $qty = $this->getSelectionQty($product, $bundleSelection->getSelectionId()) * 1;
                            if ($qty) {
                                /*  */
                                $product_id = $bundleSelection->getData('product_id');
                                $selectionId = $bundleSelection->getData('selection_id');
                                $option['value'][] = "<input type='number' id='$item_id-$optionId-$product_id'"
                                        . "name='cart[$item_id][bundle_option_qty][$optionId][$product_id]'"
                                        . " value='$qty' class='bundle_product_checkbox_quantity' />"
                                        . "<input type='hidden'  id='$item_id-$optionId-$selectionId'"
                                        . "  name='cart[$item_id][bundle_option][$optionId][$product_id]' value='$selectionId'> x "
                                        . $this->escaper->escapeHtml($bundleSelection->getName())
                                        . ' '
                                        . $this->pricingHelper->currency(
                                                $this->getSelectionFinalPrice($item, $bundleSelection)
                                );
                            }
                        }

                        if ($option['value']) {
                            $options[] = $option;
                        }
                    }
                }
            }
        }

        return $options;
    }
}
