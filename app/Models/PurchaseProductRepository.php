<?php

namespace App\Models;

use Log;

class PurchaseProductRepository {

    static $columns = [
        0 => ['checkbox'],
        1 => ['name', 'Наименование'],
        2 => ['supplier_name', 'Поставщик'],
        3 => ['vendor_code', 'Артикул'],
        4 => ['description', 'Хар-ка'],
        5 => ['price', 'З. Цена'],
        6 => ['quantity', 'Кол-во'],
        7 => ['total', 'Итого'],
        8 => ['actions', ''],
    ];

    static $historyColumns = [
        0 => ['product_supplier_price_id', '<span title="ID Товара Поставщика (product_supplier_price_id)">psp_id</span>'],
        1 => ['name', 'Наименование товара'],
        2 => ['supplier_name', 'Поставщик'],
        3 => ['purchase_price', 'З. Цена'],
        4 => ['quantity', 'Кол-во'],
    ];

    /**
     * Returns all pivot records, including records with deleted products
     */
    public static function purchaseProducts($purchaseId){
        // relations Purchase::find(..)->products don't work
        // when purchase_product record exists but product_supplier_price record was deleted
        // it doesn't show.
        $purchaseProducts = PurchaseProduct::where('purchase_id', $purchaseId)->get();

        $ret = [];
        foreach ($purchaseProducts as $purchaseProduct) {
            // check if productProductSupplierPrice item exists
            $prodSupPrice = ProductSupplierPrice::find($purchaseProduct->product_supplier_price_id);
            // regardless of whether product exists it would be set to null if prodSupPrice is deleted
            $product = $prodSupPrice ?
                       Product::find($purchaseProduct->productSupplierPrice->product_id) :
                       null;

            $ret[] = [
                'id' => $purchaseProduct->product_supplier_price_id,    // archived
                'product_and_psp_exists' => $product ? true : false,
                'product_name' => $purchaseProduct->product_name,       // archived
                'supplier_name' => $purchaseProduct->supplier_name,     // archived
                'vendor_code' => $product ? $product->vendor_code : '',
                'description' => $product ? $product->description : '',
                'purchase_price' => $purchaseProduct->purchase_price,   // archived
                'quantity' => $purchaseProduct->quantity                // archived
            ];
        }

        return $ret;
    }

    public static function productsAreDirty($purchase, $products)
    {
        $productsBefore = [];

        // relation Purchase::find(..)->products doesn't work.
        // because when purchase_product record exists but product_supplier_price record was deleted
        // it doesn't show.
        $purchaseProducts = PurchaseProduct::where('purchase_id', $purchase->id)->get();
        foreach ($purchaseProducts as $purchaseProduct) {
            $productsBefore[$purchaseProduct->product_supplier_price_id] = [
                'product_name'      => $purchaseProduct->product_name,
                'supplier_name'     => $purchaseProduct->supplier_name,
                'purchase_price'    => $purchaseProduct->purchase_price,
                'quantity'          => $purchaseProduct->quantity
            ];
        }

        ksort($productsBefore);
        ksort($products);

        return $productsBefore != $products;
    }

    /**
     * Get products (PSPs) info to create 'return' from 'order' products (sent via GET param).
     *
     * Return will be used inline is javascript.
     */
    public static function purchaseProductsFromOrder(){
        if (! isset($_GET['return_products'])) return 'null';

        $orderProducts = json_decode($_GET['return_products']);

        $ret = [];
        foreach ($orderProducts as $orderProduct) {
            // Both PSP and product should still exist at this point.
            // Check has been made in js in 'orders'.
            $prodSupPrice = ProductSupplierPrice::find($orderProduct->id);

            $ret[] = [
                'id' => $prodSupPrice->id,
                'product_exists' => true ,
                'product_name' => $prodSupPrice->product->name,
                'supplier_name' => $prodSupPrice->supplier->name,
                'vendor_code' => $prodSupPrice->product->vendor_code,
                'description' => $prodSupPrice->product->description,
                'purchase_price' => $prodSupPrice->purchase_price,
                'quantity' => $orderProduct->quantity
            ];
        }

        return json_encode($ret);
    }
}