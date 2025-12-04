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

namespace Magekc\CustomPrice\Model;

use Magento\Framework\Pricing\PriceCurrency as CorePriceCurrency;

class PriceCurrency extends CorePriceCurrency
{
    /**
     * Override format to round off decimals
     */
    public function format(
        $amount,
        $includeContainer = true,
        $precision = 0,
        $scope = null,
        $currency = null
    ) {
        // Round to nearest whole number
        $roundedAmount = round($amount);

        return parent::format(
            $roundedAmount,
            $includeContainer,
            0, // force precision = 0
            $scope,
            $currency
        );
    }
}

