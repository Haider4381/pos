<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Products List";
include ("inc/header.php");
include ("inc/nav.php");
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

<?php $breadcrumbs["Inventory"] = "";
include("inc/ribbon.php");
?>

<style>
    .form-control {
        border-radius: 5px !important;
        box-shadow: none!important;
        -webkit-box-shadow: none!important;
        -moz-box-shadow: none!important;
        font-size: 12px;
        padding-left: 10px;
    }
    label {
        margin-top: 8px !important;
    }
    textarea.form-control {
        height: 70px;
    }
    .select2-container .select2-choice {
        display: block;
        height: 32px;
        padding: 0 0 0 8px;
        overflow: hidden;
        position: relative;
        border: 1px solid #ccc;
        white-space: nowrap;
        line-height: 32px;
        color: #444;
        text-decoration: none;
        background-clip: padding-box;
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        background-color: #fff;
        border-radius: 5px;
        width: 225px;
    }
    .product-thumb {
        height: 36px;
        width: 36px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #bdbdbd;
        cursor: pointer;
        transition: 0.2s;
    }
    .product-thumb:hover {
        box-shadow: 0 0 7px #6bbfff;
        border: 1.5px solid #007bff;
    }
</style>
<!-- MAIN CONTENT -->
<div id="content">
    <section id="widget-grid" class="">
        <div class="row">
            <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="jarviswidget jarviswidget-color-purity" id="wid-id-1" data-widget-editbutton="false">
                    <header>
                        <span class="small_icon"><i class="fa fa-edit"></i></span>
                        <h2>Products list</h2>
                    </header>
                    <div>
<div class="widget-body no-padding">
    <div class="tab-content">
        <table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">
            <thead>
                <tr>
                    <th class="hasinput" style="width:7%">
                        <input type="text" class="form-control" placeholder="Image" disabled />
                    </th>
                    <th class="hasinput" style="width:14%">
                        <input type="text" class="form-control" placeholder="Product Code"  />
                    </th>
                    <th class="hasinput" style="width:20%">
                        <input type="text" class="form-control" placeholder="Product Name" />
                    </th>
                    <th class="hasinput" style="width:3%" >
                        <input type="text" placeholder="Purchase Price" class="form-control">
                    </th>
                    <th class="hasinput" style="width:3%" >
                        <input type="text" placeholder="Sale Price" class="form-control">
                    </th>
                    <th class="hasinput" style="width:12%" >
                        <input type="text" placeholder="Main Category" class="form-control">
                    </th>
                    <th class="hasinput" style="width:10%" >
                        <input type="text" placeholder="Sub Category" class="form-control">
                    </th>
                    <th class="hasinput" style="width:5%" >
                        <input type="text" placeholder="Used" class="form-control">
                    </th>
                    <th class="hasinput" style="width:8%" >
                        <input type="text" placeholder="In Stock" class="form-control">
                    </th>
                    <th class="hasinput" style="width:18%"></th>
                </tr>
                <tr>
                    <th style="text-align:center;">Image</th>
                    <th style="text-align:center;">Product Code</th>
                    <th style="text-align:center;">Product Name</th>
                    <th style="text-align:center;">Purchase Price</th>
                    <th style="text-align:center;">Sale Price</th>
                    <th style="text-align:center;">Main Category</th>
                    <th style="text-align:center;">Sub Category</th>
                    <th style="text-align:center;">Used</th>
                    <th style="text-align:center;">In Stock</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
<?php
$item_used=0;
$itemsArray=get_Items();
foreach ($itemsArray as $key => $itemRow)
{
    $item_id=$itemRow['item_id'];
    $DQ="
    SELECT ifnull(SUM(item_used),0) as item_used, item_id
    FROM
    (
        SELECT COUNT(*) as item_used, item_id FROM cust_sale_detail GROUP  by item_id
        UNION ALL
        SELECT COUNT(*) as item_used, item_id FROM adm_purchase_detail  GROUP  by item_id
    ) as c
    WHERE item_id=$item_id";
    $DQR=mysqli_query($con,$DQ);
    $used_row=mysqli_fetch_assoc($DQR);
    $item_used=$used_row['item_used'];

    $stockQ="
    SELECT ifnull(SUM(item_qty),0) as item_stock
    FROM
    (
        SELECT 0-item_Qty as item_qty FROM cust_sale_detail WHERE item_id=$item_id
        UNION ALL
        SELECT item_Qty as item_qty FROM adm_purchase_detail WHERE item_id=$item_id
        UNION ALL
        SELECT item_Qty as item_qty FROM cust_salereturn_detail WHERE item_id=$item_id
    ) as c";
    $stockQR=mysqli_query($con,$stockQ);
    $stock_row=mysqli_fetch_assoc($stockQR);
    $item_stock=$stock_row['item_stock'];

    // Image logic
    $img_path = (isset($itemRow['item_Image']) && file_exists($itemRow['item_Image']) && $itemRow['item_Image']!="") ? $itemRow['item_Image'] : "img/demo-img.png";
    $img_full = (isset($itemRow['item_Image']) && file_exists($itemRow['item_Image']) && $itemRow['item_Image']!="") ? $itemRow['item_Image'] : "img/demo-img.png";
?>        
<tr id='row<?=$item_id?>'>
    <td style="text-align:center;">
        <img src="<?=$img_path?>" class="product-thumb" onclick="openImagePopup('<?=$img_full?>')" title="Click to view full image" alt="Product Image"/>
    </td>
    <td><?php echo $itemRow['item_Code'];?> </td>
    <td><b><?php echo $itemRow['item_Name'];?></b></td>
    <td style="text-align:center;"><input type="number" class="form-control" id="rate_purchase_<?=$itemRow['item_id']?>" value="<?=$itemRow['item_PurchasePrice'];?>" onkeyup="update_rate(<?=$itemRow['item_id']?>,'item_PurchasePrice');" style="width:100px;"></td>
    <td style="text-align:center;"><input type="number" class="form-control" id="rate_sale_<?=$itemRow['item_id']?>" value="<?=$itemRow['item_SalePrice'];?>" onkeyup="update_rate(<?=$itemRow['item_id']?>,'item_SalePrice');" style="width:100px;"></td>
    <td style="text-align:center;"><?php echo $itemRow['icat_name'];?></td>
    <td style="text-align:center;"><?php echo $itemRow['isubcat_name'];?></td>
    <td style="text-align:center;"><?php echo $item_used;?></td>
    <td style="text-align:center;"><b style="font-size: 16px;"><?php echo $item_stock;?></b></td>
    <td>
        <a href="item_add.php?id=<?php echo $itemRow['item_id'];?>" class="btn btn-primary" style="width: 40%; font-size: 12px;padding: 4px;}">Edit</a>
        <?php if($item_used==0) { ?>
        <a href="javascript:del(<?php echo $itemRow['item_id'];?>)" class="btn btn-danger" style="width: 50%; font-size: 12px;padding: 4px;">Delete</a>
        <?php } ?>
        <button onclick="printbarcode(<?=$itemRow['item_id'];?>)"  class="btn btn-xs btn-warning"  >Barcode</button>
    </td>
</tr>
<?php } ?>
            </tbody>
        </table>
    </div>
</div><!--End of tab-content-->
                    </div>
                </div>
            </article>
        </div>
    </section>
</div>
<!-- END MAIN CONTENT -->
</div>
<!-- END MAIN PANEL -->

<?php include ("inc/footer.php"); ?>
<?php include ("inc/scripts.php"); ?>

<!-- PAGE RELATED PLUGIN(S) -->
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/dataTables.colVis.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/dataTables.tableTools.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/dataTables.bootstrap.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatable-responsive/datatables.responsive.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    var responsiveHelper_dt_basic = undefined;
    var responsiveHelper_datatable_fixed_column = undefined;
    var responsiveHelper_datatable_col_reorder = undefined;
    var responsiveHelper_datatable_tabletools = undefined;
    var breakpointDefinition = {
        tablet : 1024,
        phone : 480
    };

    $('#dt_basic').dataTable({
        "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
            "t"+
            "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
        "autoWidth" : true,
        "preDrawCallback" : function() {
            if (!responsiveHelper_dt_basic) {
                responsiveHelper_dt_basic = new ResponsiveDatatablesHelper($('#dt_basic'), breakpointDefinition);
            }
        },
        "rowCallback" : function(nRow) {
            responsiveHelper_dt_basic.createExpandIcon(nRow);
        },
        "drawCallback" : function(oSettings) {
            responsiveHelper_dt_basic.respond();
        }
    });

    var otable = $('#datatable_fixed_column').DataTable({
        "bPaginate": false,
        "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6 hidden-xs'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>r>"+
            "t"+
            "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
        "autoWidth" : true,
        "preDrawCallback" : function() {
            if (!responsiveHelper_datatable_fixed_column) {
                responsiveHelper_datatable_fixed_column = new ResponsiveDatatablesHelper($('#datatable_fixed_column'), breakpointDefinition);
            }
        },
        "rowCallback" : function(nRow) {
            responsiveHelper_datatable_fixed_column.createExpandIcon(nRow);
        },
        "drawCallback" : function(oSettings) {
            responsiveHelper_datatable_fixed_column.respond();
        }       
    });

    $("#datatable_fixed_column thead th input[type=text]").on( 'keyup change', function () {
        otable
        .column( $(this).parent().index()+':visible' )
        .search( this.value )
        .draw();
    } );
});

// IMAGE POPUP FUNCTION
function openImagePopup(imgPath) {
    if(imgPath && imgPath !== "") {
        var win = window.open("", "_blank");
        win.document.write('<title>Product Image</title>');
        win.document.write('<style>body{margin:0;background:#222;text-align:center;}img{margin:20px auto;max-width:95vw;max-height:95vh;display:block;box-shadow:0 0 20px #000;}</style>');
        win.document.write('<img src="'+imgPath+'" alt="Product Image" />');
    }
}

function del(val){
    $.SmartMessageBox({
        title : "Attention required!",
        content : "This is a confirmation box. Do you want to delete the Record?",
        buttons : '[No][Yes]'
    }, function(ButtonPressed) {
        if (ButtonPressed === "Yes") {
            $.post("ajax/delAjax.php",
            {
                item_id : val, 
            },
            function(data,status){ 
                if(data.trim()!="")
                {
                    $('#row'+val).remove();
                    $.smallBox({
                        title : "Delete Status",
                        content : "<i class='fa fa-clock-o'></i> <i>Record Deleted successfully...</i>",
                        color : "#659265",
                        iconSmall : "fa fa-check fa-2x fadeInRight animated",
                        timeout : 4000
                    });
                }
                else
                {
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

function update_rate(item_id, rate_type)
{
    rate=0;
    if(rate_type=='item_SalePrice')
    {
        rate=$("#rate_sale_"+item_id).val();
    }
    if(rate_type=='item_PurchasePrice')
    {
        rate=$("#rate_purchase_"+item_id).val();
    }
    var allVars="rate_type="+rate_type+"&rate="+rate+"&item_id="+item_id;
    $.ajax
    ({
        type: "POST",
        url: "item_list_ajax.php",
        dataType: 'json',
        data:allVars,
        cache: false,
        success: function(data)
        {
            if(data['msg']=='Y')
            {
                ////// 
            }
            else
            {
                alert('Problem in Updating Data;');
            }
        }
    });
}

function printbarcode(item_id, wh_id)
{
    var qty = prompt("How many barcode", "5");
    var expiry = 'expiry';
    var size=1;
    if(qty)
    {
        window.open('barcodeprint.php?item_id='+item_id+'&r='+qty+'&size='+size+'&expiry='+expiry, '_blank');
    }
}
</script>