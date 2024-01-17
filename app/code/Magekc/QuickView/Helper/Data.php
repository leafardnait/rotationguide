<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @category Magekc
 * @package  Magekc_QuickView
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)  
 *
 * @author   Kristian Claridad<kristianrafael.claridad@gmail.com>
 */
namespace MageKc\QuickView\Helper;

/**
 * Data class
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $_scopeConfig;
    
    /**
     * Constructor function
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $context->getScopeConfig();
    }

    /**
     * Get product Url function
     *
     * @param $_product
     * @return string
     */
    public function getQuickViewUrl($_product)
    {
        $id = $_product->getId();
        return $this->getBaseUrl().'quickview/view/index/id/'.$id;
    }

    /**
     * Get Quick View Button function
     *
     * @param $_product
     * @param string $class
     * @return string
     */
    public function getQuickViewButton($_product, $class = '')
    {
        if ($this->getConfig('quickview/general/active')) {
            $quickViewUrl = $this->getQuickViewUrl($_product);
            $quickViewTitle = __('Quick View');
            $quickViewLabel = "<i class='mbi mbi-eye'></i>";
            $html = "<button type='button' class=\"btn-quickview {$class}\" data-mfp-src=\"{$quickViewUrl}\" title=\"{$quickViewTitle}\">";
            $html .= "{$quickViewLabel}";
            if ($_product->getHasOptions()) {
                $html .= '<span class="has-option d-none"></span>';
            }
            $html .= '</button>';

            return $html;
        }

        return;
    }

    /**
     * Get Config Value function
     *
     * @param string $fullPath
     * @return string
     */
    public function getConfig($fullPath)
    {
        return $this->_scopeConfig->getValue($fullPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Quick View Label function
     *
     * @return string
     */
    public function getQuickViewLabel()
    {
        return $this->_scopeConfig->getValue('quickview/general/label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Base Url function
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
}