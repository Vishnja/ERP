<?php

namespace App\Models;

use Log;

class ProductSupplierPriceRepository
{
    static $columns = [
        0 => ['checkbox'],
        1 => ['product_name', 'Наименование'],
        2 => ['supplier_name', 'Поставщик'],
        3 => ['purchase_price', 'Закупочная Цена'],
        4 => ['price', 'Цена'],
        5 => ['actions', 'Действия'],
    ];

    public static function totals($input = []) {
        $total = ProductSupplierPrice::all()->count();

        $search = isset($input['search']['value']) ? $input['search']['value'] : '';
        if ($search) {
            $filtered = ProductSupplierPrice::init()
                                            ->search($search)
                                            ->get()
                                            ->count();
        } else {
            $filtered = $total;
        }

        return [
            'total' => $total,
            'filtered' => $filtered
        ];
    }

    public static function firstPage() {
        return static::getData(config('items_per_page'), 0, 'product_name', 'asc');
    }

    public static function search($input) {
        $itemsPerPage = $input['length'];
        $offset = $input['start'];
        $orderBy = self::$columns[ $input['order'][0]['column'] ][0];
        $order = $input['order'][0]['dir'];
        $search = $input['search']['value'];

        return static::getData($itemsPerPage, $offset, $orderBy, $order, $search);
    }

    public static function getData($itemsPerPage, $offset, $orderBy, $order, $search = '')
    {
        $results = ProductSupplierPrice::init();

        // search
        if ($search) $results = $results->search($search);

        $results = $results->orderBy($orderBy, $order)
                           ->limit($itemsPerPage)
                           ->skip($offset)
                           ->get();

        /**
         * Generate array
         */
        $ret = [];
        foreach ($results as $row) {
            $newRow = [];

            $newRow[] = '<input class="cb-select-item" data-item-id="' . $row->id . '" type="checkbox">';
            $newRow[] = '<a href="#" class="btn-item-edit" title="Редактировать">' . $row->product_name . '</a>';
            $newRow[] = $row->supplier_name;
            $newRow[] = $row->purchase_price;
            $newRow[] = $row->price;

            // actions
            $newRow[] =
                '<a href="#" class="btn btn-primary btn-xs btn-item-edit" title="Редактировать">
                    <i class="fa fa-edit"></i></a>
                 <a href="#" class="btn btn-danger btn-xs btn-item-delete" title="Удалить">
                    <i class="fa fa-trash"></i></a>
                 ';

            $ret[] = $newRow;
        }

        return $ret;
    }

    public static function selectSearch($search) {
        $results = ProductSupplierPrice::select(
            'product_supplier_price.*',
            'p.id as product_id', 'p.name as product_name', 'p.vendor_code as vendor_code', 'p.description as description', 'p.price as product_price',
            's.name as supplier_name'
        )
        ->join('products AS p', 'product_supplier_price.product_id', '=', 'p.id')
        ->join('suppliers AS s', 'product_supplier_price.supplier_id', '=', 's.id')
        ->where('p.name', 'LIKE', "%{$search}%")
        ->orWhere('p.vendor_code', 'LIKE', "%{$search}%")
        ->orWhere('s.name', 'LIKE', "%{$search}%")
        ->get();

        return $results->map(function ($item) {
            return [
                // standard Select2 response field (product supplier price id)
                'id' => $item->id,
                // standard Select2 response field
                'text' => excerpt($item->product_name, 45) . ' - ' .
                          excerpt($item->vendor_code, 16) . ' - ' .
                          excerpt($item->description, 19) . ' - ' .
                          excerpt($item->supplier_name, 25),

                // for select in orders
                'product_supplier_price_id' => $item->id,
                // for select in orders
                'product_and_psp_exists' => true,
                // for select in orders
                'product_id' => $item->product_id,
                // double product_name to use original field name
                'product_name' => $item->product_name,
                'supplier_name' => $item->supplier_name,
                'vendor_code' => $item->vendor_code,
                'description' => $item->description,
                'purchase_price' => $item->purchase_price,
                // for select in orders
                'price' => $item->product_price,
                'quantity' => 1
            ];
        });
    }

    public static function create($input)
    {
        $item = ProductSupplierPrice::create([
            'product_id' => $input['product_id'],
            'supplier_id' => $input['supplier_id'],
            'purchase_price' => $input['purchase_price'],
        ]);

        return ['id' => $item->id];
    }

    public static function update($input)
    {
        $item = ProductSupplierPrice::find($input['id']);
        $item->update([
            'product_id' => $input['product_id'],
            'supplier_id' => $input['supplier_id'],
            'purchase_price' => $input['purchase_price'],
        ]);

        return ['id' => $item->id];
    }
}
