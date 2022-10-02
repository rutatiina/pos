<?php

namespace Rutatiina\POS\Services;

use Rutatiina\POS\Models\POSOrderItem;
use Rutatiina\POS\Models\POSOrderItemTax;

class POSOrderItemService
{
    public static $errors = [];

    public function __construct()
    {
        //
    }

    public static function store($data)
    {
        //print_r($data['items']); exit;

        //Save the items >> $data['items']
        foreach ($data['items'] as &$item)
        {
            $item['pos_order_id'] = $data['id'];

            $itemTaxes = (is_array($item['taxes'])) ? $item['taxes'] : [] ;
            unset($item['taxes']);

            $itemModel = POSOrderItem::create($item);

            foreach ($itemTaxes as $tax)
            {
                //save the taxes attached to the item
                $itemTax = new POSOrderItemTax;
                $itemTax->tenant_id = $item['tenant_id'];
                $itemTax->pos_order_id = $item['pos_order_id'];
                $itemTax->pos_order_item_id = $itemModel->id;
                $itemTax->tax_code = $tax['code'];
                $itemTax->amount = $tax['total'];
                $itemTax->inclusive = $tax['inclusive'];
                $itemTax->exclusive = $tax['exclusive'];
                $itemTax->save();
            }
            unset($tax);
        }
        unset($item);

    }

}
