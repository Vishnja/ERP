<?php

namespace App\Models;

use Log;

class IncomeExpenseItemRepository
{
    static $columns = [
        0 => ['checkbox'],
        1 => ['name', 'Название'],
        2 => ['type', 'Тип'],
        3 => ['actions', 'Действия'],
    ];

    static $type = [
        'income' => 'Приход',
        'expense' => 'Расход'
    ];

    public static function totals() {
        $total = IncomeExpenseItem::all()->count();

        return [
            'total' => $total,
            'filtered' => $total
        ];
    }

    public static function firstPage() {
        return static::getData(config('items_per_page'), 0, 'name', 'desc');
    }

    public static function search($input) {
        $itemsPerPage = $input['length'];
        $offset = $input['start'];
        $orderBy = self::$columns[ $input['order'][0]['column'] ][0];
        $order = $input['order'][0]['dir'];

        return static::getData($itemsPerPage, $offset, $orderBy, $order);
    }

    public static function getData($itemsPerPage, $offset, $orderBy, $order)
    {
        $results = IncomeExpenseItem::orderBy($orderBy, $order)
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
            $newRow[] = '<a href="#" class="btn-item-edit" title="Редактировать">' . $row->name . '</a>';
            $newRow[] = static::$type[ $row->type ];

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
        $results = IncomeExpenseItem::where('name', 'LIKE', "%{$search}%")
                                    ->get();

        return $results->map(function ($item) {
            return ['id' => $item->id, 'text' => $item->name];
        });
    }

    public static function create($input)
    {
        $item = IncomeExpenseItem::create([
            'name' => $input['name'],
            'type' => $input['type'],
        ]);

        return ['id' => $item->id];
    }

    public static function update($input)
    {
        $item = IncomeExpenseItem::find($input['id']);
        $item->update([
            'name' => $input['name'],
            'type' => $input['type'],
        ]);

        return ['id' => $item->id];
    }
}
