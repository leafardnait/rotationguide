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
namespace MageKc\QuickView\Block\Product\Renderer;

/**
 * Configurable class
 */
class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    const SWATCH_RENDERER_TEMPLATE       = 'Magekc_QuickView::product/view/renderer.phtml';
    const CONFIGURABLE_RENDERER_TEMPLATE = 'Magekc_QuickView::product/view/type/options/configurable.phtml';

    protected function getRendererTemplate()
    {
        return $this->isProductHasSwatchAttribute() ?
        self::SWATCH_RENDERER_TEMPLATE : self::CONFIGURABLE_RENDERER_TEMPLATE;
    }
}
