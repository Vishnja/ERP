<?php

namespace App\Models;

use DB;
use Gate;
use Log;

class MoneyRepository
{
    static $columns = [
        0 => ['checkbox'],
        1 => ['serial_num', 'Номер документа'],             // field generated in Money::init()
        2 => ['created_at', 'Дата'],                        // field generated in Money::init()
        3 => ['record_type', 'Тип Записи'],
        4 => ['cash_type', 'Тип Оплаты'],
        5 => ['income_expense_item_name', 'Статья Прих. / Расх.'], // field generated in Money::init()
        6 => ['base_name', 'Основание'],                    // field generated in Money::init()
        7 => ['contractor_name', 'Контрагент'],             // field generated in Money::init()
        8 => ['actions', 'Действия'],

        // hidden columns
        9 => ['income_expense_item_type', 'Приход / Расход'], // hidden, field generated in Money::init()
        10 => ['status', 'Статус'],
    ];

    static $recordType = [
        'operational' => 'Операционная',
        'management' => 'Управленческая'
    ];

    static $moneyType = [
        'account' => 'Счет',
        'cash' => 'Касса'
    ];

    static $contractorType = [
        'App\\Models\\Buyer' => 'Покупатель',
        'App\\Models\\Supplier' => 'Поставщик'
    ];

    static $baseType = [
        'App\\Models\\Order' => 'Заказ',
        'App\\Models\\Purchase' => 'Закупка'
    ];

    static $status = [
        'active' => 'Активный',
        'cancelled' => 'Отмена'
    ];

    public static function totals($input = []) {
        // !!! default values should be the same as in getData default params !!!
        $recordTypeFilter = isset($input['record_type_filter']) ? $input['record_type_filter'] : 'all';
        $moneyTypeFilter = isset($input['money_type_filter']) ?  $input['money_type_filter'] : 'all';
        $statusFilter = isset($input['status_filter']) ? $input['status_filter'] :'all';

        $total = Money::init($recordTypeFilter, $moneyTypeFilter, $statusFilter)
                      ->get()
                      ->count();

        $search = isset($input['search']['value']) ? $input['search']['value'] : '';
        if ($search) {
            $filtered = Money::init($recordTypeFilter, $moneyTypeFilter, $statusFilter)
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
        return static::getData(config('items_per_page'), 0, 'created_at', 'desc');
    }

    public static function search($input) {
        $itemsPerPage = $input['length'];
        $offset = $input['start'];
        $orderBy = self::$columns[ $input['order'][0]['column'] ][0];
        $order = $input['order'][0]['dir'];
        $recordTypeFilter = $input['record_type_filter'];
        $moneyTypeFilter = $input['money_type_filter'];
        $statusFilter = $input['status_filter'];
        $search = $input['search']['value'];

        return static::getData(
            $itemsPerPage, $offset, $orderBy, $order,
            $recordTypeFilter, $moneyTypeFilter, $statusFilter,
            $search
        );
    }

    public static function getData(
        $itemsPerPage, $offset, $orderBy, $order,
        $recordTypeFilter = 'all', $moneyTypeFilter = 'all', $statusFilter = 'all',
        $search = ''
    ) {
        $results = Money::init($recordTypeFilter, $moneyTypeFilter, $statusFilter);

        // search
        if ($search) $results = $results->search($search);

        // sort order
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

            $newRow[0] = '<input class="cb-select-item" data-item-id="' . $row->id . '" type="checkbox">';
            $newRow[1] = '<a href="#" class="btn-item-edit" title="Редактировать">' . $row->serial . '</a>';
            $newRow[2] = $row->created_at->toDateTimeString();
            $newRow[3] = static::$recordType[$row->record_type];
            $newRow[4] = static::$moneyType[$row->money_type];
            $newRow[5] = $row->income_expense_item_name;
            $newRow[6] = $row->base_name;
            $newRow[7] = $row->contractor_name;
            $newRow[8] = static::actionButtonsHtml($row, true);

            // hidden columns for generating styles
            $newRow[9] = $row->income_expense_item_type;
            $newRow[10] = $row->status;

            $ret[] = $newRow;
        }

        return $ret;
    }

    public static function create($input)
    {
        // Money
        $item = Money::create([
            'record_type' => $input['record_type'],
            'money_type' => $input['money_type'],
            'income_expense_item_id' => $input['income_expense_item_id'],
            'contractor_id' => $input['contractor_id'],
            'contractor_type' => $input['contractor_type'],
            'base_id' => $input['base_id'],
            'base_type' => $input['base_type'],
            'comment' => $input['comment'],
            'total' => $input['total'],
            'status' => 'active',
        ]);

        $prefix = $item->incomeExpenseItem->type == 'income' ? 'I-' : 'O-';
        $item->serial = $prefix . str_pad($item->id, 8, "0", STR_PAD_LEFT);
        $item->save();

        // if 'operational' type - change related base record status
        if ($item->record_type == 'operational') {
            $item->base->paid = true;
            $item->base->save();
        }

        // additional fields
        $item->created_at_formatted = $item->created_at_formatted;
        $item->status_name = static::$status[$item->status];

        return $item;
    }

    public static function show($id)
    {
        $money = Money::find($id);
        $item = $money->toArray();
        $item['created_at_formatted'] = $money->createdAtFormatted;
        $item['status_name'] = static::$status[$money->status];

        // contractor
        if ($money['record_type'] == 'operational') {
            $contractor = $money->contractor;
            $contractorName = $money->contractor_type == 'App\\Models\\Buyer' ?
                              $contractor->fullname : $contractor->name;
            $item['contractor'] = ['id' => $contractor->id, 'text' => $contractorName];
        } else {
            $item['contractor'] = ['id' => null, 'text' => null];
        }

        // base
        if ($money['record_type'] == 'operational') {
            $base = $money->base;
            $item['base'] = ['id' => $base->id, 'text' => $base->serial];
        } else {
            $item['base'] = ['id' => null, 'text' => null];
        }

        $item['action_buttons_html'] = static::actionButtonsHtml($money);

        return $item;
    }

    public static function update($input)
    {
        $item = Money::find($input['id']);

        $item->update([
            'record_type' => $input['record_type'],
            'money_type' => $input['money_type'],
            'income_expense_item_id' => $input['income_expense_item_id'],
            'contractor_id' => $input['contractor_id'],
            'contractor_type' => $input['contractor_type'],
            'base_id' => $input['base_id'],
            'base_type' => $input['base_type'],
            'comment' => $input['comment'],
            'total' => $input['total'],
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
            case 'cancelled':
                static::actionCancelled($itemsIds);
                break;
            case 'delete':
                static::actionDelete($itemsIds);
                break;
        }
    }

    public static function actionCancelled($moneyIds)
    {
        foreach ($moneyIds as $moneyId) {
            DB::transaction(function () use ($moneyId) {

                $money = Money::find($moneyId);

                // ...
            });
        }
    }

    public static function actionDelete($moneyIds)
    {
        foreach ($moneyIds as $moneyId) {
            DB::transaction(function () use ($moneyId) {

                $money = Money::find($moneyId)->delete();

                // ...
            });
        }
    }

    /**
     * action buttons html
     * @param $money: money model
     * @param $forRow: actions in 'row' and 'popup' have slightly different html
     * @return html
     */
    public static function actionButtonsHtml($money, $forRow = false){
        $additionalButtonClass = $forRow ? 'btn-xs' : '';
        $cancelledClass = $money->status == 'cancelled' ? 'crossed' : '';

        $html = $forRow ?
            '<button class="btn btn-primary btn-xs btn-item-edit" title="Редактировать"><i class="fa fa-edit"></i></button>
            ' :
            '';

        $html .=
            '<button class="btn btn-warning ' . $additionalButtonClass . ' ' . $cancelledClass . '" data-action="cancelled" title="Отменен"><i class="fa fa-minus-circle"></i></button>
            ';

        if (Gate::allows('access', 'delete-records')) $html .=
            '<button class="btn btn-danger ' . $additionalButtonClass . '" data-action="delete" title="Удалить"><i class="fa fa-trash"></i></button>';

        return $html;
    }


    /**
     * History
     */


}
