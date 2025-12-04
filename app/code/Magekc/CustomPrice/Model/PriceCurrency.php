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

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Currency\CurrencyInterface;

class PriceCurrency implements PriceCurrencyInterface
{
    protected $currency;

    public function __construct(CurrencyInterface $currency)
    {
        $this->currency = $currency;
    }

    public function format(
        $amount,
        $includeContainer = true,
        $precision = 0,
        $scope = null,
        $currency = null
    ) {
        // Round to nearest whole number
        $roundedAmount = round($amount);

        return $this->currency->format(
            $roundedAmount,
            ['precision' => 0],
            $includeContainer,
            $scope,
            $currency
        );
    }

}