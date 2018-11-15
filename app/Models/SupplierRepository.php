<?php

namespace App\Models;

use Log;
use Gate;

class SupplierRepository
{
    static $columns = [
        0 => ['checkbox'],
        1 => ['name', 'Название'],
        2 => ['phone', 'Телефон'],
        3 => ['email', 'E-mail'],
        4 => ['contact_person', 'Контактное лицо'],
        5 => ['actions', 'Действия'],
    ];

    public static function totals($input = []) {
        $total = Supplier::all()->count();

        $search = isset($input['search']['value']) ? $input['search']['value'] : '';
        if ($search) {
            $filtered = Supplier::search($search)
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
            $results = Supplier::search($search)
                               ->orderBy($orderBy, $order);
        } else {
            $results = Supplier::orderBy($orderBy, $order);
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
            $newRow[] = '<a href="#" class="btn-item-edit" title="Редактировать">' . $row->name . '</a>';
            $newRow[] = $row->phone;
            $newRow[] = $row->email;
            $newRow[] = $row->contact_person;

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
        $results = Supplier::where('name', 'LIKE', "%{$search}%")
                           ->get();

        return $results->map(function ($item) {
            return [
                'id' => $item->id,      // standard Select2 response field
                'text' => $item->name,  // standard Select2 response field

                'name' => $item->name,  // double in order to use original field name
            ];
        });
    }

    public static function create($input)
    {
        $item = Supplier::create([
            'name' => $input['name'],
            'phone' => $input['phone'],
            'email' => $input['email'],
            'contact_person' => $input['contact_person'],
        ]);

        return ['id' => $item->id];
    }

    public static function update($input)
    {
        $item = Supplier::find($input['id']);
        $item->update([
            'name' => $input['name'],
            'phone' => $input['phone'],
            'email' => $input['email'],
            'contact_person' => $input['contact_person'],
        ]);

        return ['id' => $item->id];
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
            if (Gate::allows('access', 'delete-records')) Supplier::find($id)->delete();
        }
    }
}
