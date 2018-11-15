<?php

namespace App\Models;

use Gate;
use Log;
use Hash;

class UserRepository
{
    static $columns = [
        0 => ['checkbox'],
        1 => ['email', 'E-mail'],
        2 => ['surname', 'Фамилия'],
        3 => ['name', 'Имя'],
        4 => ['role_id', 'Роль'],
        5 => ['actions', 'Действия'],
    ];

    public static function totals($input = []) {
        $total = User::all()->count();

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
        return static::getData(config('items_per_page'), 0, 'email', 'asc');
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
        $results = User::orderBy($orderBy, $order)
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
            $newRow[] = '<a href="#" class="btn-item-edit" title="Редактировать">' . $row->email . '</a>';
            $newRow[] = $row->surname;
            $newRow[] = $row->name;
            $newRow[] = $row->role->name;

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

    public static function create($input)
    {
        $item = User::create([
            'name' => $input['name'],
            'surname' => $input['surname'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'role_id' => $input['role_id'],
            'photo' => $input['photo'],
        ]);

        return ['id' => $item->id];
    }

    public static function update($input)
    {
        $item = User::find($input['id']);
        $item->update([
            'name' => $input['name'],
            'surname' => $input['surname'],
            'email' => $input['email'],
            'photo' => $input['photo'],
            'role_id' => $input['role_id'],
        ]);

        if (isset($input['password']) && $input['password'])
            $item->update([ 'password' => Hash::make($input['password']) ]);

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
            if (Gate::allows('access', 'delete-records')) User::find($id)->delete();
        }
    }

}
