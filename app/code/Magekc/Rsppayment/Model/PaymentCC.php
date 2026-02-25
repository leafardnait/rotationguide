<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @category Magekc
 * @package  Magekc_Rsppayment
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)  
 *
 * @author   Kristian Claridad<kristianrafael.claridad@gmail.com>
 */

namespace Magekc\Rsppayment\Model;

class PaymentCC extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'magekc_rsppayment_cc';

    protected $_code = self::CODE;

    protected $_isGateway                   = true;
    protected $_canCapture                  = true; // set true to execute capture method after click place order
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_stripeApi = false;
    protected $_countryFactory;
    protected $_rspConfig;
    protected $_minAmount = null;
    protected $_maxAmount = null;
    protected $_supportedCurrencyCodes = array('EUR','JPY','SGD','USD','KRW');

    protected $_debugReplacePrivateDataKeys = ['number', 'exp_month', 'exp_year', 'cvc'];

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magekc\Rsppayment\Model\Config\RspConfigCC $rspConfig,
        array $data = array()
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            null,
            null,
            $data
        );

        $this->_countryFactory = $countryFactory;
        $this->_rspConfig = $rspConfig;

        $this->_minAmount = $this->getConfigData('min_order_total');
        $this->_maxAmount = $this->getConfigData('max_order_total');
    }

    /**
     * Authorize payment abstract method
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->savePaymentTransaction($payment, $amount); // save payment transaction
        if (!$this->canAuthorize()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The authorize action is not available.'));
        }
        
        return $this;
    }

    /**
     * Capture payment abstract method
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->savePaymentTransaction($payment, $amount); // save payment transaction
        if (!$this->canCapture()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The capture action is not available.'));
        }
        return $this;
    }

    /**
     * Determine method availability based on quote amount and config data
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if ($quote && (
            $quote->getBaseGrandTotal() < $this->_minAmount
            || ($this->_maxAmount && $quote->getBaseGrandTotal() > $this->_maxAmount))
        ) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    /**
     * Availability for currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }

    /**
     * Map card type codes to readable names
     *
     * @param string|null $code
     * @return string
     */
    public function getCardTypeName(?string $code): string
    {
        $map = [
            'VI'  => 'VISA',
            'MC'  => 'MasterCard',
            'AE'  => 'American Express',
            'DI'  => 'Discover',
            'JCB' => 'JCB',
            'OT'  => 'Other'
        ];

        return $map[$code] ?? 'Unknown';
    }


    public function savePaymentTransaction($payment, $amount)
    {
        $order = $payment->getOrder();
        $billing = $order->getBillingAddress();
        $errorMessage = '';
        try{
            
            $gateway_url =  trim($this->_rspConfig->getMerchantGatewayUrl());
            $api_mid = trim($this->_rspConfig->getMerchantId());
            $api_accountid = trim($this->_rspConfig->getMerchantAccountId());
            $api_username = trim($this->_rspConfig->getMerchantUsername());
            $api_password = trim($this->_rspConfig->getMerchantPassword());
            $api_secretkey = trim($this->_rspConfig->getMerchantSecretKey());
            $mm = trim($payment->getCcExpMonth());
            $mm = strlen($mm) != 2 ? '0'.$mm : $mm;
            $trackid = trim($order->getIncrementId()).'_'.time();
            $statecode = trim($billing->getRegionCode());
            $type = $this->getCardTypeName($payment->getCcType());
            $totals = number_format($amount, 2, '.', '');
            $currencyDesc = $order->getBaseCurrencyCode();

            $fields = array(
                'req_type' => 'CAPTURE',
                'req_mid' => $api_mid,
                'req_accountid' => $api_accountid,
                'req_username' => $api_username,
                'req_password' => $api_password,
                'req_trackid' => $trackid,
                'req_amount' => $totals,
                'req_currency' => $currencyDesc,
                'req_cardnumber' => trim($payment->getCcNumber()),
                'req_cardtype' => trim($type),
                'req_yyyy' => trim($payment->getCcExpYear()),
                'req_mm' => $mm,
                'req_cvv' => trim($payment->getCcCid()),
                'req_firstname' => urlencode(trim($billing->getData('firstname'))), 
                'req_lastname' => urlencode(trim($billing->getData('lastname'))), 
                'req_address' => urlencode(trim($billing->getData('street'))), 
                'req_city' => urlencode(trim($billing->getData('city'))), 
                'req_statecode' => $statecode,
                'req_countrycode' => trim($billing->getData('country_id')),
                'req_zipcode' => urlencode(trim($billing->getData('postcode'))), 
                'req_phone' => urlencode(trim($billing->getData('telephone'))), 
                'req_email' => urlencode(trim($billing->getData('email'))), 
                'req_ipaddress' =>  urlencode($_SERVER["REMOTE_ADDR"]),
                'req_returnurl' => urlencode('https://rotationguide.com/rsppayment/checkout/callback'),
                'req_remarks' => urlencode('Magento Order'),
                'req_signature' => md5( 'CAPTURE' . $api_mid . $api_accountid . $api_username. $api_password. $trackid . $api_secretkey ),
            );
            
            $fields_string="";
            foreach($fields as $key=>$value) {
                    $fields_string .= $key.'='.$value.'&';
            }
            $fields_string = substr($fields_string,0,-1);

            $result = $this->_rspConfig->sendApiPaymentTransaction($fields_string);
            
            if( strlen(@$result) > 0 ){
                $res_code = '';
                $res_message = '';
                $res_description = '';
                $res_referenceid = '';
                $res = explode("&", $result);
                for ($i = 0; $i < count($res); $i++) {
                    $arr =explode("=", $res[$i]);
                    if( count($arr) == 2 ){
                        if( $arr[0] == 'res_code' ){
                            $res_code = trim($arr[1]);
                        }else if( $arr[0] == 'res_message' ){
                            $res_message = urldecode(trim($arr[1]));
                        }else if( $arr[0] == 'res_description' ){
                            $res_description = urldecode(trim($arr[1]));
                        }else if( $arr[0] == 'res_referenceid' ){
                            $res_referenceid = trim($arr[1]);
                        }
                    }
                }
                
                if( $res_code == '0' ){
                    $payment->setTransactionId($res_referenceid);
                    $payment->setIsTransactionApproved(true);
                    $order->addStatusToHistory('order_paid', 'Payment Sucessfully placed with Merchant Track Id : ' .$trackid, false);
                    $order->save();
                } else {
                    $this->_rspConfig->tempLog($res_message . ' : '. $res_description);
                    $errorMessage = 'FAILED !. Unable to process your order, please try again later.';
                }
            }
            else
            {
                $errorMessage = 'FAILED !. Unable to process your order, please try again later.';
            }
 
        }catch (\Exception $e){
            $this->_rspConfig->tempLog($e->getMessage());
            $errorMessage = 'Payment capturing error.';
        }
        
        if (!empty($errorMessage)) {
            $this->_rspConfig->tempLog($errorMessage);
            throw new \Magento\Framework\Validator\Exception(__($errorMessage));
        }
        
        return $this;
    }
}