<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @category Magekc
 * @package  Magekc_CustomPrice
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)  
 *
 * @author   Kristian Claridad<kristianrafael.claridad@gmail.com>
 */

namespace Magekc\CustomPrice\Plugin;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class PriceCurrencyPlugin
{
    /**
     * Round all prices to whole numbers and force precision = 0
     */
    public function aroundFormat(
        PriceCurrencyInterface $subject,
        callable $proceed,
        $amount,
        $includeContainer = true,
        $precision = null,
        $scope = null,
        $currency = null
    ) {
        $roundedAmount = round($amount);

        // Force precision to 0 for display
        return $proceed($roundedAmount, $includeContainer, 0, $scope, $currency);
    }
}

