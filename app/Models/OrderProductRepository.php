<?php

namespace App\Models;

use Log;
use App\Classes\Table;

class OrderProductRepository {

    static $columns = [
        0 => ['checkbox'],
        1 => ['name', 'Наименование'],
        2 => ['vendor_code', 'Артикул'],
        3 => ['description', 'Характеристика'],
        4 => ['price', 'Цена'],
        5 => ['quantity', 'Кол-во'],
        6 => ['total', 'Итого'],
        7 => ['actions', ''],
    ];

    static $historyColumns = [
        0 => ['product_supplier_price_id', '<span title="ID Товара Поставщика (product_supplier_price_id)">psp_id</span>'],
        1 => ['name', 'Наименование товара'],
        2 => ['price', 'Цена'],
        3 => ['quantity', 'Количество'],
    ];

    static $balancesColumns = [
        0 => ['p_id', '<span title="ID Товара (product_id)">p_id</span>'],
        1 => ['name', 'Наименование'],
        2 => ['vendor_code', 'Артикул'],
        3 => ['description', 'Характеристика'],
        4 => ['quantity_receipt', '<span title="В собственности">Собс.</span>'],
        5 => ['quantity_realization', '<span title="На реализации">Реал.</span>'],
    ];

    /**
     * Returns all pivot records, including records with deleted products
     */
    public static function orderProducts($orderId){
        // relation Order::find(..)->products doesn't work.
        // because when order_product record exists but product_supplier_price record was deleted
        // it doesn't show.
        $orderProducts = OrderProduct::where('order_id', $orderId)->get();

        $ret = [];
        foreach ($orderProducts as $orderProduct) {
            // check if productProductSupplierPrice item exists
            $prodSupPrice = ProductSupplierPrice::find($orderProduct->product_supplier_price_id);
            // regardless of whether product exists it would be set to null if prodSupPrice is deleted
            $product = $prodSupPrice ?
                       Product::find($orderProduct->productSupplierPrice->product_id)  :
                       null;

            $ret[] = [
                'product_supplier_price_id' => $orderProduct->product_supplier_price_id,    // archived
                'product_and_psp_exists' => $product ? true : false,
                'product_id' => $product ? $product->id : '',
                'product_name' => $orderProduct->product_name,          // archived
                'vendor_code' => $product ? $product->vendor_code : '',
                'description' => $product ? $product->description : '',
                'price' => $orderProduct->price,                        // archived
                'quantity' => $orderProduct->quantity,                  // archived
            ];
        }

        return $ret;
    }

    public static function productsAreDirty($order, $products)
    {
        $productsBefore = [];

        // relation Order::find(..)->products doesn't work.
        // because when order_product record exists but product_supplier_price record was deleted
        // it doesn't show.
        $orderProducts = OrderProduct::where('order_id', $order->id)->get();
        foreach ($orderProducts as $orderProduct) {
            $productsBefore[$orderProduct->product_supplier_price_id] = [
                'product_name'  => $orderProduct->product_name,
                'price'         => $orderProduct->price,
                'quantity'      => $orderProduct->quantity
            ];
        }

        ksort($productsBefore);
        ksort($products);

        return $productsBefore != $products;
    }

    public static function orderProductsBalances($input) {
        $rows = '';
        foreach ($input['products'] as $productId) {
            $product = Product::find($productId);
            $rows[] = [
                $product->id,
                '<span title="' . e($product->name) . '">' . excerpt($product->name, 35) . '</span>',
                '<span title="' . e($product->vendor_code) . '">' . excerpt($product->vendor_code, 35) . '</span>',
                '<span title="' . e($product->description) . '">' . excerpt($product->description, 35) . '</span>',
                $product->quantity_receipt,
                $product->quantity_realization
            ];
        }

        return Table::table(static::$balancesColumns, $rows);
    }

}