<?php

namespace App\Models;

use DB;
use Log;
use Gate;

class BuyerRepository {

    static $columns = [
        0 => ['checkbox'],
        1 => ['name', 'Имя'],
        2 => ['surname', 'Фамилия'],
        3 => ['phone', 'Телефон'],
        4 => ['email', 'E-mail'],
        5 => ['city', 'Город'],
        6 => ['address', 'Адрес'],
        7 => ['NP_number', 'Номер НП'],
        8 => ['actions', 'Действия'],
    ];

    public static function totals($input = []) {
        $total = Buyer::all()->count();

        $search = isset($input['search']['value']) ? $input['search']['value'] : '';
        if ($search) {
            $filtered = Buyer::search($search)
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
            $results = Buyer::search($search)
                            ->orderBy($orderBy, $order);
        } else {
            $results = Buyer::orderBy($orderBy, $order);
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
            $newRow[] = $row->surname;
            $newRow[] = $row->phone;
            $newRow[] = $row->email;
            $newRow[] = $row->city;
            $newRow[] = $row->address;
            $newRow[] = $row->NP_number;

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
        $results = Buyer::where(DB::raw("CONCAT(buyers.surname, ' ', buyers.name)"), 'LIKE', "%{$search}%")
                        ->orWhere(DB::raw("CONCAT(buyers.name, ' ', buyers.surname)"), 'LIKE', "%{$search}%")
                        ->get();

        return $results->map(function ($item) {
            return ['id' => $item->id, 'text' => $item->fullname];
        });
    }

    public static function create($input)
    {
        $buyer = Buyer::create([
            'name' => $input['name'],
            'surname' => $input['surname'],
            'phone' => $input['phone'],
            'email' => $input['email'],
            'city' => $input['city'],
            'address' => $input['address'],
            'NP_number' => $input['NP_number'] ? $input['NP_number'] : null
        ]);

        // for loading into select2
        return ['id' => $buyer->id, 'text' => $buyer->fullname];
    }

    public static function update($input)
    {
        $buyer = Buyer::find($input['id']);
        $buyer->update([
            'name' => $input['name'],
            'surname' => $input['surname'],
            'phone' => $input['phone'],
            'email' => $input['email'],
            'city' => $input['city'],
            'address' => $input['address'],
            'NP_number' => $input['NP_number'] ? $input['NP_number'] : null
        ]);

        // for loading into select2
        return ['id' => $buyer->id, 'text' => $buyer->fullname];
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
            if (Gate::allows('access', 'delete-records')) Buyer::find($id)->delete();
        }
    }
}