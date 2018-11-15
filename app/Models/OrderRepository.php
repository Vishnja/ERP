<?php

namespace App\Models;

use App\Classes\Table;
use DB;
use Log;
use Gate;


class OrderRepository {

    static $columns = [
        0 => ['checkbox'],
        1 => ['serial_num', 'Номер документа'],         // field generated in Order::init()
        2 => ['created_at', 'Дата'],
        3 => ['buyer_fullname', 'Покупатель'],          // field generated in Order::init()
        4 => ['payment_method_id', 'Способ оплаты'],
        5 => ['shipping_method_id', 'Способ доставки'],
        6 => ['paid', '<i class="fa fa-money" title="Оплачен"></i>'],
        7 => ['shipped', '<i class="fa fa-truck" title="Доставлен"></i>'],
        8 => ['status_name', 'Статус'],
        9 => ['actions', 'Действия'],
    ];

    static $historyColumns = [
        0 => ['buyer', 'Покупатель'],
        1 => ['payment_method', '<span title="Способ Оплаты">Опл.</span>'],
        2 => ['shipping_method', '<span title="Способ Доставки">Дост.</span>'],
        3 => ['NP_city_store', '<span title="Город Склад НП">Г. Скл.</span>'],
        4 => ['shipping_cost', '<span title="Стоимость Доставки">Дост.</span>'],

        5 => ['discount', 'Скидка'],

        6 => ['paid', '<i class="fa fa-money" title="Оплачен"></i>'],
        7 => ['shipped', '<i class="fa fa-truck" title="Доставлен"></i>'],
        8 => ['status', 'Статус'],

    ];

    public static function totals($input = []) {
        $statusFilter = isset($input['filter']) ? $input['filter'] : 'open';

        $total = Order::init($statusFilter)
                      ->get()
                      ->count();

        $search = isset($input['search']['value']) ? $input['search']['value'] : '';
        if ($search) {
            $filtered = Order::init($statusFilter)
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
        $statusFilter = $input['filter'];
        $search = $input['search']['value'];

        return static::getData($itemsPerPage, $offset, $orderBy, $order, $statusFilter, $search);
    }

    public static function getData($itemsPerPage, $offset, $orderBy, $order,
                                   $statusFilter = 'open', $search = '')
    {
        $results = Order::init($statusFilter);

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

            $newRow[] = '<input class="cb-select-item" data-item-id="' . $row->id . '" type="checkbox">';
            $newRow[] = '<a href="#" class="btn-order-edit" title="Редактировать">' . $row->serial . '</a>';
            $newRow[] = $row->createdAtFormatted;
            $newRow[] = $row->buyer_fullname;
            $newRow[] = $row->pm_name;
            $newRow[] = $row->sm_name;
            $newRow[] = $row->paid ? '<i class="fa fa-check" aria-hidden="true"></i>' : '';
            $newRow[] = $row->shipped ? '<i class="fa fa-check" aria-hidden="true"></i>' : '';
            $newRow[] = $row->status_name;
            $newRow[] = static::actionButtonsHtml($row, true);

            $ret[] = $newRow;
        }

        return $ret;
    }

    public static function selectSearch($search) {
        $results = Order::where('serial', 'LIKE', "%{$search}%")
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
            $GLOBALS['order'] = $order = Order::create([
                'buyer_id' => $input['buyer_id'],
                'buyer_fullname' => Buyer::find($input['buyer_id'])->fullname,
                'payment_method_id' => $input['payment_method_id'],
                'shipping_method_id' => $input['shipping_method_id'],
                'NP_city_store' => $input['NP_city_store'],
                'status_id' => 1, //new
                'discount_value' => $input['discount_value'] ? $input['discount_value'] : null,
                'discount_type' => $input['discount_type'],
                'shipping_cost' => $input['shipping_cost'],
                'grand_total' => $input['grand_total'],
            ]);

            $order->serial = 'ERP-' . str_pad($order->id, 8, "0", STR_PAD_LEFT);
            $order->save();

            // additional fields
            $order->created_at_formatted = $order->created_at_formatted;
            $order->status_name = $order->status->name;

            if (isset($input['products'])) {
                foreach ($input['products'] as $product) {
                    $order->products()->attach($product['product_supplier_price_id'], [
                        'product_name' => $product['product_name'],
                        'price' => $product['price'],
                        'quantity' => $product['quantity'],
                    ]);
                }
            }

            // history
            // todo: creation via shop api
            HistoryRepository::orderCreated($order);
        });

        return $GLOBALS['order'];
    }

    public static function show($id)
    {
        $order = Order::find($id);
        $item = $order->toArray();

        $item['status_name'] = $order->status->name;
        $item['created_at_formatted'] = $order->createdAtFormatted;

        $item['order_buyer'] = ['id' => $order->buyer_id, 'text' => $order->buyer_fullname];

        $item['products'] = OrderProductRepository::orderProducts($id);
        $item['action_buttons_html'] = static::actionButtonsHtml($order);

        return $item;
    }

    public static function update($input, $id)
    {
        DB::transaction(function () use ($input, $id) {
            $order = Order::find($id);

            $order->buyer_id = $input['buyer_id'];
            $order->buyer_fullname = Buyer::find($input['buyer_id'])->fullname;
            $order->payment_method_id = $input['payment_method_id'];
            $order->shipping_method_id = $input['shipping_method_id'];
            $order->NP_city_store = $input['NP_city_store'];
            $order->discount_value = $input['discount_value'] ? $input['discount_value'] : null;
            $order->discount_type = $input['discount_type'];
            $order->shipping_cost = $input['shipping_cost'];
            $order->grand_total = $input['grand_total'];

            $orderIsDirty = $order->orderIsDirty;
            $order->save();

            // sync products, check if something has changed
            $products = [];
            foreach ($input['products'] as $product) {
                $products[$product['product_supplier_price_id']] = [
                    'product_name'  => $product['product_name'],
                    'price'         => $product['price'],
                    'quantity'      => $product['quantity'],
                ];
            }

            $orderProductsAreDirty = OrderProductRepository::productsAreDirty($order, $products);
            $order->products()->sync($products);

            // write to History
            HistoryRepository::orderUpdated($order, $orderIsDirty, $orderProductsAreDirty);
        });
    }

    /**
     * Actions
     */
    public static function action($input)
    {
        $ordersIds = $input['ids'];
        if (! is_array($ordersIds)) $ordersIds = [$ordersIds];

        switch ($input['action']) {
            case 'paid':
                static::actionPaid($ordersIds);
                break;
            case 'shipped':
                static::actionShipped($ordersIds);
                break;
            case 'cancelled':
                static::actionCancelled($ordersIds);
                break;
            case 'delete':
                static::actionDelete($ordersIds);
                break;
        }

        // return html for action buttons refresh in popup
        if (count($ordersIds) == 1 && $input['action'] != 'delete')
            return static::actionButtonsHtml(Order::find($ordersIds[0]));
    }

    public static function actionPaid($ordersIds)
    {
        // $moneyType = 'cash' or 'account';
        $moneyType = 'cash';

        foreach ($ordersIds as $orderId) {
            DB::transaction(function () use ($orderId, $moneyType) {

                $order = Order::find($orderId);

                // create
                if (! $order->paid) {
                    // todo : check the fields
                    MoneyRepository::create([
                        'record_type' => 'operational',
                        'money_type' => $moneyType,
                        'income_expense_item_id' => 1,
                        'contractor_id' => $order->buyer_id,
                        'contractor_type' => 'App\Models\Buyer',
                        'base_id' => $order->id,
                        'base_type' => 'App\Models\Order',
                        'comment' => '',
                        'total' => $order->grand_total,
                    ]);

                    $order->update(['paid' => 1]);

                    // todo : write to history ?
                }

                // cancel
                else {
                    Money::where('base_id', $order->id)
                        ->where('base_type', 'App\Models\Order')
                        ->delete();

                    $order->update(['paid' => 0]);

                    // todo : write to history ?
                }

            });
        }
    }

    public static function actionShipped($ordersIds)
    {
        foreach ($ordersIds as $orderId) {
            DB::transaction(function () use ($orderId) {

                $order = Order::find($orderId);

                // create
                if (! $order->shipped) {
                    /*
                    foreach ($order->products as $purchaseProduct) {
                        // change product quantity
                        $purchaseProduct->product->{'quantity_' . $purchase->type} +=
                            $purchaseProduct->pivot->quantity;
                        $purchaseProduct->product->save();

                        // write to history
                        HistoryRepository::productQuantityUpdated(
                            $purchaseProduct->product, 'Закупка', $purchase->serial,
                            static::$typesForPurchase[$purchase->type],
                            $purchaseProduct->pivot->quantity
                        );
                    }
                    */

                    $order->update(['shipped' => 1]);
                }

                // cancel
                else {
                    /*
                    foreach ($order->products as $purchaseProduct) {
                        // change product quantity
                        $purchaseProduct->product->{'quantity_' . $purchase->type} -=
                            $purchaseProduct->pivot->quantity;
                        $purchaseProduct->product->save();

                        // write to history
                        HistoryRepository::productQuantityUpdated(
                            $purchaseProduct->product, 'Закупка - отмена', $purchase->serial,
                            static::$typesForPurchase[$purchase->type],
                            - $purchaseProduct->pivot->quantity
                        );
                    }
                    */

                    $order->update(['shipped' => 0]);
                }

            });
        }
    }

    public static function actionCancelled($ordersIds)
    {

    }

    public static function actionDelete($ordersIds)
    {
        // todo
        // todo: delete history ?
        foreach ($ordersIds as $orderId) {
            DB::transaction(function () use ($orderId) {
                $order = Order::find($orderId);

                // todo all logic before deletion
                // ...

                if (Gate::allows('access', 'delete-records')) $order->delete();
            });
        }
    }

    /**
     * action buttons html
     * @param $order: order model
     * @param $forRow: actions in 'row' and 'popup' have slightly different html
     * @return html
     */
    public static function actionButtonsHtml($order, $forRow = false){
        $additionalButtonClass = $forRow ? 'btn-xs' : '';
        $actionClasses = static::getActionButtonsClasses($order);

        $html = $forRow ?
            '<button class="btn btn-primary btn-xs btn-order-edit" title="Редактировать"><i class="fa fa-edit"></i></button>
            ' :
            '';

        $html .=
            '<button class="btn btn-success ' . $additionalButtonClass . ' ' . $actionClasses['paidClass'] . '" data-action="paid" title="Оплачен"><i class="fa fa-money"></i></button>
            <button class="btn btn-default ' . $additionalButtonClass . ' ' . $actionClasses['shippedClass'] . '" data-action="shipped" title="Доставлен"><i class="fa fa-truck"></i></button>
            <button class="btn btn-warning ' . $additionalButtonClass . '" data-action="cancelled" title="Отменен"><i class="fa fa-minus-circle"></i></button>
            ';

        if (Gate::allows('access', 'delete-records')) $html .=
            '<button class="btn btn-danger ' . $additionalButtonClass . '" data-action="delete" title="Удалить"><i class="fa fa-trash"></i></button>';

        return $html;
    }

    // helper function used in getData() and getActionButtonsHtml()
    public static function getActionButtonsClasses($order) {
        $paidClass = $order->money ? 'crossed' : '';
        $shippedClass = $order->shipped ? 'crossed' : '';

        return [
            'paidClass' => $paidClass,
            'shippedClass' => $shippedClass
        ];
    }

    /**
     * History
     */
    public static function history($id)
    {
        $history = History::where('table', 'orders')
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

            // order info row
            if ($record->changes['order']) $ret .=
                Table::table(static::$historyColumns, [$record->changes['order']]);

            // order products
            if ($record->changes['products']) $ret .=
                Table::table(OrderProductRepository::$historyColumns, $record->changes['products']);

            $i++;
        }

        return $ret;
    }


}