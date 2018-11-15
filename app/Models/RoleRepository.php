<?php

namespace App\Models;

use Gate;
use Log;

class RoleRepository
{
    static $columns = [
        0 => ['checkbox'],
        1 => ['name', 'Название'],
        2 => ['actions', 'Действия'],
    ];

    static $otherCapabilities = [
        ['slug' => 'delete-records', 'name' => 'Удалять записи'],
        ['slug' => 'watch-history', 'name' => 'Смотреть историю'],
    ];

    public static function menuCapabilities(\Menu $menu)
    {
        return $menu->actualMenuItems;
    }

    public static function totals() {
        $total = Role::all()->count();

        return [
            'total' => $total,
            'filtered' => $total
        ];
    }

    public static function firstPage() {
        return static::getData(config('items_per_page'), 0, 'name', 'asc');
    }

    public static function search($input) {
        $itemsPerPage = $input['length'];
        $offset = $input['start'];
        $orderBy = 'name';
        $order = 'asc';

        return static::getData($itemsPerPage, $offset, $orderBy, $order);
    }

    public static function getData($itemsPerPage, $offset, $orderBy, $order)
    {
        $results = Role::orderBy($orderBy, $order)
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
        $item = Role::create([
            'name' => $input['name'],
            'capabilities' => $input['capabilities']
        ]);

        return ['id' => $item->id];
    }

    public static function update($input)
    {
        $item = Role::find($input['id']);
        $item->update([
            'name' => $input['name'],
            'capabilities' => $input['capabilities']
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
                return static::actionDelete($itemsIds);
                break;
        }
    }

    public static function actionDelete($itemsIds)
    {
        foreach ($itemsIds as $id) {
            if (Gate::allows('access', 'delete-records')) {
                $role = Role::find($id);

                if ($role->users->count())
                    return [
                        'status' => 'fail',
                        'message' => "У роли '" . $role->name . "' есть пользователи!"
                    ];

                $role->delete();
            }
        }

        return ['status' => 'success'];
    }
}
