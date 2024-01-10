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

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Callback class
 */
class Callback extends Action implements \Magento\Framework\App\CsrfAwareActionInterface
{

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $_orderSender;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     *
     * @var InvoiceService
     */
    protected $_invoiceService;

    /**
     *
     * @var Transaction
     */
    protected $_transaction;

    /**
     *
     * @var InvoiceSender
     */
    protected $_invoiceSender;

    /**
	* @var \Magekc\Rsppayment\Model\Config\RspConfig
	*/
	protected $_rspConfig;

    /**
     * Constructor function
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param \Magekc\Rsppayment\Model\Config\RspConfig $rspConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magekc\Rsppayment\Model\Config\RspConfig $rspConfig
    )
    {
        $this->_orderFactory = $orderFactory;
        $this->_orderSender = $orderSender;
        $this->_logger = $logger;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->_invoiceSender = $invoiceSender;
        $this->_rspConfig = $rspConfig;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Unset the quote and redirect to checkout success.
     */
    public function execute()
    {
       
        // $this->_rspConfig->tempLog($this->getRequest()->getParams());

        if (!$this->getRequest()->isPost()) {
            return;
        }
        
        $response_code = $this->getRequest()->getPost('res_code');
        $response_message = $this->getRequest()->getPost('res_message');
        $res_trackid = $this->getRequest()->getPost('res_trackid');
        $res_referenceid = $this->getRequest()->getPost('res_referenceid');
        $res_description = $this->getRequest()->getPost('res_description');

        if ($res_trackid) {
            $res_trackid = explode('_', $res_trackid);
        }

        $this->_order = $this->_loadOrder(@$res_trackid[0]);
        $state = $this->_order->getState();
        
        switch ($response_code) {
            case '0':
                $comment = 'order_paid: Payment Sucessfully placed with Reference Track Id : ' .$res_referenceid;
                
                $this->_handlePaymentOrderPaid($comment, $res_referenceid);
                echo "\n\nSUCCESSFUL";
                $this->getResponse()->setRedirect(
                    $this->_getUrl('checkout/onepage/success')
                );
                $this->_success();
                break;
            default:
                $this->messageManager->addErrorMessage(
                    __('FAILED !. Unable to process your order !. '.$res_description)
                );
                $this->_failure();
                /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath('checkout/cart');
                break;
        }
    }

    /**
     * Update order status to order paid function
     *
     * @param $comment
     * @param $res_referenceid
     * @return void
     */
    protected function _handlePaymentOrderPaid($comment, $res_referenceid)
    {
        $this->_changeOrderState('order_paid', $comment, $res_referenceid);
    }

    /**
     * Change order status and create transaction data function
     *
     * @param [type] $state
     * @param [type] $message
     * @param [type] $res_referenceid
     * @return void
     */
    protected function _changeOrderState($state, $message, $res_referenceid)
    {
        $this->_order->setState($state, true, $message, 1)->save();
        $this->_order->setStatus($state);
        $this->_order->addStatusHistoryComment(__($message));
        $payment = $this->_order->getPayment();
        $payment->setTransactionId($res_referenceid);
        $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_ORDER, null, true);

        $payment->save();

        $this->_order->save();
    }
    /**
     * Get order by order increment id function
     *
     * @param string $ref
     * @return void
     */
    protected function _loadOrder($ref)
    {
        $order = $this->_orderFactory->create()->loadByIncrementId($ref);

        if (!($order && $order->getId())) {
            throw new \Exception('Could not find Magento order with id $order_id');
        }

        return $order;

    }

    /**
     * Set response header status function
     *
     * @return void
     */
    protected function _success()
    {
        $this->getResponse()
            ->setStatusHeader(200);
    }

    /**
     * Set response header status function
     *
     * @return void
     */
    protected function _failure()
    {
        $this->getResponse()
            ->setStatusHeader(400);
    }
    /**
     * Build URL for store.
     *
     * @param string $path
     * @param int $storeId
     * @param bool|null $secure
     *
     * @return string
     */
    protected function _getUrl($path, $secure = null)
    {
        $store = $this->_storeManager->getStore(null);

        return $this->_urlBuilder->getUrl(
            $path,
            ['_store' => $store, '_secure' => $secure === null ? $store->isCurrentlySecure() : $secure]
        );
    }
}
