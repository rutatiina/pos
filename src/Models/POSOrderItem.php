<?php

namespace Rutatiina\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Scopes\TenantIdScope;

class POSOrderItem extends Model
{
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
