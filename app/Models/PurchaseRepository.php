<?php

namespace App\Models;

use DB;
use Log;
use Gate;
use App\Classes\Table;

class PurchaseRepository {

    static $columns = [
        0 => ['checkbox'],
        1 => ['serial', 'Номер документа'],
        2 => ['created_at', 'Дата'],
        3 => ['type', 'Тип'],
        4 => ['total', 'Сумма'],
        5 => ['paid', '<i class="fa fa-money"></i>'],
        6 => ['shipped', '<i class="fa fa-truck"></i>'],
        7 => ['status_name', 'Статус'],
        8 => ['actions', 'Действия'],
    ];

    static $historyColumns = [
        0 => ['type', 'Тип'],
        1 => ['paid', '<i class="fa fa-money"></i>'],
        2 => ['shipped', '<i class="fa fa-truck"></i>'],
        3 => ['status_name', 'Статус'],
    ];

    static $purchaseTypes = [
        'receipt' => 'Поступление от поставщика',
        'realization' => 'Прием на реализацию',
    ];

    static $returnTypes = [
        'receipt' => 'Возврат поставщику',
        'realization' => 'Возврат комитенту',
    ];

    static $typesShortnames = [
        'receipt' => 'Поступление',
        'realization' => 'Реализация',
        'return' => 'Возврат'
    ];

    public static function totals($input = []) {
        $typeFilter = isset($input['filter']) ? $input['filter'] : 'all';

        $total = Purchase::init($typeFilter)
                         ->get()
                         ->count();

        $search = isset($input['search']['value']) ? $input['search']['value'] : '';
        if ($search) {
            $filtered = Purchase::init($typeFilter)
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
        $typeFilter = $input['filter'];
        $search = $input['search']['value'];

        return static::getData($itemsPerPage, $offset, $orderBy, $order, $typeFilter, $search);
    }

    public static function getData($itemsPerPage, $offset, $orderBy, $order,
                                   $typeFilter = 'all', $search = '')
    {
        $results = Purchase::init($typeFilter);

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

            $purchaseTypeClass = $row->status_id == '2' ? 'btn-return-edit' : 'btn-purchase-edit';

            $newRow[] = '<input class="cb-select-item" data-item-id="' . $row->id . '" type="checkbox">';
            $newRow[] = '<a href="#" class="' . $purchaseTypeClass . '" title="Редактировать">' . $row->serial . '</a>';
            $newRow[] = $row->createdAtFormatted;
            $newRow[] = static::$typesShortnames[$row->type];
            $newRow[] = $row->total;
            $newRow[] = $row->paid ? '<i class="fa fa-check" aria-hidden="true"></i>' : '';
            $newRow[] = $row->shipped ? '<i class="fa fa-check" aria-hidden="true"></i>' : '';
            $newRow[] = $row->status_name;
            $newRow[] = static::actionButtonsHtml($row, true);

            $ret[] = $newRow;
        }

        return $ret;
    }

    public static function selectSearch($search) {
        $results = Purchase::where('serial', 'LIKE', "%{$search}%")
                           ->get();

        return $results->map(function ($item) {
            return [
                'id' => $item->id,          // standard Select2 response field
                'text' => $item->serial,    // standard Select2 response field
            ];
        });
    }

    public static function create($input)
    {
        DB::transaction(function () use ($input) {
            // 1 - 'new', 2 - 'return', 3 - 'complete'
            $status_id = $input['purchase_or_return'] == 'purchase' ? 1 : 2;

            $GLOBALS['purchase'] = $purchase = Purchase::create([
                'type' => $input['type'],
                'total' => $input['total'],
                'status_id' => $status_id
            ]);

            $purchase->serial = str_pad($purchase->id, 8, "0", STR_PAD_LEFT);
            $purchase->save();

            // created_at_formatted
            $purchase->created_at_formatted = $purchase->created_at_formatted;
            $purchase->status_name = $purchase->status->name;

            if (isset($input['products'])) {
                foreach ($input['products'] as $product) {
                    $purchase->products()->attach($product['product_supplier_price_id'], [
                        'product_name' => $product['product_name'],
                        'supplier_name' => $product['supplier_name'],
                        'purchase_price' => $product['purchase_price'],
                        'quantity' => $product['quantity'],
                    ]);
                }
            }

            // history
            HistoryRepository::purchaseCreated($purchase);
        });

        return $GLOBALS['purchase'];
    }

    public static function show($id)
    {
        $purchase = Purchase::find($id);
        $item = $purchase->toArray();

        $item['status_name'] = $purchase->status->name;
        $item['created_at_formatted'] = $purchase->createdAtFormatted;

        $item['products'] = PurchaseProductRepository::purchaseProducts($id);
        $item['action_buttons_html'] = static::actionButtonsHtml($purchase);

        return $item;
    }

    public static function update($input)
    {
        DB::transaction(function () use ($input) {
            $purchase = Purchase::find($input['id']);

            $purchase->type = $input['type'];
            $purchase->total = $input['total'];
            $purchaseIsDirty = $purchase->purchaseIsDirty;

            $products = [];
            foreach ($input['products'] as $product) {
                $products[$product['product_supplier_price_id']] = [
                    'product_name' => $product['product_name'],
                    'supplier_name' => $product['supplier_name'],
                    'purchase_price' => $product['purchase_price'],
                    'quantity' => $product['quantity'],
                ];
            }

            $purchaseProductsAreDirty = PurchaseProductRepository::productsAreDirty($purchase, $products);
            $purchase->products()->sync($products);

            // write to History
            HistoryRepository::purchaseUpdated($purchase, $purchaseIsDirty, $purchaseProductsAreDirty);
        });
    }

    /**
     * Actions
     */
    public static function action($input)
    {
        $purchasesIds = $input['ids'];
        if (! is_array($purchasesIds)) $purchasesIds = [$purchasesIds];

        switch ($input['action']) {
            case 'paid-cash':
                static::actionPaid($purchasesIds, 'cash');
                break;
            case 'paid-cashless':
                static::actionPaid($purchasesIds, 'account');
                break;
            case 'shipped':
                static::actionShipped($purchasesIds);
                break;
            case 'cancelled':
                static::actionCancelled($purchasesIds);
                break;
            case 'delete':
                static::actionDelete($purchasesIds);
                break;
        }

        // return html for action buttons refresh in popup
        if (count($purchasesIds) == 1 && $input['action'] != 'delete')
            return static::actionButtonsHtml(Purchase::find($purchasesIds[0]));
    }

    public static function actionPaid($purchasesIds, $moneyType)
    {
        foreach ($purchasesIds as $purchaseId) {
            DB::transaction(function () use ($purchaseId, $moneyType) {
                $purchase = Purchase::find($purchaseId);

                // create
                // if 'paid' flag isn't set, 'money' record can not exist also
                if (! $purchase->paid) {
                    MoneyRepository::create([
                        'record_type' => 'operational',
                        'money_type' => $moneyType,
                        'income_expense_item_id' => 1,
                        'contractor_id' => $purchase->supplier_id,
                        'contractor_type' => 'App\Models\Supplier',
                        'base_id' => $purchase->id,
                        'base_type' => 'App\Models\Purchase',
                        'comment' => '',
                        'total' => $purchase->total,
                    ]);

                    $purchase->update(['paid' => 1]);

                    // todo history
                }

                // cancel
                else {
                    Money::where('base_id', $purchase->id)
                        ->where('base_type', 'App\Models\Purchase')
                        ->delete();

                    $purchase->update(['paid' => 0]);

                    // todo history
                }
            });
        }
    }

    public static function actionShipped($purchasesIds)
    {
        foreach ($purchasesIds as $purchaseId) {
            DB::transaction(function () use ($purchaseId) {

                $purchase = Purchase::find($purchaseId);

                // create
                if (! $purchase->shipped) {
                    foreach ($purchase->products as $purchaseProduct) {
                        // change product quantity
                        $purchaseProduct->product->{'quantity_' . $purchase->type} +=
                            $purchaseProduct->pivot->quantity;
                        $purchaseProduct->product->save();

                        // write to history
                        // todo: I don't know what this block of code does at this moment
                        /*
                        HistoryRepository::productQuantityUpdated(
                            $purchaseProduct->product, 'Закупка', $purchase->serial,
                            static::$typesForPurchase[$purchase->type],
                            $purchaseProduct->pivot->quantity
                        );
                        */
                    }

                    $purchase->update(['shipped' => 1]);
                }

                // cancel
                else {
                    foreach ($purchase->products as $purchaseProduct) {
                        // change product quantity
                        $purchaseProduct->product->{'quantity_' . $purchase->type} -=
                            $purchaseProduct->pivot->quantity;
                        $purchaseProduct->product->save();

                        // write to history
                        // todo: I don't know what this block of code does at this moment
                        /*
                        HistoryRepository::productQuantityUpdated(
                            $purchaseProduct->product, 'Закупка - отмена', $purchase->serial,
                            static::$typesForPurchase[$purchase->type],
                            - $purchaseProduct->pivot->quantity
                        );
                        */
                    }

                    $purchase->update(['shipped' => 0]);
                }

            });
        }
    }

    public static function actionCancelled($purchase)
    {
        // $purchase->delete();
    }

    public static function actionDelete($purchasesIds)
    {
        // todo
        foreach ($purchasesIds as $purchaseId) {
            DB::transaction(function () use ($purchaseId) {
                $purchase = Purchase::find($purchaseId);

                // todo all logic before deletion
                // ...

                if (Gate::allows('access', 'delete-records')) $purchase->delete();
            });
        }
    }

    /**
     * action buttons html
     * @param $purchase: purchase model
     * @param $forRow: actions in 'row' and 'popup' have slightly different html
     * @return html
     */
    public static function actionButtonsHtml($purchase, $forRow = false){
        $additionalButtonClass = $forRow ? 'btn-xs' : '';
        $actionClasses = static::getActionButtonsClasses($purchase);
        $purchaseTypeClass = $purchase->status_id == '2' ? 'btn-return-edit' : 'btn-purchase-edit';

        $html = $forRow ?
            '<button class="btn btn-primary btn-xs ' . $purchaseTypeClass . '" title="Редактировать"><i class="fa fa-edit"></i></button>
            ' :
            '';

        $html .=
            '<button class="btn btn-success ' . $additionalButtonClass . ' ' . $actionClasses['paidCashClass'] . '" data-action="paid-cash" title="Оплачен"><i class="fa fa-money"></i></button>
            <button class="btn btn-info ' . $additionalButtonClass . ' ' . $actionClasses['paidCashlessClass'] . '" data-action="paid-cashless" title="Оплачен б/н"><i class="fa fa-credit-card"></i></button>
            <button class="btn btn-default ' . $additionalButtonClass . ' ' . $actionClasses['shippedClass'] . '" data-action="shipped" title="Доставлен"><i class="fa fa-truck"></i></button>
            <button class="btn btn-warning ' . $additionalButtonClass . '" data-action="cancelled" title="Отменен"><i class="fa fa-minus-circle"></i></button>
            ';

        if (Gate::allows('access', 'delete-records')) $html .=
            '<button class="btn btn-danger ' . $additionalButtonClass . '" data-action="delete" title="Удалить"><i class="fa fa-trash"></i></button>';

        return $html;
    }

    // helper function used in getData() and getActionButtonsHtml()
    public static function getActionButtonsClasses($purchase) {
        $money = $purchase->money;

        if ($money) {
            $paidCashClass = $money->money_type == 'cash' ? 'crossed' : 'disabled';
            $paidCashlessClass = $money->money_type == 'account'? 'crossed' : 'disabled';
        } else {
            $paidCashClass = $paidCashlessClass = '';
        }

        $shippedClass = $purchase->shipped ? 'crossed' : '';

        return [
            'paidCashClass' => $paidCashClass,
            'paidCashlessClass' => $paidCashlessClass,
            'shippedClass' => $shippedClass
        ];
    }

    /**
     * History
     */
    public static function history($id)
    {
        $history = History::where('table', 'purchases')
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

            // purchase info row
            if ($record->changes['purchase']) $ret .=
                Table::table(static::$historyColumns, [$record->changes['purchase']]);

            // purchase products (psp's)
            if ($record->changes['products']) $ret .=
                Table::table(PurchaseProductRepository::$historyColumns, $record->changes['products']);

            $i++;
        }

        return $ret;
    }

}