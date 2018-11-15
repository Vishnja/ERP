<?php

namespace App\Models;

use DB;
use Log;
use Validator;

class ValidationRepository {

    public static function validate($input)
    {
        return static::processValidation(
            $input['fields'],
            static::{$input['entity'] . 'Rules'}($input['fields'])
        );
    }

    /**
     * Validation rules generators for different entities
     */

    // orders, purchases, money
    public static function orderRules($fields){
        $rules = [
            'buyer_id'  => 'required',
            'products'  => 'required',
        ];

        return $rules;
    }

    public static function purchaseRules($fields){
        $rules = [
            'products'  => 'required',
        ];

        return $rules;
    }

    public static function returnRules($fields){
        $rules = [
            'products'  => 'required',
        ];

        return $rules;
    }

    public static function moneyRules($fields){

        if ($fields['record_type'] == 'operational') {
            $rules = [
                'contractor_id' => 'required',
                'base_id'       => 'required',
                'total'         => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            ];
        } else {
            $rules = [
                'total'         => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            ];
        }

        return $rules;
    }

    // users/roles
    public static function userRules($fields){
        $rules = [
            'name'      => 'required',
            'surname'   => 'required',
            'email'     => 'required|unique:users' . ( $fields['id'] ? ',email,' . $fields['id'] : '' ),
        ];

        // password required on 'create'
        if ($fields['id'] == 0) $rules['password'] = 'required';

        return $rules;
    }

    public static function roleRules($fields){
        return [
            'name' => 'required|unique:roles' . ( $fields['id'] ? ',name,' . $fields['id'] : '' )
        ];
    }

    // directories
    public static function supplierRules($fields){
        return [
            'name'  => 'required|unique:suppliers' . ( $fields['id'] ? ',name,' . $fields['id'] : '' ),
            //'phone' => 'required',
            //'email' => 'required',
            //'contact_person' => 'required',
        ];
    }

    public static function buyerRules($fields){
        return [
            'name'  => 'required',
            'surname' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'city' => 'required',
            'address' => 'required',
        ];
    }

    public static function productRules($fields){
        return [
            'name'  => 'required',
            'vendor_code' => 'required',
            //'description' => 'required',
            'price' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
        ];
    }

    public static function productSupplierPriceRules($fields){
        return [
            'product_id'  => 'required',
            'supplier_id' => 'required',
            'purchase_price' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
        ];
    }

    public static function incomeExpenseItemRules($fields){
        return [
            //'name'  => 'required',
            'name' => 'required|unique:income_expense_items' . ( $fields['id'] ? ',name,' . $fields['id'] : '' )
        ];
    }

    /**
     * Helpers
     */
    public static function processValidation($fields, $rules){
        $validator = Validator::make($fields, $rules);

        return [
            'errors_html'   => $validator->fails() ? static::errorsHtml($validator->errors()) : '',
            'status'        => $validator->fails() ? 'fail' : 'success',
        ];
    }

    public static function errorsHtml($errors)
    {
        $html = '<ul>';

        foreach ($errors->all() as $error) {
            $html .= '<li>' . $error . '</li>';
        }

        $html .= '</ul>';

        return $html;
    }
}