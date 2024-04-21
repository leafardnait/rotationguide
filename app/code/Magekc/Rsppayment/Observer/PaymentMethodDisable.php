<?php
namespace Magekc\Rsppayment\Observer;
use Magento\Framework\Event\ObserverInterface;

class PaymentMethodDisable implements ObserverInterface
{
    protected $_currency;
    protected $_storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currency,
    ){
        $this->_storeManager = $storeManager;
        $this->_currency = $currency;
    }

    /**
     * Get current store currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }    
    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // you can replace "checkmo" with your required payment method code
        if($observer->getEvent()->getMethodInstance()->getCode()=="magekc_rsppayment"){
            $isAvailable = false;
            if ($this->getCurrentCurrencyCode() === 'SGD') {
                $isAvailable = true;
            }
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', $isAvailable); //this is disabling the payment method at checkout page
        }
        if($observer->getEvent()->getMethodInstance()->getCode()=="cashondelivery"){
            $isCodAvailable = true;
            if ($this->getCurrentCurrencyCode() === 'SGD') {
                $isCodAvailable = false;
            }
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', $isCodAvailable); //this is disabling the payment method at checkout page
        }
        
    }
}