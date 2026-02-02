// ---- GLOBAL FUNCTIONS ----

function NewCustomerModal() {
  $("#NewCustomerModal").modal("show");
}

// Event delegation for cart table (better for dynamic rows!)
$('#u_tbl').on('input', 'input.item_Qty, input.item_Rate, input.item_DiscountPrice', function() {
    rowInputChanged(this);
});

// NEW: Set Qty handler (change set qty → update all items in that set)
$('#u_tbl').on('input', 'input.set_qty', function() {
    var setId = $(this).data('set-id');
    var setQty = parseFloat($(this).val());
    if (isNaN(setQty) || setQty <= 0) setQty = 1;
    updateSetQuantities(setId, setQty);
});

function checkParameters() {
    var s_TotalItems = $.trim($("#s_TotalItems").val());
    var ex_client_name = $.trim($("#ex_client_name").val());
    var sp_Amount = $.trim($("#sp_Amount").val());
    var s_SaleMode = $.trim($("#s_SaleMode").val());

    if (ex_client_name == '') {
        $.smallBox({
            title: "Error",
            content: "<i class='fa fa-clock-o'></i> <i>Please Give Customer Name.</i>",
            color: "#C46A69",
            iconSmall: "fa fa-times fa-2x fadeInRight animated",
            timeout: 4000
        });
        $("#ex_client_name").focus();
        return false;
    }

    if (s_SaleMode == 'cash' && (sp_Amount == 0 || sp_Amount == '')) {
        $.smallBox({
            title: "Error",
            content: "<i class='fa fa-clock-o'></i> <i>Your sale mode is cash please fill received cash field.</i>",
            color: "#C46A69",
            iconSmall: "fa fa-times fa-2x fadeInRight animated",
            timeout: 4000
        });
        return false;
    }
}

// Re-entrancy lock for bulk set updates
var isBulkSetUpdate = false;

function rowInputChanged(e) {
    var row = $(e).closest('tr');
    var qty = parseFloat(row.find('input.item_Qty').val());
    if (isNaN(qty) || qty <= 0) qty = 1;

    var rate = parseFloat(row.find('input.item_Rate').val());
    if (isNaN(rate) || rate < 0) rate = 0;

    var discountAmount = parseFloat(row.find('input.item_DiscountPrice').val());
    if (isNaN(discountAmount) || discountAmount < 0) discountAmount = 0;

    var netAmount = (qty * rate) - discountAmount;
    if (netAmount < 0) netAmount = 0;

    // Ext Amount column index (after adding Set Qty column)
    var td = row.find('td').eq(8);

    td.find('input.item_NetPrice').remove();
    td.append('<input type="hidden" name="item_NetPrice[]" class="item_NetPrice" value="' + netAmount.toFixed(2) + '">');
    row.find('.item_NetPrice_show').text(netAmount.toFixed(2));

    // If this row belongs to a set and user directly changed Quantity,
    // compute Set Qty = Qty / UnitPerSet and propagate to the whole set.
    var setId = String(row.attr('data-set-id') || '');
    var ups = parseFloat(row.attr('data-unit-per-set'));
    if (setId && !isNaN(ups) && ups > 0 && !isBulkSetUpdate) {
        var derivedSetQty = qty / ups;
        // Show integer if exact, else up to 2 decimals
        var displaySetQty = (Math.abs(derivedSetQty - Math.round(derivedSetQty)) < 1e-9)
            ? Math.round(derivedSetQty)
            : parseFloat(derivedSetQty.toFixed(2));
        $('input.set_qty[data-set-id="' + setId + '"]').val(displaySetQty);
        // Propagate set qty across this set's rows to keep items consistent
        updateSetQuantities(setId, derivedSetQty);
        return; // updateSetQuantities will recalc amounts again
    }

    calculate();
    totalItems();
}

function calculate() {
    var net_sum = 0, taxamount = 0;
    $('input.item_NetPrice').each(function() {
        var val = parseFloat($(this).val());
        if (!isNaN(val)) net_sum += val;
    });
    $("#s_TotalAmount").val(net_sum.toFixed(2));
    var s_DiscountAmount = parseFloat($("#s_DiscountAmount").val()) || 0;
    var due = net_sum - s_DiscountAmount;
    var s_Tax = parseFloat($("#s_Tax").val()) || 0;
    taxamount = 0;
    if (s_Tax > 0) {
        taxamount = (s_Tax / 100) * due;
        due += taxamount;
    }
    taxamount = taxamount.toFixed(2);
    due = due < 0 ? 0 : due;
    due = due.toFixed(2);
    $("#s_NetAmount").val(due);
    $("#s_NetAmountShow").html(due);
    $("#s_TaxAmount").val(taxamount);
    $("#s_TaxAmountShow").html(taxamount);
    var total_items = 0;
    $('input.item_NetPrice').each(function() {
        if (parseFloat($(this).val()) > 0) total_items++;
    });
    $("#totalItems").html(total_items);
    $("#s_TotalItems").val(total_items);
}

function totalItems() {
    var total_items = 0;
    $('input.item_NetPrice').each(function() {
        if (parseFloat($(this).val()) > 0) total_items++;
    });
    $("#totalItems").html(total_items);
    $("#s_TotalItems").val(total_items);
}

$(document).ready(function() {
    $("#u_row").remove(); // Remove template row completely!

    $("#checkout-form").on("submit", function(e) {
        // Remove all invisible/template rows
        $("#u_tbl tr:hidden").remove();

        // Remove rows with any blank input (name/id/qty)
        $("#u_tbl tbody tr").each(function() {
            var nameVal = $(this).find("input[name='item_Name[]']").val();
            var idVal   = $(this).find("input[name='item_id[]']").val();
            var qtyVal  = $(this).find("input[name='item_Qty[]']").val();
            if ((!nameVal || nameVal.trim() === "") ||
                (!idVal   || idVal.trim()   === "") ||
                (!qtyVal  || qtyVal.trim()  === "")) {
                $(this).remove();
            }
        });
    });

    $('#u_tbl tr').each(function() {
        var row = $(this);
        var qty = parseFloat(row.find('input.item_Qty').val()) || 0;
        var rate = parseFloat(row.find('input.item_Rate').val()) || 0;
        var discountAmount = parseFloat(row.find('input.item_DiscountPrice').val()) || 0;
        var netAmount = (qty * rate) - discountAmount;
        if (netAmount < 0) netAmount = 0;
        row.find('input.item_NetPrice').val(netAmount.toFixed(2));
        row.find('.item_NetPrice_show').text(netAmount.toFixed(2));
    });
    calculate();
    totalItems();
});

// Global lock to prevent double fetch (Enter + blur duplicates)
var itemDetailAjaxRunning = false;
function releaseItemDetailLock() { itemDetailAjaxRunning = false; }

function getItemDetail() {
    if (itemDetailAjaxRunning) return;
    itemDetailAjaxRunning = true;

    var inputVal = $("#ex_item").val();
    var item_id = null;
    var set_id = null;

    // Datalist check
    $("#item_list option").each(function() {
        if ($(this).val() === inputVal) {
            item_id = $(this).data('item-id');
            set_id = $(this).data('set-id');
            return false;
        }
    });

    // If set selected from datalist
    if (set_id) {
        var pureSetId = String(set_id).replace('set_', '');
        $.post('get_set_items.php', { set_id: pureSetId, mode: 'sale' }, function(data){
            processSetItems(data, { set_id: pureSetId, set_name: inputVal });
            releaseItemDetailLock();
        }).fail(function(){ releaseItemDetailLock(); });
        return;
    }

    // If product selected from datalist
    if (item_id) {
        $.ajax({
            type: "GET",
            url: "ajax/get_item_detail.php",
            dataType: 'json',
            data: {item_id: item_id},
            success: function(data) {
                if (data && data.item_id) {
                    fillProductFields(data);
                } else {
                    alert("No item data found.");
                }
            },
            error: function() {
                alert("Please Choose An Item First");
            },
            complete: function(){ releaseItemDetailLock(); }
        });
        return;
    }

    // Manual entry (code entered, not found in datalist)
    if (inputVal !== "") {
        $.ajax({
            type: "GET",
            url: "ajax/get_item_detail.php",
            dataType: 'json',
            data: {item_code: inputVal},
            success: function(data) {
                if (data && data.is_set && data.set_id) {
                    $.post('get_set_items.php', { set_id: data.set_id, mode: 'sale' }, function(setData){
                        processSetItems(setData, { set_id: data.set_id, set_name: data.set_name || inputVal });
                        releaseItemDetailLock();
                    }).fail(function(){ releaseItemDetailLock(); });
                }
                else if (Array.isArray(data) && data.length > 0 && data[0].item_id) {
                    processSetItems(JSON.stringify(data));
                    releaseItemDetailLock();
                }
                else if (data && data.item_id) {
                    fillProductFields(data);
                    releaseItemDetailLock();
                }
                else {
                    alert("Please Choose A Valid Product or Set");
                    releaseItemDetailLock();
                }
            },
            error: function() {
                alert("Please Choose A Valid Product or Set");
                releaseItemDetailLock();
            }
        });
        return;
    }

    releaseItemDetailLock();
}

// Processes set items (array JSON string)
function processSetItems(data, setMeta) {
    var items = [];
    try { items = JSON.parse(data); } catch(e) { }
    if (!Array.isArray(items) || items.length === 0) {
        alert("Set is empty or not found!");
        return;
    }
    items.forEach(function(row){
        addOrUpdateTableRow(row, setMeta);
    });
    $('#ex_item').val('');
    calculate();
    totalItems();
    $("#ex_qty").focus().select();
}

function addOrUpdateTableRow(row, setMeta) {
    var incomingSetId = setMeta && setMeta.set_id ? String(setMeta.set_id) : '';

    // If row for same item_id exists:
    var found_duplicate = false;
    var rowItemId = String(row.item_id).trim();
    $("#u_tbl .item_id").each(function(idx, elemObj) {
        var tr = $(elemObj).closest("tr");
        var tableItemId = String(elemObj.value).trim();
        var tableSetId = String(tr.attr('data-set-id') || '');
        if (tableItemId === rowItemId) {
            // Same set: increase Set Qty by 1 (instead of doubling per-item qty)
            if (incomingSetId && tableSetId === incomingSetId) {
                var $anySetQty = $('input.set_qty[data-set-id="' + incomingSetId + '"]').first();
                var currentSetQty = parseFloat($anySetQty.val());
                if (isNaN(currentSetQty) || currentSetQty <= 0) currentSetQty = 1;
                updateSetQuantities(incomingSetId, currentSetQty + 1);
                found_duplicate = true;
                return false; // break
            } else if (!incomingSetId && !tableSetId) {
                // Non-set duplicate: accumulate quantity
                found_duplicate = true;
                var prevQty = parseFloat(tr.find("input.item_Qty").val()) || 0;
                var addQty = parseFloat(row.quantity) || 1;
                var rate = parseFloat(tr.find("input.item_Rate").val()) || parseFloat(row.item_Price) || 0;
                var discount = parseFloat(tr.find("input.item_DiscountPrice").val()) || 0;
                var newQty = prevQty + addQty;
                tr.find("input.item_Qty").val(newQty);

                var netAmount = (newQty * rate) - discount;
                if (netAmount < 0) netAmount = 0;

                tr.find("span.item_NetPrice_show").text(netAmount.toFixed(2));
                var netInput = tr.find('input.item_NetPrice');
                if (netInput.length) netInput.val(netAmount.toFixed(2));
                rowInputChanged(tr.find("input.item_Qty")[0]);
                return false;
            }
        }
    });

    if (found_duplicate) return;

    // New row build
    var itemStock = (typeof row.stock !== 'undefined' && row.stock !== null) ? row.stock : (row.item_Stock || '');
    var unitPerSet = parseFloat(row.quantity) || 1; // per-set quantity of this item
    var isSetRow = !!incomingSetId;
    var setQty = isSetRow ? 1 : ''; // default 1 for newly added set
    var effectiveQty = isSetRow ? (setQty * unitPerSet) : (parseFloat(row.quantity) || 1);

    var netAmount = ((parseFloat(effectiveQty) || 1) * parseFloat(row.item_Price || 0));

    var item = {
        item_Code: row.item_Code,
        item_Name: row.item_Name,
        item_Stock: itemStock,
        set_id: incomingSetId,
        set_qty: isSetRow ? 1 : '', // IMPORTANT: default 1 (not 30)
        unitPerSet: unitPerSet,
        qty: effectiveQty,
        rate: row.item_Price || 0,
        discount: 0,
        costprice: row.item_CostPrice,
        netamount: netAmount,
        item_id: row.item_id
    };
    var html = getNewRowHtml(item);

    if (!$('#u_tbl tbody').length) {
        $('#u_tbl').append('<tbody></tbody>');
    }
    $('#u_tbl tbody').append(html);

    removeBlankRowsFromTable();
    playProductAddSound();
}

function fillProductFields(data) {
    $("#ex_itemcode").val(data.item_Code || '');
    $("#ex_qty").val('1');
    $("#ex_rate").val(data.sale_price || data.item_SalePrice || '');
    $("#ex_netamount").val(data.sale_price || data.item_SalePrice || '');
    $("#ex_costprice").val(data.cost_price || data.item_PurchasePrice || '');
    $("#ex_itemname").val(data.item_Name || '');
    $("#ex_item_id_from_imei").val(data.item_id || '');
    $("#ex_item_stock").val(data.stock || data.item_CurrentStock || '');
    $("#ex_stock").val(data.stock || data.item_CurrentStock || '');
    $("#ex_qtyinpack").val(data.qty_pack || data.item_QtyInPack || '');
    $("#ex_qty").focus().select();
}

function select_clilent_name(){
	ex_client_name=$('#ex_client_name').val();
	if(ex_client_name!==''){
		var selectedOption = $('option[value="'+$("#ex_client_name").val()+'"]');
		client_id=selectedOption.attr("data-client-id");
		client_Phone=selectedOption.attr("data-client-phone");
		if(client_id>0){
			$('#client_id').val(client_id);
			$('#ex_client_phone').val(client_Phone);
		}
	}
}

function select_clilent_phone(){
	ex_client_phone=$('#ex_client_phone').val();
	if(ex_client_phone!==''){
		var selectedOption = $('option[value="'+$("#ex_client_phone").val()+'"]');
		client_id=selectedOption.attr("data-client-id");
		client_Name=selectedOption.attr("data-client-name");
		if(client_id>0){
			$('#client_id').val(client_id);
			$('#ex_client_name').val(client_Name);
		}
	}
}

function calculate_netamount_row(val){
	var ex_qty=parseFloat($("#ex_qty").val())||0;
	var ex_rate=parseFloat($("#ex_rate").val())||0;
	var ex_discount_amount=parseFloat($("#ex_discount_amount").val())||0;
	var ex_netamount=(ex_qty*ex_rate)-ex_discount_amount;
	if(ex_netamount<0) ex_netamount=0;
	$("#ex_netamount").val(ex_netamount);
}

$("#client_Name").keypress(function(e){
	if(e.keyCode==13){ $("#ex_imei").focus().select(); }
});
$("#ex_qty").keypress(function(e){
	if(e.keyCode==13){ $("#ex_rate").focus().select(); }
});
$("#ex_rate").keypress(function(e){
	if(e.keyCode==13){ $("#ex_discount_percentage").focus(); }
});
$("#ex_discount_percentage").keypress(function(e){
	if(e.keyCode==13){ $("#ex_costprice").focus(); }
});
$("#ex_costprice").keypress(function(e){
	if(e.keyCode==13){ addToTable(); }
});

function checkParameters_NewCustomer(){
	var new_client_Name = $.trim($("#new_client_Name").val());
	if (new_client_Name == ''){
		$.smallBox({
		title : "Error",
		content : "<i class='fa fa-clock-o'></i> <i>Please Fill Customer Name.</i>",
		color : "#C46A69",
		iconSmall : "fa fa-times fa-2x fadeInRight animated",
		timeout : 4000
		});
	$("#new_client_Name").focus();
	return false;
	}
}

function del(val){
 $.SmartMessageBox({
 title : "Attention required!",
 content : "This is a confirmation box. Do you want to delete the Record?",
 buttons : '[No][Yes]'
 }, function(ButtonPressed) {
 if (ButtonPressed === "Yes") {
$.post("ajax/delAjax.php",{ s_id : val }, function(data,status){ 
 if(data.trim()!=""){
 		$.smallBox({
		 title : "Delete Status",
		 content : "<i class='fa fa-clock-o'></i> <i>Record Deleted successfully...</i>",
		 color : "#659265",
		 iconSmall : "fa fa-check fa-2x fadeInRight animated",
		 timeout : 4000
		 });
 		window.location="sale_add"
 } else {
 	 $.smallBox({
		 title : "Delete Status",
		 content : "<i class='fa fa-clock-o'></i> <i>Problem Deleting Record...</i>",
		 color : "#C46A69",
		 iconSmall : "fa fa-times fa-2x fadeInRight animated",
		 timeout : 4000
		 });
 }
 });
 }
 if (ButtonPressed === "No") {
 $.smallBox({
 title : "Delete Status",
 content : "<i class='fa fa-clock-o'></i> <i>You pressed No...</i>",
 color : "#C46A69",
 iconSmall : "fa fa-times fa-2x fadeInRight animated",
 timeout : 4000
 });
 }
 });
 e.preventDefault();
}

window.item_Name=window.item_BarCode=window.item_id=0;

$(document).ready(function(){
	// $("#client_Name").focus();
})

function saveForm(val){
	$('#save_value').val(val);
	$("#checkout-form").submit();
}

$("#ex_itemcode").keypress(function(e){
	if(e.keyCode==13){ getDBDataItemCodeBlur(); }
});

$("#ex_imei").keypress(function(e){
	if(e.keyCode==13){ getDBData(); }
});

$("#ex_item").keypress(function(e){
	if(e.keyCode==13){ $("#ex_qty").focus(); }
});

$("#ex_saleprice").keypress(function(e){
	if(e.keyCode==13){ $("#ex_discountpercentage").focus(); }
});

$("#ex_discountpercentage").keypress(function(e){
	if(e.keyCode==13){ addToTable(); }
});

$("#ex_rate").keypress(function(e){
	if(e.keyCode==13){ addToTable(); }
});

function postDataForCustomerDsiplay(){
	var item_CostPrice=$("#item_CostPrice").val();
	var item_Rate=$("#item_Rate").val();
	var allVars="item_CostPrice="+item_CostPrice+"&item_Rate="+item_Rate;
	$.ajax({
	 type: "POST",
	 url: "sale_add_json_customer_display.php",
	 dataType: 'json',
	 data:allVars,
	 cache: false,
	 success: function(data){ 
	 	alert(data.msg);
	 }
	});
}

function getDBData(){
	var ex_imei=$("#ex_imei").val();
	if(ex_imei==''){
		alert('Please Choose Item First or Scan Item IMEI');
		$("#ex_imei").focus();
		return false;
	}
	var client_id=$("#client_id").val();
	var allVars="item_IMEI="+ex_imei+"&client_id="+client_id;
	$.ajax({
	 type: "POST",
	 url: "sale_add_json.php",
	 dataType: 'json',
	 data:allVars,
	 cache: false,
	 success: function(data){ 
	 	if(data['msg']=='Y'){
	 		$("#ex_qty").val('1');
	 		$("#ex_rate").val(data.item_SalePrice);
	 		$("#ex_netamount").val(data.item_SalePrice);
	 		$("#ex_costprice").val(data.item_PurchasePrice);
			$("#ex_itemname").val(data.item_Name);
			$("#ex_itemcode").val(data.item_Code);
			$("#ex_item_id_from_imei").val(data.item_id_from_imei);
			$("#ex_item_stock").val(data.item_CurrentStock);
			$("#ex_stock").val(data.item_CurrentStock);
			$("#ex_qtyinpack").val(data.item_QtyInPack);
	 		$("#ex_qty").focus();
			window.newtest=data.item_NetAmount
	 		window.item_Name=data.item_Name;
	 		window.item_BarCode=data.item_BarCode;
	 		window.item_id=data.item_id_from_imei;
	 	}else{
	 		alert(data.msg);
	 	}
	 }
	});
}

function getDBDataItemCodeBlur(){
	var ex_itemcode=$("#ex_itemcode").val();
	if(ex_itemcode==''){
		alert('Please Give Product Code');
		$("#ex_itemcode").focus();
		return false;
	}
	var client_id=$("#client_id").val();
	var allVars="item_Code="+ex_itemcode+"&client_id="+client_id;
	$.ajax({
	 type: "POST",
	 url: "sale_add_json.php",
	 dataType: 'json',
	 data:allVars,
	 cache: false,
	 success: function(data){ 
	 	if(data['msg']=='Y'){
	 		$("#ex_qty").val('1');
	 		$("#ex_rate").val(data.item_SalePrice);
	 		$("#ex_netamount").val(data.item_SalePrice);
	 		$("#ex_costprice").val(data.item_PurchasePrice);
			$("#ex_itemname").val(data.item_Name);
			$("#ex_item_id_from_imei").val(data.item_id_from_imei);
			$("#ex_item_stock").val(data.item_CurrentStock);
			$("#ex_stock").val(data.item_CurrentStock);
			$("#ex_qtyinpack").val(data.item_QtyInPack);
			$("#ex_qty").focus();
			get_Last_Records('itemcode', ex_itemcode);
	 		$('#ex_item').val(data.item_Name);
			window.newtest=data.item_NetAmount
	 		window.item_Name=data.item_Name;
	 		window.item_BarCode=data.item_BarCode;
	 		window.item_id=data.item_id_from_imei;
	 	}else{
	 		alert(data.msg);
	 	}
	 }
	});
}

function get_Last_Records(type, value){
	var item_code=0,item_id=0;
	if(type=='itemcode'){ item_code=value; }
	if(type=='item_id'){ item_id=value; }
	var client_id=$("#client_id").val();
	var allVars="item_Code="+item_code+"&client_id="+client_id+"&item_id="+item_id+"&where_type="+type+"&get_last_records=1";
	$.ajax({
	 type: "POST",
	 url: "sale_add_json_lastrecord.php",
	 dataType: 'json',
	 data:allVars,
	 cache: false,
	 success: function(data){ 
	 	if(data['msg']=='Y'){
	 		$("#show_last_record_sales").html(data.last_sale_records);
	 	}else{
	 		alert(data.msg);
	 	}
	 }
	});
}

function reCalculate(e){
	var extra=$(e).val();
	var thisTr=$(e).closest('tr');
	var net_amount=$(thisTr).find("#item_NetPriceDuplicate").val();
	var net_total=net_amount;
	if(!isNaN(parseFloat(extra)) && parseFloat(extra)!==0){
		extra=parseFloat(extra); net_amount=parseFloat(net_amount);
		net_total=extra+net_amount
	}
	$(thisTr).find('#show_netprice').text(net_total);
	$(thisTr).find('#item_NetPrice').val(net_total);
	calculate();	
}

// IMPORTANT: Set Qty column; default 1 for sets, dash for others
function getNewRowHtml(item) {
    var setQtyCell = '-';
    if (item.set_id) {
        setQtyCell = `<input type="number" class="set_qty" min="1" step="any" value="${(item.set_qty!==undefined && item.set_qty!==''? item.set_qty : 1)}" data-set-id="${item.set_id}" style="width:70px;">`;
    }
    return `
    <tr data-set-id="${item.set_id || ''}" data-unit-per-set="${item.unitPerSet || ''}">
        <td>${item.item_Code || ''}</td>
        <td>${item.item_Name || ''}</td>
        <td>${item.item_Stock || ''}</td>
        <td>${setQtyCell}</td>
        <td>
            <input type="number" name="item_Qty[]" class="item_Qty" value="${item.qty}" min="1" style="width:70px;">
        </td>
        <td>
            <input type="number" name="item_Rate[]" class="item_Rate" value="${item.rate}" min="0" style="width:90px;">
        </td>
        <td>
            <input type="number" name="item_DiscountPrice[]" class="item_DiscountPrice" value="${item.discount}" min="0" style="width:60px;">
        </td>
        <td style="text-align:right;">
            <span class="item_CostPrice_show">${item.costprice}</span>
            <input type="hidden" name="item_CostPrice[]" class="item_CostPrice" value="${item.costprice}">
        </td>
        <td>
            <span class="item_NetPrice_show">${Number(item.netamount || 0).toFixed(2)}</span>
            <input type="hidden" name="item_NetPrice[]" class="item_NetPrice" value="${Number(item.netamount || 0).toFixed(2)}">
        </td>
        <td>
            <p class="btn btn-danger" onclick="delRow(this)">Delete</p>
            <input type="hidden" name="item_id[]" class="item_id" value="${item.item_id}">
            <input type="hidden" name="item_Code[]" value="${item.item_Code}">
            <input type="hidden" name="item_Name[]" value="${item.item_Name}">
        </td>
    </tr>
    `;
}

// Update all items of a set when Set Qty changes
function updateSetQuantities(setId, setQty) {
    if (!setId) return;
    if (isNaN(setQty) || setQty <= 0) setQty = 1;

    isBulkSetUpdate = true;

    // Sync all visible Set Qty inputs
    $('input.set_qty[data-set-id="'+setId+'"]').val(
        (Math.abs(setQty - Math.round(setQty)) < 1e-9) ? Math.round(setQty) : parseFloat(setQty.toFixed(2))
    );

    // Update each item row quantity = setQty × unitPerSet
    $('#u_tbl tbody tr[data-set-id="'+setId+'"]').each(function() {
        var ups = parseFloat($(this).data('unit-per-set')) || 1;
        var newQty = ups * setQty;
        $(this).find('input.item_Qty').val(newQty);
        rowInputChanged($(this).find('input.item_Qty')[0]); // recalculates amount
    });

    isBulkSetUpdate = false;

    calculate();
    totalItems();
}

function addToTable() {
    var ex_itemid = $("#ex_item_id_from_imei").val();
    var ex_itemname = $("#ex_itemname").val();
    var ex_item = $("#ex_item").val();
    var ex_itemcode = $("#ex_itemcode").val();
    var ex_qty = $("#ex_qty").val();
    var ex_rate = $("#ex_rate").val();
    var ex_discount_amount = $("#ex_discount_amount").val();
    var ex_costprice = $("#ex_costprice").val();
    var ex_stock = $("#ex_item_stock").val();

    if (!ex_itemname || ex_itemname.trim() === "") {
        ex_itemname = ex_item;
    }

    if ((!ex_itemname || ex_itemname.trim() === "") && (!ex_itemcode || ex_itemcode.trim() === "")) {
        $.smallBox({
            title: "Error",
            content: "<i class='fa fa-clock-o'></i> <i>Please enter product name or code.</i>",
            color: "#C46A69",
            iconSmall: "fa fa-times fa-2x fadeInRight animated",
            timeout: 4000
        });
        $("#ex_item").focus();
        return false;
    }

    if (!ex_rate || !ex_qty) {
        alert('Please Give Qty and Rate..');
        $("#ex_qty").focus();
        return false;
    }

    var found_duplicate = false;
    $("#u_tbl tbody tr").each(function(index, row) {
        var row_itemid = $(row).find("input.item_id").val();
        var row_itemname = $(row).find("input[name='item_Name[]']").val();
        var row_itemcode = $(row).find("input[name='item_Code[]']").val();

        if ((ex_itemid && row_itemid == ex_itemid && ex_itemid != "") ||
            ((!ex_itemid || ex_itemid == "0") &&
            row_itemname == ex_itemname && row_itemcode == ex_itemcode)) {
            found_duplicate = true;
            var prevQty = parseFloat($(row).find("input.item_Qty").val()) || 0;
            var item_Qty = parseFloat(ex_qty) || 0;
            var prevRate = parseFloat($(row).find("input.item_Rate").val()) || 0;
            var discount = parseFloat($(row).find("input.item_DiscountPrice").val()) || 0;
            var newQty = prevQty + item_Qty;

            $(row).find("input.item_Qty").val(newQty);
            var netAmount = (newQty * prevRate) - discount;
            if (netAmount < 0) netAmount = 0;
            $(row).find("span.item_NetPrice_show").text(netAmount.toFixed(2));
            $(row).find("input.item_NetPrice").val(netAmount.toFixed(2));
            $(row).find('input[name="item_Qty[]"]').val(newQty);

            calculate();
            totalItems();
        }
    });

    if (found_duplicate) {
        $("#ex_imei, #ex_itemcode, #ex_item, #ex_qty, #ex_rate, #ex_costprice, #ex_netamount, #ex_itemname, #ex_item_id_from_imei, #ex_item_stock, #ex_stock, #ex_discount_amount").val("");
        $('#show_last_record_sales').html('');
        $("#ex_itemcode").focus();
        return false;
    }

    var qty = parseFloat(ex_qty) || 1;
    var rate = parseFloat(ex_rate) || 0;
    var discount = parseFloat(ex_discount_amount) || 0;
    var netAmount = (qty * rate) - discount;
    if (netAmount < 0) netAmount = 0;

    var item = {
        item_Code: ex_itemcode,
        item_Name: ex_itemname,
        item_Stock: ex_stock,
        set_id: '',            // normal item
        unitPerSet: '',        // normal item
        set_qty: '',           // show dash
        qty: qty,
        rate: rate,
        discount: discount,
        costprice: ex_costprice,
        netamount: netAmount,
        item_id: ex_itemid
    };

    if (!$('#u_tbl tbody').length) {
        $('#u_tbl').append('<tbody></tbody>');
    }
    $('#u_tbl tbody').append(getNewRowHtml(item));

    calculate();
    totalItems();
    playProductAddSound();

    $("#ex_imei, #ex_itemcode, #ex_item, #ex_qty, #ex_rate, #ex_costprice, #ex_netamount, #ex_itemname, #ex_item_id_from_imei, #ex_item_stock, #ex_stock, #ex_discount_amount").val("");
    $('#show_last_record_sales').html('');
    $("#ex_itemcode").focus();
}

function delRow(e) {
    $(e).closest('tr').remove();
    calculate();
    totalItems();
}

function checkDuplicate(){
	var error=0;
	var ex_imei=$("#ex_imei").val();
	if(ex_imei!==''){
		$("#u_tbl tr #item_IMEI").each(function(index,elem){
			if(ex_imei==elem.value){
				alert("Duplicate Entry For this IMEI ..");
				$("#ex_imei").val('');
				error++;
			}
		});
	}
	return !error;
}

function checkDuplicateItem(){ return true; }
function updateQty(e){ return false; }

$(document).ready(function(){
    // 1. Page open par PRODUCT CODE field par focus ho
    $("#ex_itemcode").focus();

    // 2. Enter on product code → search product
    $("#ex_itemcode").on('keydown', function(e){
        if(e.key === "Enter") {
            $("#ex_item").focus().select();
            e.preventDefault();
        }
    });

    // 3. Enter on search product → quantity
    $("#ex_item").on('keydown', function(e){
        if(e.key === "Enter") {
            getItemDetail(); // lock prevents duplicate
            $("#ex_qty").focus().select();
            e.preventDefault();
        }
    });

    // 4. Enter on quantity → rate
    $("#ex_qty").on('keydown', function(e){
        if(e.key === "Enter") {
            $("#ex_rate").focus().select();
            e.preventDefault();
        }
    });

    // 5. Enter on rate → add
    $("#ex_rate").on('keydown', function(e){
        if(e.key === "Enter") {
            addToTable();
            e.preventDefault();
        }
    });
});

function removeBlankRowsFromTable() {
    $('#u_tbl tbody tr').each(function() {
        var nameVal = $(this).find("input[name='item_Name[]']").val();
        var idVal = $(this).find("input[name='item_id[]']").val();
        var qtyVal = $(this).find("input[name='item_Qty[]']").val();
        if ((!nameVal || nameVal.trim() === "") ||
            (!idVal || idVal.trim() === "") ||
            (!qtyVal || qtyVal.trim() === "")) {
            $(this).remove();
        }
    });
}

$(document).ready(function() {
   $('#ex_itemcode').on('keydown', function(e) {
        if(e.key === "Enter" || e.keyCode === 13) {
            var code = $(this).val().trim();
            if(code !== "") {
                $.ajax({
                    type: "GET",
                    url: "ajax/get_item_detail.php",
                    dataType: 'json',
                    data: {item_code: code},
                    success: function(data) {
                        // Product found
                        if (data && data.item_id) {
                            $("#ex_item").val(data.item_Name || '');
                            $("#ex_item_id_from_imei").val(data.item_id || '');
                            $("#ex_item_stock").val(data.stock || data.item_CurrentStock || '');
                            $("#ex_stock").val(data.stock || data.item_CurrentStock || '');
                            $("#ex_qtyinpack").val(data.qty_pack || data.item_QtyInPack || '');
                            $("#ex_rate").val(data.sale_price || data.item_SalePrice || '');
                            $("#ex_costprice").val(data.cost_price || data.item_PurchasePrice || '');
                            $("#ex_item").focus().select();
                            setTimeout(function(){
                                $("#ex_qty").focus().select();
                            }, 50);
                        }
                        // Set found
                        else if (data && data.is_set && data.set_id) {
                            $.post('get_set_items.php', { set_id: data.set_id, mode: 'sale' }, function(setData){
                                var items = [];
                                try { items = JSON.parse(setData); } catch(e) { }
                                items.forEach(function(row){
                                    addOrUpdateTableRow(row, { set_id: data.set_id, set_name: data.set_name || '' });
                                });
                                $('#ex_item').val('');
                                calculate();
                                totalItems();
                                $("#ex_qty").focus().select();
                            });
                        }
                        else {
                            alert("Product or Set not found!");
                        }
                    },
                    error: function() {
                        alert("Error fetching product/set details!");
                    }
                });
            }
            e.preventDefault();
        }
    });

    // Product Name Enter → Qty field
    $("#ex_item").on('keydown', function(e){
        if(e.key === "Enter" || e.keyCode === 13) {
            getItemDetail();
            $("#ex_qty").focus().select();
            e.preventDefault();
        }
    });

    // Qty Enter → Unit Price field
    $("#ex_qty").on('keydown', function(e){
        if(e.key === "Enter" || e.keyCode === 13) {
            $("#ex_rate").focus().select();
            e.preventDefault();
        }
    });

    // Unit Price Enter → Table me add ho jaye
    $("#ex_rate").on('keydown', function(e){
        if(e.key === "Enter" || e.keyCode === 13) {
            addToTable();
            e.preventDefault();
        }
    });
});

function playProductAddSound() {
    var audio = document.getElementById('product-add-sound');
    if(audio) {
        audio.currentTime = 0; // always play from start
        audio.play();
    }
}