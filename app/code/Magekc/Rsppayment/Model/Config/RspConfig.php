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

namespace Magekc\Rsppayment\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magekc\Rsppayment\Model\Adminhtml\Source\Environment;
use Magekc\Rsppayment\Model\StoreConfigResolver;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magekc\Rsppayment\Logger\Handler\Debug as RspLogger;

/**
 * RspConfig class
 */
class RspConfig extends \Magento\Payment\Gateway\Config\Config
{
    public const KEY_ENVIRONMENT = 'environment';
    public const KEY_ACTIVE = 'active';    
    // config production
    public const KEY_MERCHANT_ID = 'merchant_id';
    public const KEY_MERCHANT_ACCOUNT_ID = 'account_id';
    public const KEY_MERCHANT_USERNAME = 'username';
    public const KEY_MERCHANT_PASSWORD = 'password';
    public const KEY_MERCHANT_SECRET_KEY = 'merchant_secret_key';
    public const KEY_MERCHANT_GATEWAY_URL = 'merchant_gateway_url';
    // config sandbox
    public const KEY_SANDBOX_MERCHANT_ID = 'test_merchant_id';
    public const KEY_SANDBOX_MERCHANT_ACCOUNT_ID = 'test_account_id';
    public const KEY_SANDBOX_MERCHANT_USERNAME = 'test_username';
    public const KEY_SANDBOX_MERCHANT_PASSWORD = 'test_password';
    public const KEY_SANDBOX_MERCHANT_SECRET_KEY = 'test_merchant_secret_key';
    public const KEY_SANDBOX_MERCHANT_GATEWAY_URL = 'test_merchant_gateway_url';

    public const KEY_MERCHANT_CALLBACK_URL = 'req_returnurl';

    /**
     * @var StoreConfigResolver
     */
    private $storeConfigResolver;

    /**
     *
     * @var EncryptorInterface
     */
    private $_encryptor;

    /**
     * @var CurlFactory
     */
    protected $_curlFactory;
    
    /**
     *
     * @var RspLogger
     */
    private $_rspLogger;

    /**
     *
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreConfigResolver $storeConfigResolver
     * @param string|null $methodCode
     * @param string $pathPattern
     * @param EncryptorInterface $encryptor
     * @param CurlFactory $curlFactory
     * @param RspLogger $rspLogger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreConfigResolver $storeConfigResolver,
        EncryptorInterface $encryptor,
        \Magento\Framework\HTTP\Client\Curl $curlFactory,
        RspLogger $rspLogger,
        string $methodCode = null,
        string $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->storeConfigResolver = $storeConfigResolver;
        $this->_encryptor = $encryptor;
        $this->_curlFactory = $curlFactory;
        $this->_rspLogger = $rspLogger;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Get environment
     *
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getEnvironment()
    {
        return $this->getConfigValue(self::KEY_ENVIRONMENT);
    }

    /**
     * Get Payment configuration status
     *
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function isActive(): bool
    {
        return (bool) $this->getConfigValue(self::KEY_ACTIVE);
    }

    /**
     * Get Merchant Id
     *
     * @return string|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getMerchantId(): ?string
    {
        if ($this->getEnvironment() === Environment::ENVIRONMENT_SANDBOX) {
            return $this->getConfigValue(self::KEY_SANDBOX_MERCHANT_ID);
        }
        return $this->getConfigValue(self::KEY_MERCHANT_ID);
    }

    /**
     * Get Merchant Account Id
     *
     * @return string|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getMerchantAccountId(): ?string
    {
        if ($this->getEnvironment() === Environment::ENVIRONMENT_SANDBOX) {
            return $this->getConfigValue(self::KEY_SANDBOX_MERCHANT_ACCOUNT_ID);
        }
        return $this->getConfigValue(self::KEY_MERCHANT_ACCOUNT_ID);
    }

    /**
     * Get Merchant Username
     *
     * @return string|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getMerchantUsername(): ?string
    {
        if ($this->getEnvironment() === Environment::ENVIRONMENT_SANDBOX) {
            return $this->getConfigValue(self::KEY_SANDBOX_MERCHANT_USERNAME);
        }
        return $this->getConfigValue(self::KEY_MERCHANT_USERNAME);
    }


    /**
     * Get Merchant Password
     *
     * @return string|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getMerchantPassword(): ?string
    {
        $mvalue = $this->getConfigValue(self::KEY_MERCHANT_PASSWORD);
        if ($this->getEnvironment() === Environment::ENVIRONMENT_SANDBOX) {
            $mvalue = $this->getConfigValue(self::KEY_SANDBOX_MERCHANT_PASSWORD);
        }
        return $this->decryptValue($mvalue);
    }

    /**
     * Get Merchant Secret Key
     *
     * @return string|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getMerchantSecretKey(): ?string
    {
        $mvalue = $this->getConfigValue(self::KEY_MERCHANT_SECRET_KEY);
        if ($this->getEnvironment() === Environment::ENVIRONMENT_SANDBOX) {
            $mvalue = $this->getConfigValue(self::KEY_SANDBOX_MERCHANT_SECRET_KEY);
        }
        return $this->decryptValue($mvalue);
    }

    /**
     * Get Merchant Gateway Url
     *
     * @return string|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getMerchantGatewayUrl(): ?string
    {
        $mvalue = $this->getConfigValue(self::KEY_MERCHANT_GATEWAY_URL);
        if ($this->getEnvironment() === Environment::ENVIRONMENT_SANDBOX) {
            $mvalue = $this->getConfigValue(self::KEY_SANDBOX_MERCHANT_GATEWAY_URL);
        }
        return $mvalue;
    }

    /**
     * Get Callback url
     *
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getCallbackUrl(): string
    {
        return $this->getConfigValue(self::KEY_MERCHANT_CALLBACK_URL);
    }

    /**
     * Send Api Payment Transaction
     *
     * @param array $bodyRequest
     * @return array|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function sendApiPaymentTransaction($bodyRequest)
    {
        $result = [];

        if (!$this->isActive()) {
            $this->_rspLogger->customLog('Rsp Payment Disable');
            return false;
        }
        try {

            $gatewayUrl = $this->getMerchantGatewayUrl();
        
            $this->_curlFactory->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->_curlFactory->setOption(CURLOPT_CONNECTTIMEOUT, 180);
            $this->_curlFactory->setOption(CURLOPT_FOLLOWLOCATION, 1);
            $this->_curlFactory->setOption(CURLOPT_SSL_VERIFYPEER,false);
            $this->_curlFactory->setOption(CURLOPT_POST,1);
            $this->_curlFactory->post($gatewayUrl, $bodyRequest);
            $result = $this->_curlFactory->getBody();

        } catch (\Exception $e) {
            $result['response'] = $e->getMessage();
        }
        $this->_rspLogger->customLog($result);
        return $result;
    }
    
    /**
     * Decriptor function
     *
     * @param string $value
     * @return void
     */
    public function decryptValue($value)
    {
        if($value){
            return $this->_encryptor->decrypt($value);
        }
    }

    /**
     * Get Config Value
     *
     * @param string $key
     * @return void
     */
    public function getConfigValue($key)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig->getValue('payment/magekc_rsppayment/'.$key, $storeScope);
    }

    /**
     * temp log function
     *
     * @param [type] $log
     * @return void
     */
    public function tempLog($log)
    {
        if (!empty($log)) {
            $this->_rspLogger->customLog(json_encode($log, true));
        }
    }
}
