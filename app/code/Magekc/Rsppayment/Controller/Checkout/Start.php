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
namespace Magekc\Rsppayment\Controller\Checkout;

/**
 * Start class
 */
class Start extends \Magento\Framework\App\Action\Action
{
	/**
	* @var \Magento\Checkout\Model\Session
	*/
	protected $_checkoutSession;

	/**
	* @var \Magekc\Rsppayment\Model\Config\RspConfig
	*/
	protected $_rspConfig;

    /**
	* @var \Magento\Framework\App\Action\Context
	*/
	protected $_resultRedirectFactory;

	/**
	* @var \Magento\Framework\UrlInterface
	*/
	protected $_urlBuilder;

	/**
	* @param \Magento\Framework\App\Action\Context $context
	* @param \Magento\Checkout\Model\Session $checkoutSession
	* @param \Magekc\Rsppayment\Model\Config\RspConfig $rspConfig
	* @param \Magento\Framework\UrlInterface $urlBuilder
	*/
	public function __construct(
	\Magento\Framework\App\Action\Context $context,
	\Magento\Checkout\Model\Session $checkoutSession,
	\Magekc\Rsppayment\Model\Config\RspConfig $rspConfig,
	\Magento\Framework\UrlInterface $urlBuilder
	) {
		$this->_rspConfig = $rspConfig;
		$this->_checkoutSession = $checkoutSession;
		$this->_urlBuilder = $urlBuilder;
		$this->_resultRedirectFactory = $context->getResultRedirectFactory();
		parent::__construct($context);
	}

	/**
	* Start checkout by creating request data and redirect customer to rising sun payment gateway.
	*/
    public function execute()
    {
        $order = $this->_getOrder();
		if (empty($order->getIncrementId())) {
			$resultRedirect = $this->resultRedirectFactory->create();
			$response_url = $this->_urlBuilder->getUrl('checkout/cart');
       		$resultRedirect->setUrl($response_url);
			return $resultRedirect;
		}

		$billing = $order->getBillingAddress();
		
		if (empty($billing)) {
			$billing = $order->getShippingAddress();
		}
		
		$gateway_url =  trim($this->_rspConfig->getMerchantGatewayUrl() ?? "");
		$return_url =  trim($this->_rspConfig->getCallbackUrl() ?? "");
		$api_mid = trim($this->_rspConfig->getMerchantId() ?? "");
		$api_accountid = trim($this->_rspConfig->getMerchantAccountId() ?? "");
		$api_username = trim($this->_rspConfig->getMerchantUsername() ?? "");
		$api_password = trim($this->_rspConfig->getMerchantPassword() ?? "");
		$api_secretkey = trim($this->_rspConfig->getMerchantSecretKey() ?? "");
		
		$trackid = trim($order->getIncrementId() ?? "").'_'.time();
		$statecode = trim($billing->getRegionCode() ?? "");
		$amount = $order->getBaseGrandTotal();
		$totals = number_format($amount, 2, '.', '');
		$currencyDesc = $order->getBaseCurrencyCode();

		$fields = array(
			'req_type' => 'PAYMENT',
			'req_mid' => $api_mid,
			'req_accountid' => $api_accountid,
			'req_username' => $api_username,
			'req_password' => $api_password,
			'req_trackid' => $trackid,
			'req_amount' => $totals,
			'req_currency' => $currencyDesc,
			'req_firstname' => urlencode(trim($billing->getFirstName() ?? "")), 
			'req_lastname' => urlencode(trim($billing->getLastName() ?? "")), 
			'req_email' => urlencode(trim($billing->getEmail() ?? "")),
			'req_ipaddress' =>  urlencode($_SERVER["REMOTE_ADDR"]),
			'req_returnurl' => urlencode($return_url),
			'req_remarks' => urlencode('Magento Order'),
			'req_signature' => md5( 'PAYMENT' . $api_mid . $api_accountid . $api_username. $api_password. $trackid . $api_secretkey ),
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
			$redirect_url_gateway = '';
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
					}else if( $arr[0] == 'res_redirecturl' ){
						$redirect_url_gateway = urldecode(trim($arr[1]));
					}
				}
			}

			if( $res_code == '2' ){ // pending response order status
				if (!empty($redirect_url_gateway)) {
					echo '<form action="' . $redirect_url_gateway . '" method="post" id="rsp_payment_form">
						<style type="text/css">
							@import url(https://fonts.googleapis.com/css?family=Raleway:100);
							.Absolute-Center {font-family: "Roboto", Helvetica, Arial, sans-serif;width: 600px;height: 100px;position: absolute;top:0;bottom: 0;left: 0;right: 0;margin: auto;font-size: 14px;}
							.Absolute-Center p{color:#ffffff}
							body{background:#000d3a;margin: 40px 50px;color:#4a8df8;font-family: "Raleway", cursive;font-weight:100;}h1{color:#ff8200;font-family: "Raleway", cursive;font-weight:100;font-stretch:normal;font-size:3em;font-weight:bold;}
							a{color:#ff8200;font-weight:bold;font-family: "Raleway", cursive;}.slider{position:absolute;width:400px;height:2px;margin-top:-20px;}.line{position:absolute;background:#ffffff;width:400px;height:2px;}
							.break{position:absolute;background:#222;width:6px;height:2px;}
							.dot1{-webkit-animation: loading 2s infinite;-moz-animation: loading 2s infinite;-ms-animation: loading 2s infinite;-o-animation: loading 2s infinite;animation: loading 2s infinite;}.dot2{-webkit-animation: loading 2s 0.5s infinite;-moz-animation: loading 2s 0.5s infinite;-ms-animation: loading 2s 0.5s infinite;-o-animation: loading 2s 0.5s infinite;animation: loading 2s 0.5s infinite;}
							.dot3{-webkit-animation: loading 2s 1s infinite;-moz-animation: loading 2s 1s infinite;-ms-animation: loading 2s 1s infinite;-o-animation: loading 2s 1s infinite;animation: loading 2s 1s infinite;}
							@keyframes "loading" {from { left: 0; }to { left: 400px; }}@-moz-keyframes loading {from { left: 0; }to { left: 400px; }}@-webkit-keyframes "loading" {from { left: 0; }to { left: 400px; }}@-ms-keyframes "loading" {from { left: 0; }to { left: 400px; }}@-o-keyframes "loading" {from { left: 0; }to { left: 400px; }
						</style>
						<div class="Absolute-Center">
							<h1>Just a moment...</h1>
							<div class="slider">
								<div class="line"></div>
								<div class="break dot1"></div>
								<div class="break dot2"></div>
								<div class="break dot3"></div>
							</div>
							<p>Please wait while you are being redirected to payment page... Not working? <a href="'.$redirect_url_gateway.'">Click here.</a></p>
						</div>
						<script type="text/javascript">
							window.onload=function(){
								document.forms["rsp_payment_form"].submit();
							}
						</script>
					</form>';
				}
			}
		}
        
    }

	/**
	* Get order object.
	*
	* @return \Magento\Sales\Model\Order
	*/
	protected function _getOrder()
	{
		return $this->_checkoutSession->getLastRealOrder();
	}

}
