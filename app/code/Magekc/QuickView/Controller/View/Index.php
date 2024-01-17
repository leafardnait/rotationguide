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
namespace MageKc\QuickView\Controller\View;

/**
 * Index class
 */
class Index extends \Magento\Catalog\Controller\Product
{

    /**
     * @var \Magento\Catalog\Helper\Product\View
     */
    protected $productHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPage;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForward;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param \Magento\Framework\App\Action\Context               $context
     * @param \Magento\Catalog\Helper\Product\View                $productHelper
     * @param \Magento\Framework\View\Result\PageFactory          $resultPage
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForward
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Helper\Product\View $productHelper,
        \Magento\Framework\View\Result\PageFactory $resultPage,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForward,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->productHelper = $productHelper;
        $this->resultForward = $resultForward;
        $this->resultPage    = $resultPage;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $categoryId     = (int) $this->getRequest()->getParam('category', false);
        $productId      = (int) $this->getRequest()->getParam('id');
        $specifyOptions = $this->getRequest()->getParam('options');

        if ($this->getRequest()->isPost() && $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED)) {
            $product = $this->_initProduct();
            if (!$product) {
                return $this->noProductRedirect();
            }
            if ($specifyOptions) {
                $notice = $product->getTypeInstance()->getSpecifyOptionMessage();
                $this->messageManager->addNotice($notice);
            }
            if ($this->getRequest()->isAjax()) {
                $this->getResponse()->representJson(
                    $this->jsonHelper->jsonEncode([
                        'backUrl' => $this->_redirect->getRedirectUrl(),
                    ])
                );

                return;
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setRefererOrBaseUrl();

            return $resultRedirect;
        }

        $params = new \Magento\Framework\DataObject();
        $params->setCategoryId($categoryId);
        $params->setSpecifyOptions($specifyOptions);

        try {
            $page = $this->resultPage->create(false, ['isIsolated' => true, 'template' => 'Magekc_QuickView::root.phtml']);
            $page->addDefaultHandle();
            $this->productHelper->prepareAndRender($page, $productId, $this, $params);

            return $page;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $this->noProductView();
        } catch (\Exception $e) {
            $resultForward = $this->resultForward->create();
            $resultForward->forward('noroute');

            return $resultForward;
        }
    }

    protected function noProductView($message = '')
    {
        $html = '<div class="message info error"><div>' . $message . '</div></div>';
        $this->getResponse()->setBody($html);
    }
}
