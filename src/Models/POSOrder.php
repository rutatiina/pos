<?php

namespace Rutatiina\POS\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Rutatiina\Tenant\Scopes\TenantIdScope;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class POSOrder extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected static $logName = 'Txn';
    protected static $logFillable = true;
    protected static $logAttributes = ['*'];
    protected static $logAttributesToIgnore = ['updated_at'];
    protected static $logOnlyDirty = true;

    protected $connection = 'tenant';

    protected $table = 'rg_pos_orders';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected $casts = [
        'canceled' => 'integer',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'number_string',
        'total_in_words',
        'print',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new TenantIdScope);

        self::deleting(function($txn) { // before delete() method call this
             $txn->items()->each(function($row) {
                $row->delete();
             });
             $txn->ledgers()->each(function($row) {
                $row->delete();
             });
             $txn->item_taxes()->each(function($row) {
                $row->delete();
             });
        });

        self::restoring(function($txn) {
             $txn->items()->withTrashed()->each(function($row) {
                $row->restore();
             });
             $txn->ledgers()->withTrashed()->each(function($row) {
                $row->restore();
             });
             $txn->item_taxes()->withTrashed()->each(function($row) {
                $row->restore();
             });
        });
    }
    
    /**
     * Scope a query to only include approved records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    
    /**
     * Scope a query to only include not canceled records
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotCancelled($query)
    {
        return $query->where(function($q) {
            $q->where('canceled', 0);
            $q->orWhereNull('canceled');
        });
    }

    public function rgGetAttributes()
    {
        $attributes = [];
        $describeTable =  \DB::connection('tenant')->select('describe ' . $this->getTable());

        foreach ($describeTable  as $row) {

            if (in_array($row->Field, ['id', 'created_at', 'updated_at', 'deleted_at', 'tenant_id', 'user_id'])) continue;

            if (in_array($row->Field, ['currencies', 'taxes'])) {
                $attributes[$row->Field] = [];
                continue;
            }

            if ($row->Default == '[]') {
                $attributes[$row->Field] = [];
            } else {
                $attributes[$row->Field] = ''; //$row->Default; //null affects laravel validation
            }
        }

        //add the relationships
        $attributes['type'] = [];
        $attributes['debit_account'] = [];
        $attributes['credit_account'] = [];
        $attributes['items'] = [];
        $attributes['ledgers'] = [];
        $attributes['comments'] = [];
        $attributes['contact'] = [];
        $attributes['recurring'] = [];

        return $attributes;
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = strtolower($value);
    }

    public function getContactAddressArrayAttribute()
    {
        return preg_split("/\r\n|\n|\r/", $this->contact_address);
    }

    public function getNumberStringAttribute()
    {
        return $this->number_prefix.(str_pad(($this->number), $this->number_length, "0", STR_PAD_LEFT)).$this->number_postfix;
    }

    public function getTotalInWordsAttribute()
    {
        $f = new \NumberFormatter( locale_get_default(), \NumberFormatter::SPELLOUT );
        return ucfirst($f->format($this->total));
    }

    public function debit_financial_account()
    {
        return $this->hasOne('Rutatiina\FinancialAccounting\Models\Account', 'id', 'debit');
    }

    public function credit_financial_account()
    {
        return $this->hasOne('Rutatiina\FinancialAccounting\Models\Account', 'id', 'credit');
    }

    public function items()
    {
        return $this->hasMany('Rutatiina\POS\Models\POSOrderItem', 'pos_order_id')->orderBy('id', 'asc');
    }

    public function getLedgersAttribute($txn = null)
    {
        // if (!$txn) $this->items;

        $txn = $txn ?? $this;

        $txn = (is_object($txn)) ? $txn : collect($txn);
        
        $ledgers = [];

        //DR ledger
        $ledgers['ledgers'][] = [
            'financial_account_code' => $txn['debit_financial_account_code'],
            'effect' => 'debit',
            'total' => $txn['total'],
            'contact_id' => $txn['contact_id']
        ];

        //CR ledger
        $ledgers['ledgers'][] = [
            'financial_account_code' => $txn['credit_financial_account_code'],
            'effect' => 'credit',
            'total' => $txn['total'],
            'contact_id' => $txn['contact_id']
        ];

        foreach ($ledgers as &$ledger)
        {
            $ledger['tenant_id'] = $txn->tenant_id;
            $ledger['date'] = $txn->date;
            $ledger['currency'] = $txn->currency;
        }
        unset($ledger);

        return collect($ledgers);
    }

    public function contact()
    {
        return $this->hasOne('Rutatiina\Contact\Models\Contact', 'id', 'contact_id');
    }

    public function item_taxes()
    {
        return $this->hasMany('Rutatiina\POS\Models\POSOrderItemTax', 'pos_order_id', 'id');
    }

    public function getTaxesAttribute()
    {
        $grouped = [];
        $this->item_taxes->load('tax'); //the values of the tax are used by the display of the document on the from end

        Log::info($this->item_taxes);

        foreach($this->item_taxes as $item_tax)
        {
            if (isset($grouped[$item_tax->tax_code]))
            {
                $grouped[$item_tax->tax_code]['amount'] += $item_tax['amount'];
                $grouped[$item_tax->tax_code]['inclusive'] += $item_tax['inclusive'];
                $grouped[$item_tax->tax_code]['exclusive'] += $item_tax['exclusive'];
            }
            else
            {
                $grouped[$item_tax->tax_code] = $item_tax->toArray();
            }
        }
        return $grouped;
    }

    public function getPrintAttribute()
    {
        return optional(POSOrderSetting::first())->print_receipt;
    }

}
