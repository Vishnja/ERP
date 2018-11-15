<?php

namespace App\Models;

use DB;
use Log;

class HistoryRepository {
    /**
     * Enum values names
     */
    public static $action = [
        'create' => 'создание',
        'update'  => 'обновление'
    ];

    /**
     * Order
     */

    public static function orderCreated($order){
        History::create([
            'table' => 'orders',
            'item_id' => $order->id,
            'user' => auth()->user()->fullname . ' (id: ' . auth()->user()->id . ')',
            'action' => 'create',
            'changes' => [
                'order' => $order->archiveAttributes,
                'products' => $order->products->map(function ($item) {
                    return [
                        'id' => $item->pivot->product_supplier_price_id,
                        'product_name' => $item->pivot->product_name,
                        'price' => $item->pivot->price,
                        'quantity' => $item->pivot->quantity
                    ];
                })
            ]
        ]);
    }

    public static function orderUpdated($order, $orderIsDirty = false, $productsAreDirty = false){
        if ($orderIsDirty || $productsAreDirty) {
            History::create([
                'table' => 'orders',
                'item_id' => $order->id,
                'user' => auth()->user()->fullname . ' (id: ' . auth()->user()->id . ')',
                'action' => 'update',
                'changes' => [
                    'order' => $orderIsDirty ? $order->archiveAttributes : '',
                    'products' => $productsAreDirty ?
                        // relation Order::find(..)->products doesn't work.
                        // because when order_product record exists but product_supplier_price record was deleted
                        // it doesn't show.
                        OrderProduct::where('order_id', $order->id)->get()->map(function ($item) {

                            $prodSupPrice = ProductSupplierPrice::find($item->product_supplier_price_id);
                            // regardless of whether product exists it would be set to null if prodSupPrice is deleted
                            $product = $prodSupPrice ?
                                       Product::find($item->productSupplierPrice->product_id)  :
                                       null;

                            return [
                                'id' => $item->product_supplier_price_id,
                                // it's not necessary to apply stike, because it will show only after save
                                // and product may be deleted long before.
                                // also this situation with saving order with deleted products
                                // is unlikely to happen at all.
                                'product_name' => $product ?
                                                  $item->product_name :
                                                  '<strike>' . $item->product_name . '</strike>',
                                'price' => $item->price,
                                'quantity' => $item->quantity
                            ];
                        }) :
                        ''
                ]
            ]);
        }
    }

    /**
     * Purchase
     */

    public static function purchaseCreated($purchase){
        History::create([
            'table' => 'purchases',
            'item_id' => $purchase->id,
            'user' => auth()->user()->fullname . ' (id: ' . auth()->user()->id . ')',
            'action' => 'create',
            'changes' => [
                'purchase' => $purchase->archiveAttributes,
                'products' => $purchase->products->map(function ($item) {
                    return [
                        'id' => $item->pivot->product_supplier_price_id,
                        'product_name' => $item->pivot->product_name,
                        'supplier_name' => $item->pivot->supplier_name,
                        'purchase_price' => $item->pivot->purchase_price,
                        'quantity' => $item->pivot->quantity
                    ];
                })
            ]
        ]);
    }

    public static function purchaseUpdated($purchase, $purchaseIsDirty = false, $productsAreDirty = false){
        if ($purchaseIsDirty || $productsAreDirty) {
            History::create([
                'table' => 'purchases',
                'item_id' => $purchase->id,
                'user' => auth()->user()->fullname . ' (id: ' . auth()->user()->id . ')',
                'action' => 'update',
                'changes' => [
                    'purchase' => $purchaseIsDirty ? $purchase->archiveAttributes : '',
                    'products' => $productsAreDirty ?
                        // relation Purchase::find(..)->products doesn't work.
                        // because when purchase_product record exists but product_supplier_price record was deleted
                        // it doesn't show.
                        PurchaseProduct::where('purchase_id', $purchase->id)->get()->map(function ($item) {

                            $prodSupPrice = ProductSupplierPrice::find($item->product_supplier_price_id);
                            // regardless of whether product exists it would be set to null if prodSupPrice is deleted
                            $product = $prodSupPrice ?
                                       Product::find($item->productSupplierPrice->product_id)  :
                                       null;

                            return [
                                'id' => $item->product_supplier_price_id,
                                // it's not necessary to apply stike, because it will show only after save
                                // and product may be deleted long before.
                                // also this situation with saving purchase with deleted products
                                // is unlikely to happen at all.
                                'product_name' => $product ?
                                                  $item->product_name :
                                                  '<strike>' . $item->product_name . '</strike>',
                                'supplier_name' => $product ?
                                                   $item->supplier_name :
                                                   '<strike>' . $item->supplier_name . '</strike>',
                                'purchase_price' => $item->purchase_price,
                                'quantity' => $item->quantity
                            ];
                        }) :
                        ''
                ]
            ]);
        }
    }

    /**
     * Product
     */

    public static function productCreated($product){
        History::create([
            'table' => 'products',
            'item_id' => $product->id,
            'user' => auth()->user()->fullname . ' (id: ' . auth()->user()->id . ')',
            'action' => 'create',
            'changes' => [
                'general_info' => $product->archiveAttributes,
                /*'items_income_expense' => null*/
            ]
        ]);
    }

    // is used when product info is updated from the product page
    public static function productGeneralInfoUpdated($product){
        History::create([
            'table' => 'products',
            'item_id' => $product->id,
            'user' => auth()->user()->fullname . ' (id: ' . auth()->user()->id . ')',
            'action' => 'update',
            'changes' => [
                'general_info' => $product->archiveAttributes
            ]
        ]);
    }

    // is used when product quantity is updated from 'orders' of 'purchases'
    public static function productQuantityUpdated(
        $product, $baseType, $baseId, $quantityType, $quantity
    ){
        History::create([
            'table' => 'products',
            'item_id' => $product->id,
            'user' => auth()->user()->fullname . ' (id: ' . auth()->user()->id . ')',
            'action' => 'update',
            'changes' => [
                'quantity' => [
                    'baseType' => $baseType,
                    'baseId' => $baseId,
                    'quantityType' => $quantityType,
                    'quantity' => $quantity
                ]
            ]
        ]);
    }
}