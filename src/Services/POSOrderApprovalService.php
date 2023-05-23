<?php

namespace Rutatiina\POS\Services;

use Rutatiina\GoodsReceived\Services\GoodsReceivedInventoryService;
use Rutatiina\FinancialAccounting\Services\ItemBalanceUpdateService;
use Rutatiina\GoodsDelivered\Services\GoodsDeliveredInventoryService;
use Rutatiina\FinancialAccounting\Services\AccountBalanceUpdateService;
use Rutatiina\FinancialAccounting\Services\ContactBalanceUpdateService;

trait POSOrderApprovalService
{
    public static function run($txn)
    {
        if ($txn['status'] != 'approved')
        {
            //can only update balances if status is approved
            return false;
        }

        if (isset($txn['balances_where_updated']) && $txn['balances_where_updated'])
        {
            //cannot update balances for task already completed
            return false;
        }

        //inventory checks and inventory balance update if needed
        //$this->inventory(); //currently inventory update for estimates is disabled

        //Update the account balances
        AccountBalanceUpdateService::doubleEntry($txn);

        //Update the contact balances
        ContactBalanceUpdateService::doubleEntry($txn);

        //Update the item balances
        ItemBalanceUpdateService::entry($txn);

        //Update the inventory if any item belong is DR'ed to an inventory 'sub_type'
        GoodsDeliveredInventoryService::update($txn);

        $txn->status = 'approved';
        $txn->balances_where_updated = 1;
        $txn->save();

        return true;
    }

}
