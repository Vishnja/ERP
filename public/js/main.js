(function($){
    $(document).ready(function(){
        // expanded / collapsed menu
        $('body').on('expanded.pushMenu', function() {
            Cookies.set('sidebar_extended', 'true');
        });

        $('body').on('collapsed.pushMenu', function() {
            Cookies.set('sidebar_extended', 'false');
        });

        // fix for Bootstrap modal
        $.fn.modal.Constructor.prototype.enforceFocus = function() {};

    });
})(jQuery);

/************************************** Helpers ***********************************************/

/**
 * Nav tabs
 */
function activateTab(tab){
    $('.nav-tabs a[href="#' + tab + '"]').tab('show');
};

/**
 * Select2
 */
function select2load(selector, item) {
    var select = $(selector);
    var option = $('<option></option>').
        attr('selected', true).
        text(item.text).
        val(item.id);
    option.appendTo(select);
    select.trigger('change');
}

function select2clear(selector) {
    var select = $(selector);
    $(selector).find('option').remove();
    select.trigger('change');
}

/**
 * Table helpers
 */

// excerpt
function excerpt(text, maxChars) {
    return text.substr(0, maxChars) + "..";
}

// escape html
function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };

    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// unescape html
function unescapeHtml(text) {
    if (typeof text === 'undefined' || ! text) return '';

    var map = {
        '&amp;': '&',
        '&lt;': '<',
        '&gt;': '>',
        '&quot;': '"',
        '&#039;': "'"
    };

    return text.replace(/&amp;|&lt;|&gt;|&quot;|&#039;/gi, function(m) { return map[m]; });
}

// Add Rows
// Multiple or singular row
function addRows(tableId, rows){
    rows.forEach(function(cols) {
        var itemId = cols[0];

        cols.forEach(function(element, index){
            if (index == 0)
                cols[0] = '<input class="cb-select-item" data-item-id="' + itemId + '" type="checkbox">';
        });

        // actions (last column)
        cols.push(
            '<button data-id="' + itemId + '" class="btn btn-default btn-xs btn-circle remove-row">' +
                '<i class="glyphicon glyphicon-remove"></i>' +
            '</button>');

    });

    $( tableId ).DataTable().rows.add(rows).draw(true);
}

/**
 * addOrderProductRows
 */
function addOrderProductRows(products) {
    var rows = [];

    if (! (products instanceof Array)) products = [ products ];

    products.forEach(function(product){

        var productNameForTip = product.product_and_psp_exists ?
            product.product_name :
            'Удален - ' + product.product_name;

        var productName = product.product_and_psp_exists ?
            excerpt(product.product_name, 40) :
            '<strike>' + excerpt(product.product_name, 40) + '</strike>';

        var productIsEditableClass = product.product_and_psp_exists ? 'action-product-edit' : '';

        var element = product.product_and_psp_exists ? 'a' : 'span';

        var productNameWithAction =
            '<' + element + ' class="product-name ' + productIsEditableClass + '" ' +
                'title="' + escapeHtml(productNameForTip) + '" ' +
                'data-product-name-original="' + escapeHtml(product.product_name) + '" ' +
                'data-product-supplier-price-id="' + product.product_supplier_price_id + '" ' +
                'data-product-id="' + product.product_id + '" ' +
            '>' +
                productName +
            '</' + element + '> ';

        var productVendorCode = product.product_and_psp_exists ?
            '<span title="' + escapeHtml(product.vendor_code) + '">' +
                excerpt(product.vendor_code, 11) +
            '</span>' :
            '<span>-</span>';

        var productDescription = product.product_and_psp_exists ?
            '<span title="' + escapeHtml(product.description) + '">' +
                excerpt(product.description, 13) +
            '</span>' :
            '<span>-</span>';

        var total = product.price * product.quantity;

        // columns
        var cols = [
            null, // item-id for checkbox
            productNameWithAction,
            productVendorCode,
            productDescription,
            '<input class="product-price" value="' + product.price + '">',
            '<input type="text" class="product-num-spinner" value="' + product.quantity + '">',
            '<span class="product-total">' + total.toFixed(2) + '</span>'
        ];

        rows.push(cols);
    });

    addRows('#products-table', rows);

    $(".product-price").numeric({ negative: false });

    $("input.product-num-spinner").TouchSpin({
        min: 1,
        max: 999,
        verticalbuttons: true
    });

    setTimeout(function(){
        $('#products-table').DataTable().columns.adjust().draw(false);
    }, 1);
}

/**
 * addPurchaseProductRows
 *
 * @param products [
 *          id (product_supplier_price_id), product_and_psp_exists, product_name,
 *          supplier_name, vendor_code, description, purchase_price, quantity
 *        ]
 * @param tableId
 */
function addPurchaseProductRows(products, tableId) {
    var rows = [];

    if (! (products instanceof Array)) products = [ products ];

    products.forEach(function(product){

        var productNameForTip = product.product_and_psp_exists ?
            product.product_name :
            'Удален - ' + product.product_name;

        var productName = product.product_and_psp_exists ?
            excerpt(product.product_name, 31) :
            '<strike>' + excerpt(product.product_name, 31) + '</strike>';

        productName =
            '<span class="product-name" ' +
                'title="' + escapeHtml(productNameForTip) + '" ' +
                'data-product-name-original="' + escapeHtml(product.product_name) + '"' +
            '>' +
                productName +
            '</span>';

        var supplierName = product.product_and_psp_exists ?
            excerpt(product.supplier_name, 10) :
            '<strike>' + excerpt(product.supplier_name, 10) + '</strike>';

        supplierName =
            '<span class="supplier-name" title="' + escapeHtml(product.supplier_name) + '">' +
                supplierName +
            '</span>';

        var productVendorCode = product.product_and_psp_exists ?
            '<span class="vendor-code" title="' + escapeHtml(product.vendor_code) + '">' +
                excerpt(product.vendor_code, 9) +
            '</span>' :
            '<span>-</span>';

        var productDescription = product.product_and_psp_exists ?
            '<span class="description" title="' + escapeHtml(product.description) + '">' +
                excerpt(product.description, 8) +
            '</span>' :
            '<span>-</span>';

        var total = product.purchase_price * product.quantity;

        // columns
        var cols = [
            product.id, // product supplier price id
            productName,
            supplierName,
            productVendorCode,
            productDescription,
            '<span class="product-price">' + product.purchase_price + '</span>',
            '<input type="text" class="product-num-spinner" value="' + product.quantity + '">',
            '<span class="product-total">' + total.toFixed(2) + '</span>'
        ];

        rows.push(cols);
    });

    addRows(tableId, rows);

    $(".product-price").numeric({ negative: false });

    $("input.product-num-spinner").TouchSpin({
        min: 1,
        max: 999,
        verticalbuttons: true
    });

    setTimeout(function(){
        $(tableId).DataTable().columns.adjust().draw(false);
    }, 1);
}

/**
 * MySQL style datetime
 */
function getDateTime() {
    var now     = new Date();
    var year    = now.getFullYear();
    var month   = now.getMonth() + 1;
    var day     = now.getDate();
    var hour    = now.getHours();
    var minute  = now.getMinutes();
    var second  = now.getSeconds();

    if(month.toString().length == 1) month = '0' + month;
    if(day.toString().length == 1) day = '0' + day;
    if(hour.toString().length == 1) hour = '0' + hour;
    if(minute.toString().length == 1) minute = '0' + minute;
    if(second.toString().length == 1) second = '0' + second;

    var dateTime = year+'-'+month+'-'+day+' '+hour+':'+minute+':'+second;
    return dateTime;
}

/**
 * Checkbox items helpers
 */

// .cb-select-all change handler
$('.cb-select-all').change(function() {
    var isChecked = $(this).is(':checked');

    // scroll table
    if ($(this).closest('.dataTables_scroll').length) {
        $(this).closest('.dataTables_scroll').find('.dataTables_scrollBody .cb-select-item').each( function() {
            $(this).prop('checked', isChecked);
        });
    }
    // normal table
    else {
        $(this).closest('table').find('.cb-select-all').prop('checked', isChecked);

        $(this).closest('table').find('tbody .cb-select-item').each( function() {
            $(this).prop('checked', isChecked);
        });
    }
});

// clear .cb-select-all on DataTable redraw
$(document).on('draw.dt', function ( e, settings ) {
    $('#' + settings.sTableId + ' .cb-select-all').prop('checked', false);
} );

// clear .cb-select-all on modal close
$(document).on( 'hidden', function (e) {
    $invoker = $(e.target);
    $invoker.find('.cb-select-all').prop('checked', false);
} );

// order product selected rows.
// is used to send get array from order to purchase page to create 'return'
function orderProductSelectedRows() {
    var ret = [];

    $('#products-table .cb-select-item:checked').each( function() {

        var $row = $(this).closest('tr');
        // check if product exists
        // check is made by name, but special attr. may be added
        var productName = unescapeHtml( $row.find('.product-name').attr('title') );
        var productNameOriginal = unescapeHtml( $row.find('.product-name').data('product-name-original') );
        if (productName.substr(0,6) == 'Удален') {
            alert("Товар '" + productNameOriginal + "' удален и не будет добавлен в возврат!");
            return true;
        }

        var row = {};
        row['id'] = $row.find('.product-name').data('product-supplier-price-id');
        row['quantity'] = $row.find('.product-num-spinner').val();

        ret.push( row );
    });

    return ret;
}

// purchase products selected rows
function purchaseProductSelectedRows() {
    var ret = [];

    $('#purchase-products-table .cb-select-item:checked').each( function() {

        var $row = $(this).closest('tr');
        // check if product exists
        // check is made by name, but special attr. may be added
        var productName = unescapeHtml( $row.find('.product-name').attr('title') );
        var productNameOriginal = unescapeHtml( $row.find('.product-name').data('product-name-original') );
        if (productName.substr(0,6) == 'Удален') {
            alert("Товар '" + productNameOriginal + "' удален и не будет добавлен в возврат!");
            return true;
        }

        var row = [];
        row['id'] = $row.find('.cb-select-item').data('item-id');   // product supplier price id
        row['product_and_psp_exists'] = true;
        row['product_name'] = productName;
        row['supplier_name'] = unescapeHtml( $row.find('.supplier-name').attr('title') );
        row['vendor_code'] = unescapeHtml( $row.find('.vendor-code').attr('title') );
        row['description'] = unescapeHtml( $row.find('.description').attr('title') );
        row['purchase_price'] = $row.find('.product-price').text();
        row['quantity'] = $row.find('.product-num-spinner').val();

        ret.push( row );
    });

    return ret;
}

// table selected items ids
function tableSelectedItemsIds(tableId){
    var ids = [];

    $(tableId + " .cb-select-item:checked").each( function(){
        ids.push( $(this).data('item-id') );
    });

    return ids;
}

// actions handler helper
// @param itemEntityName: e.g.: '#order', is used to generate table and field ids:
//                        '#order' + '-id', '#order' + 's-table'

function initActionsHandler(itemEntityName, tableInstance, actionUrl) {
    var tableId = itemEntityName + 's-table';
    var itemIdInputName = itemEntityName + '-id';

    // enable/disable mass action buttons
    initMassActionButtonsStateRefresh(tableId);

    // action handler
    $(document).on('click', '*[data-action]', function() {

        if ($(this).hasClass('disabled')) return false;

        var action = $(this).data('action');
        var itemsIds;
        var $parent = $(this).parent();

        // items
        var parentIsModal = false;

        if ($parent.is("td")) {
            itemsIds = $(this).closest('tr').find('.cb-select-item').data('item-id');
        }
        else if ($parent.is("div.modal-footer-actions")) {
            parentIsModal = true;
            itemsIds = $(itemIdInputName).val();
        }
        else if ($parent.is("h1")) {
            itemsIds = tableSelectedItemsIds(tableId);
        }

        // delete confirmation
        if (action == 'delete') {
            if (! confirm("Подтвердите удаление!")) return false;
        }

        var itemsData = {
            _method: "GET",
            _token: $('#csrf-token').val(),

            action: action,
            ids: itemsIds
        };

        $.ajax({
            url: actionUrl,
            type: 'GET',
            data: itemsData,
            success: function(response) {
                if (response.status == 'success') {
                    if (parentIsModal) {
                        // close modal
                        if (action == 'delete') {
                            $(itemEntityName).modal('hide');
                        }
                        // refresh action buttons if parent is modal
                        else {
                            $parent.html(response.action_buttons_html);
                        }
                    }

                    // refresh Datatable
                    tableInstance.draw(false);
                }
                else {
                    alert(response.message);

                    // refresh Datatable
                    // e.g.: some records are deleted, and some are not due to error
                    tableInstance.draw(false);

                }
            },
            error: function(){
                alert('Произошла ошибка при совершении действия!');
            }
        });

        return false;
    });
}

/**
 * Mass action buttons helpers
 */
function refreshMassActionButtonsState(tableId) {
    if (tableSelectedItemsIds(tableId).length > 0) {
        $(".content-header button[data-action]").removeClass('disabled');
    } else {
        $(".content-header button[data-action]").addClass('disabled');
    }
}

function initMassActionButtonsStateRefresh(tableId) {
    $(document).on('change', tableId + " .cb-select-all," + tableId +" .cb-select-item", function(){
        refreshMassActionButtonsState(tableId);
    });

    $(document).on('draw.dt', function(e, settings){
        refreshMassActionButtonsState(tableId);
    } );
}

/**
 * Validate and save (in callback)
 */
function validateAndSaveItem(entity, fields, modalId, callback) {
    modalId = typeof modalId !== 'undefined' ? modalId : '#item';

    // AJAX validation
    var data = {
        _method: "POST",
        _token: $('#csrf-token').val(),

        entity: entity,
        fields: fields
    };

    var isValid = true;

    $.ajax({
        url: baseUrl + '/validation',   // baseUrl defined in 'page.blade.php'
        type: 'POST',
        data: data,
        success: function(response) {
            if (response.status == 'fail') {

                $(modalId + " .alert").remove();
                $(modalId).modal('loading').find('.modal-body').prepend(
                    '<div class="alert alert-error fade in">' +
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                        response.errors_html +
                    '</div>');

                // strange bug with .modal('loading')
                //$(modalId).find('.loading-mask.fade').remove();

                isValid = false;
            } else {
                callback();
            }
        },
        error: function() {
            $(modalId).modal('loading');
            // strange bug with .modal('loading')
            //$(modalId).find('.loading-mask.fade').remove();

            alert('Произошла ошибка во время валидации!');
            isValid = false;
        }
    });

    return isValid;
}