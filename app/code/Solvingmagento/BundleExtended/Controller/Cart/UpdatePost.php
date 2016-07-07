<?php

namespace Solvingmagento\BundleExtended\Controller\Cart;

class UpdatePost extends \Magento\Checkout\Controller\Cart\UpdatePost {

    protected function _updateShoppingCart() {
        $items = $this->recalculateBundleOptionsQtys($this->getRequest()->getParam('cart'));
        $this->recalculateQtys($this->getRequest()->getParam('cart'));
        if (!empty($items)) {
            foreach ($items as $item) {
                $this->_eventManager->dispatch(
                        'checkout_cart_update_item_complete', ['item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
                );
            }
        }
        return $this->_goBack($this->_url->getUrl('checkout/cart'));
    }

    public function recalculateBundleOptionsQtys($request) {
        $getProducts = $request;
        foreach ($getProducts as $productId => $values) {
            if (isset($getProducts[$productId]["bundle_option_qty"])) {

                $getInfoBuyRequest = $this->cart->getQuote()->getItemById($productId)->getOptionByCode("info_buyRequest")->getValue();
                $parceledInfoBuyRequest = unserialize($getInfoBuyRequest);
                $parceledInfoBuyRequest["qty"] = $values["qty"];

                foreach ($values["bundle_option"] as $key => $value) {
                    $parceledInfoBuyRequest["bundle_option"][$key] = $values["bundle_option"][$key];
                    $parceledInfoBuyRequest["bundle_option_qty"][$key] = $values["bundle_option_qty"][$key];
                }
                try {
                    if (isset($parceledInfoBuyRequest["qty"])) {
                        $filter = new \Zend_Filter_LocalizedToNormalized(
                                ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
                        );
                        $parceledInfoBuyRequest["qty"] = $filter->filter($parceledInfoBuyRequest["qty"]);
                    }

                    $quoteItem = $this->cart->getQuote()->getItemById($productId);
                    if (!$quoteItem) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t find the quote item.'));
                    }

                    $item = $this->cart->updateItem($productId, new \Magento\Framework\DataObject($parceledInfoBuyRequest));
                    $items[] = $item;
                    if (is_string($item)) {
                        throw new \Magento\Framework\Exception\LocalizedException(__($item));
                    }
                    if ($item->getHasError()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__($item->getMessage()));
                    }

                    $related = $this->getRequest()->getParam('related_product');
                    if (!empty($related)) {
                        $this->cart->addProductsByIds(explode(',', $related));
                    }
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    if ($this->_checkoutSession->getUseNotice(true)) {
                        $this->messageManager->addNotice($e->getMessage());
                    } else {
                        $messages = array_unique(explode("\n", $e->getMessage()));
                        foreach ($messages as $message) {
                            $this->messageManager->addError($message);
                        }
                    }

                    $url = $this->_checkoutSession->getRedirectUrl(true);
                    if ($url) {
                        return $this->resultRedirectFactory->create()->setUrl($url);
                    } else {
                        $cartUrl = $this->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl();
                        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl($cartUrl));
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('We can\'t update the item right now.'));
                    $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                    return $this->_goBack($this->_url->getUrl('checkout/cart'));
                }
            }
        }
        return $items;
    }

    public function recalculateQtys($request) {
        try {
            $сartData = $request;
//            foreach ($сartData as $optionId =>$value){
//                if (isset($сartData[$optionId]["bundle_option"])) {
//                    unset($сartData[$optionId]["bundle_option"]);
//                    unset($сartData[$optionId]["bundle_option_qty"]);
//                }
//                
//            }
            if (is_array($сartData)) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                        ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
                );
                foreach ($сartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $сartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                if (!$this->cart->getCustomerSession()->getCustomerId() && $this->cart->getQuote()->getCustomerId()) {
                    $this->cart->getQuote()->setCustomerId(null);
                }

                $сartData = $this->cart->suggestItemsQty($сartData);
                $this->cart->updateItems($сartData)->save();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(
                    $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t update the shopping cart.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
    }

}
