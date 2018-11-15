<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Money extends Model
{
    protected $table = 'money';

    /**
     * Mass assign
     */
    protected $guarded = [];

    /**
     * Relationships
     */
    public function incomeExpenseItem()
    {
        return $this->belongsTo('App\Models\IncomeExpenseItem');
    }

    public function contractor()
    {
        return $this->morphTo();
    }

    public function base()
    {
        return $this->morphTo();
    }

    /**
     * Getters
     */
    public function getCreatedAtFormattedAttribute(){
        return formatDate($this->created_at);
    }

    /**
     * Scopes
     */
    public function scopeInit($query, $recordTypeFilter, $moneyTypeFilter, $statusFilter)
    {
        $query = $query->select(
            'money.*',
            // to make field 'serial' orderable
            DB::raw('SUBSTRING(money.serial FROM 3) as serial_num'),
            'i.name as income_expense_item_name',
            DB::raw("CASE contractor_type
                        WHEN 'App\\\\Models\\\\Buyer' THEN CONCAT(b.surname, ' ', b.name)
                        WHEN 'App\\\\Models\\\\Supplier' THEN s.name
                        ELSE NULL
                     END as contractor_name"),
            DB::raw("CASE base_type
                        WHEN 'App\\\\Models\\\\Order' THEN 'Заказ'
                        WHEN 'App\\\\Models\\\\Purchase' THEN 'Закупка'
                        ELSE NULL
                     END as base_name"),
            // ambiguous column
            'money.money_type as money_type',
            'i.type as income_expense_item_type'
        )
        // income expense items
        ->join('income_expense_items AS i', 'money.income_expense_item_id', '=', 'i.id')
        // contractor
        ->leftJoin('buyers AS b', 'money.contractor_id', '=', 'b.id')
        ->leftJoin('suppliers AS s', 'money.contractor_id', '=', 's.id')
        // base
        ->leftJoin('orders AS o', 'money.base_id', '=', 'o.id')
        ->leftJoin('purchases AS p', 'money.base_id', '=', 'p.id');

        // record type filter
        switch ($recordTypeFilter) {
            case 'operational':
                $query->where('money.record_type', 'operational');
                break;
            case 'management':
                $query->where('money.record_type', 'management');
                break;
            case 'all':
                break;
        }

        // money type filter
        switch ($moneyTypeFilter) {
            case 'account':
                $query->where('money.money_type', 'account');
                break;
            case 'money':
                $query->where('money.money_type', 'cash');
                break;
            case 'all':
                break;
        }

        // money type filter
        switch ($statusFilter) {
            case 'active':
                $query->where('money.status', 'active');
                break;
            case 'cancelled':
                $query->where('money.status', 'cancelled');
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
            $query->where('money.serial', 'LIKE', "%{$search}%")
                  ->orWhere('i.name', 'LIKE', "%{$search}%")

                  // Buyer Surname Name
                  ->orWhere( function($query) use ($search){
                        $query->where(DB::raw("CONCAT(b.surname, ' ', b.name)"), 'LIKE', "%{$search}%")
                              ->where('money.contractor_type', 'App\\Models\\Buyer');
                        return $query;
                  })
                  // Buyer Name Surname
                  ->orWhere( function($query) use ($search){
                        $query->where(DB::raw("CONCAT(b.name, ' ', b.surname)"), 'LIKE', "%{$search}%")
                              ->where('money.contractor_type', 'App\\Models\\Buyer');
                        return $query;
                  })
                  // Supplier
                  ->orWhere( function($query) use ($search){
                        $query->where('s.name', 'LIKE', "%{$search}%")
                              ->where('money.contractor_type', 'App\\Models\\Supplier');
                        return $query;
                  })

                  // fix datetime MySQL bug for 'like'
                  ->orWhere(DB::raw("CAST(money.created_at AS CHAR)"), 'LIKE', "%{$search}%");
        });

        return $query;
    }
}
