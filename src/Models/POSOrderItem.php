<?php

namespace Rutatiina\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Rutatiina\Tenant\Scopes\TenantIdScope;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class POSOrderItem extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected static $logName = 'TxnItem';
    protected static $logFillable = true;
    protected static $logAttributes = ['*'];
    protected static $logAttributesToIgnore = ['updated_at'];
    protected static $logOnlyDirty = true;

    protected $connection = 'tenant';

    protected $table = 'rg_pos_order_items';

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new TenantIdScope);

        self::deleting(function($txn) {
             $txn->taxes()->each(function($row) {
                $row->delete();
             });
        });
    }

    public function pos_order()
    {
        return $this->belongsTo('Rutatiina\POS\Models\POSOrder', 'pos_order_id', 'id');
    }

    public function taxes()
    {
        return $this->hasMany('Rutatiina\POS\Models\POSOrderItemTax', 'pos_order_item_id', 'id');
    }

}
