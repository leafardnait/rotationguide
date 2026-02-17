<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magekc\Rsppayment\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

class SupportedType implements ArrayInterface
{

    /**
     * Possible environment types
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'PAYMENT',
                'label' => 'PAYMENT',
            ],
            [
                'value' => 'CAPTURE',
                'label' => 'CAPTURE'
            ],
            [
                'value' => 'SEARCH',
                'label' => 'SEARCH'
            ],
            [
                'value' => 'FULL REFUND',
                'label' => 'FULL REFUND'
            ],
            [
                'value' => 'PARTIAL REFUND',
                'label' => 'PARTIAL REFUND'
            ],
            [
                'value' => 'PAYMENT, SEARCH',
                'label' => 'PAYMENT, SEARCH'
            ],
            [
                'value' => 'CAPTURE, SEARCH, FULL REFUND',
                'label' => 'CAPTURE, SEARCH, FULL REFUND'
            ]
        ];
    }
}
