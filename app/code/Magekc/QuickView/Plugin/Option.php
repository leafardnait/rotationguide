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
namespace MageKc\QuickView\Plugin;

/**
 * Option class
 */
class Option extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
    /**
     * Before Get Option function
     *
     * @return void
     */
    public function beforeGetOption()
    {
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->getLayout()->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        return $this;
    }
}