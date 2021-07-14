<?php

namespace Rutatiina\POS\Services;

use Rutatiina\POS\Models\POSOrderLedger;

class POSOrderLedgerService
{
    public static $errors = [];

    public function __construct()
    {
        //
    }

    public static function store($data)
    {
        foreach ($data['ledgers'] as &$ledger)
        {
            $ledger['retainer_invoice_id'] = $data['id'];
            POSOrderLedger::create($ledger);
        }
        unset($ledger);

    }

}
