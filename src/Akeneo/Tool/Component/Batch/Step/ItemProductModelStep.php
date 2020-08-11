<?php

namespace Akeneo\Tool\Component\Batch\Step;

/**
 * Basic step implementation that read items, process them and write them
 *
 * @author    Diogo Pina <diogo@vivapets.com>
 * @copyright 2020 Vivapets (http://www.vivapets.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class ItemProductModelStep extends ItemStep
{
    protected $skuModel;

    protected function process($readItem)
    {
        if (!isset($this->skuModel)) {
            $this->skuModel = $this->getProductModelSku($readItem);
        }

        $readItem->skuModel = $this->skuModel;
        return parent::process($readItem);
    }

    private function getProductModelSku($product) {
        foreach ($product->getValues() as $value) {
            if ($value->getAttributeCode() == 'sku') {
                return $value->getData() . '-1';
            }                
        }
    }
}
