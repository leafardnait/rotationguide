<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magekc\Rsppayment\Model\Adminhtml\Source;

class Cctype extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * @return array
     */
    public function getAllowedTypes()
    {
        return array('VI', 'MC', 'AE', 'DI', 'JCB', 'OT');
    }
}
