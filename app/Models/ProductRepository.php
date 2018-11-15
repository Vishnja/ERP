<?php

namespace App\Models;

use DB;
use Log;
use Gate;
use App\Classes\Table;

class ProductRepository
{
    static $columns = [
        0 => ['checkbox'],
        1 => ['name', 'Наименование'],
        2 => ['description', 'Характеристика'],
        3 => ['vendor_code', 'Артикул'],
        4 => ['price', 'Цена'],
        5 => ['quantity_receipt', 'Собст.'],
        6 => ['quantity_realization', 'Реал.'],
        7 => ['actions', 'Действия'],
    ];

    static $historyColumns = [
        0 => ['name', 'Наименование'],
        1 => ['description', 'Характеристика'],
        2 => ['vendor_code', 'Артикул'],
        3 => ['price', 'Цена'],
    ];

    static $historyQuantityChangeColumns = [
        0 => ['base', 'Основание'],
        1 => ['serial', 'Номер документа'],
        2 => ['quantity_type', 'Тип'],
        3 => ['quantity', 'Кол-во'],
    ];

    public static function totals($input = []) {
        $total = Product::all()->count();

        $search = isset($input['search']['value']) ? $input['search']['value'] : '';
        if ($search) {
            $filtered = Product::search($search)
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
        return static::getData(config('items_per_page'), 0, 'name', 'asc');
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
        // search
        if ($search) {
            $results = Product::search($search)
                              ->orderBy($orderBy, $order);
        } else {
            $results = Product::orderBy($orderBy, $order);
        }

        $results = $results->limit($itemsPerPage)
                           ->skip($offset)
                           ->get();

        /**
         * Generate array
         */
        $ret = [];
        foreach ($results as $row) {
            $newRow = [];

            $newRow[] = '<input class="cb-select-item" data-item-id="' . $row->id . '" type="checkbox">';
            $newRow[] = '<a href="#" class="btn-item-edit" title="' . e($row->name) . '">' . excerpt($row->name, 70) . '</a>';
            $newRow[] = '<span title="' . e($row->description) . '">' . excerpt($row->description, 60) . '</span>';
            $newRow[] = '<span title="' . e($row->vendor_code) . '">' . excerpt($row->vendor_code, 50) . '</span>';
            $newRow[] = $row->price;
            $newRow[] = $row->quantity_receipt;
            $newRow[] = $row->quantity_realization;

            // actions
            $actions =
                '<a href="#" class="btn btn-primary btn-xs btn-item-edit" title="Редактировать">
                    <i class="fa fa-edit"></i></a>
                ';

            if (Gate::allows('access', 'delete-records')) $actions .=
                '<a href="#" class="btn btn-danger btn-xs" data-action="delete" title="Удалить">
                    <i class="fa fa-trash"></i></a>';

            $newRow[] = $actions;

            $ret[] = $newRow;
        }

        return $ret;
    }

    public static function selectSearch($search) {
        $results = Product::where('name', 'LIKE', "%{$search}%")
                          ->orWhere('vendor_code', 'LIKE', "%{$search}%")
                          ->get();

        return $results->map(function ($item) {
            return [
                'id' => $item->id,      // standard Select2 response field
                'text' => $item->name,  // standard Select2 response field

                'name' => $item->name,  // double in order to use original field name
                'vendor_code' => $item->vendor_code,
                'description' => $item->description,
                'price' => $item->price,
                'quantity' => 1
            ];
        });
    }

    public static function create($input)
    {
        DB::transaction(function () use ($input) {
            $GLOBALS['item'] = Product::create([
                'name' => $input['name'],
                'vendor_code' => $input['vendor_code'],
                'description' => $input['description'],
                'price' => $input['price'],
                'quantity_receipt' => 0,
                'quantity_realization' => 0,
            ]);

            // history
            HistoryRepository::productCreated($GLOBALS['item']);
        });

        return ['id' => $GLOBALS['item']->id];
    }

    public static function update($input)
    {
        DB::transaction(function () use ($input) {
            $GLOBALS['item'] = $item = Product::find($input['id']);

            $item->name = $input['name'];
            $item->vendor_code = $input['vendor_code'];
            $item->description = $input['description'];
            $item->price = $input['price'];

            // history
            if ($item->isDirty()) HistoryRepository::productGeneralInfoUpdated($item);
            $item->save();
        });

        return ['id' => $GLOBALS['item']->id];
    }

    public static function history($id)
    {
        $history = History::where('table', 'products')
                          ->where('item_id', $id)
                          ->get();

        $ret = '';
        $i = 1;
        foreach ($history as $record) {
            // header row
            $action = HistoryRepository::$action[$record->action];
            $ret .= "<p>{$i}. Действие: {$action}. " .
                    "Пользователь: {$record->user}. " .
                    "Дата/время: {$record->created_at}.</p>";

            // general info change
            if (isset($record->changes['general_info'])) $ret .=
                "<table>" .
                    "<thead>" .
                        Table::head(static::$historyColumns) .
                    "</thead>" .
                    "<tbody>" .
                        Table::body([ $record->changes['general_info'] ]) .
                    "</tbody>" .
                "</table>";

            // product quantity change with linked documents
            if (isset($record->changes['quantity'])) $ret .=
                "<table>" .
                    "<thead>" .
                        Table::head(static::$historyQuantityChangeColumns) .
                    "</thead>" .
                    "<tbody>" .
                        Table::body([ $record->changes['quantity'] ]) .
                    "</tbody>" .
                "</table>";

            $i++;
        }

        return $ret;
    }

    /**
     * Actions
     */
    public static function action($input)
    {
        $itemsIds = $input['ids'];
        if (! is_array($itemsIds)) $itemsIds = [$itemsIds];

        switch ($input['action']) {
            case 'delete':
                static::actionDelete($itemsIds);
                break;
        }
    }

    public static function actionDelete($itemsIds)
    {
        foreach ($itemsIds as $id) {
            if (Gate::allows('access', 'delete-records')) Product::find($id)->delete();
        }
    }
}
