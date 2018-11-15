<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Log;


class Order extends Model
{
    /**
     * Mass assign
     */
    protected $guarded = [];

    /**
     * Enum values names
     */
    public static $discount = [
        'currency' => 'грн.',
        'percent'  => '%'
    ];

    /**
     * Relationships
     */
    public function products()
    {
        return $this->belongsToMany('App\Models\ProductSupplierPrice', 'order_product')
                    ->withPivot('product_name')
                    ->withPivot('price')
                    ->withPivot('quantity');
    }

    public function buyer()
    {
        return $this->belongsTo('App\Models\Buyer');
    }

    public function paymentMethod()
    {
        return $this->belongsTo('App\Models\PaymentMethod');
    }

    public function shippingMethod()
    {
        return $this->belongsTo('App\Models\ShippingMethod');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\OrderStatus');
    }

    public function money()
    {
        return $this->morphOne('App\Models\Money', 'base');
    }

    /**
     * Getters
     */
    public function getArchiveAttributesAttribute()
    {
        return [
            'buyer' => $this->buyer_fullname . ' (id: ' . $this->buyer_id . ')',
            'payment_method' => $this->paymentMethod->name,
            'shipping_method' => $this->shippingMethod->name,
            'NP_city_store' => $this->NP_city_store,
            'shipping_cost' => $this->shipping_cost,

            'discount' => $this->discount_value ?
                          $this->discount_value . ' ' . static::$discount[ $this->discount_type ] :
                          '',

            'paid' => $this->paid ? '<i class="fa fa-check" aria-hidden="true"></i>' : '',
            'shipped' => $this->shipped ? '<i class="fa fa-check" aria-hidden="true"></i>' : '',
            'status' => $this->status->name,
        ];
    }

    public function getOrderIsDirtyAttribute(){
        if ($this->buyer_fullname != $this->buyer->fullname) return true;

        $dirtyFields = $this->getDirty();
        // grand total can change if products info changes
        unset($dirtyFields['grand_total']);
        return empty($dirtyFields) ? false : true;
    }

    public function getCreatedAtFormattedAttribute(){
        return formatDate($this->created_at);
    }

    /**
     * Scopes
     */
    public function scopeInit($query, $statusFilter)
    {
        $query = $query->select(
                        'orders.*',
                        //to make field 'serial' orderable
                        DB::raw('SUBSTRING(orders.serial FROM 5) as serial_num'),
                        DB::raw("CONCAT(b.surname, ' ', b.name) as buyer_fullname"),
                        'pm.name as pm_name', 'sm.name as sm_name', 's.name as status_name'
                    )
                    ->join('buyers AS b', 'orders.buyer_id', '=', 'b.id')
                    ->join('payment_methods AS pm', 'orders.payment_method_id', '=', 'pm.id')
                    ->join('shipping_methods AS sm', 'orders.shipping_method_id', '=', 'sm.id')
                    ->join('order_statuses AS s', 'orders.status_id', '=', 's.id');

        // filter
        switch ($statusFilter) {
            case 'open':
                $query->where('status_id', 1);
                break;
            case 'fulfilled':
                $query->where('status_id', 3);
                break;
            case 'cancelled':
                $query->where('status_id', 2);
                break;
            case 'all':
                break;
        }

        return $query;
    }

    public function scopeSearch($query, $search)
    {
        // generate brackets for search condition
        $query = $query->where( function($query) use ($search) {
            $query->where('serial', 'LIKE', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT(b.surname, ' ', b.name)"), 'LIKE', "%{$search}%")
                  ->orWhere('pm.name', 'LIKE', "%{$search}%")
                  ->orWhere('sm.name', 'LIKE', "%{$search}%")
                  // fix datetime MySQL bug for 'like'
                  ->orWhere(DB::raw("CAST(orders.created_at AS CHAR)"), 'LIKE', "%{$search}%");

        });

        return $query;
    }
}
