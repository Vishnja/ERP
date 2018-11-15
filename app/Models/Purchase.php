<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Log;

class Purchase extends Model
{
    /**
     * Mass assign
     */
    protected $guarded = [];

    /**
     * Relationships
     */
    public function products()
    {
        return $this->belongsToMany('App\Models\ProductSupplierPrice', 'purchase_product')
                    ->withPivot('product_name')
                    ->withPivot('purchase_price')
                    ->withPivot('supplier_name')
                    ->withPivot('quantity');
    }

    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\PurchaseStatus');
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
        Log::info($this->type);

        return [
            // 2 is for 'return'
            'type' => $this->status_id == 2 ?
                      PurchaseRepository::$returnTypes[$this->type] :
                      PurchaseRepository::$purchaseTypes[$this->type],
            'paid' => $this->paid ? '<i class="fa fa-check" aria-hidden="true"></i>' : '',
            'shipped' => $this->shipped ? '<i class="fa fa-check" aria-hidden="true"></i>' : '',
            'status' => $this->status->name,
        ];
    }

    public function getPurchaseIsDirtyAttribute(){
        $dirtyFields = $this->getDirty();
        // grand total can change if products info changes
        unset($dirtyFields['total']);
        return empty($dirtyFields) ? false : true;
    }

    public function getCreatedAtFormattedAttribute(){
        return formatDate($this->created_at);
    }

    /**
     * Scopes
     */
    public function scopeInit($query, $typeFilter)
    {
        $query = $query->select(
            'purchases.*',
            'os.name as status_name'
        )
        ->join('order_statuses AS os', 'purchases.status_id', '=', 'os.id');

        // filter
        switch ($typeFilter) {
            case 'purchases':
                $query->whereIn('status_id', ['1', '3']);
                break;
            case 'returns':
                $query->where('status_id', '2');
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
                  // fix datetime MySQL bug for 'like'
                  ->orWhere(DB::raw("CAST(purchases.created_at AS CHAR)"), 'LIKE', "%{$search}%")
                  ->orWhere('total', 'LIKE', "%{$search}%")
                  ->orWhere('os.name', 'LIKE', "%{$search}%");
        });

        return $query;
    }
}
