<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Purchase List";
include ("inc/header.php");
include ("inc/nav.php");

$branch_id = (int)($_SESSION['branch_id'] ?? 0);
$debug = isset($_GET['debug']) && $_GET['debug'] == '1';

// tiny helper
function table_exists_local($con, $name){
    $name = mysqli_real_escape_string($con, $name);
    $res = mysqli_query($con, "SHOW TABLES LIKE '{$name}'");
    if ($res === false) return false;
    $ok = mysqli_num_rows($res) > 0;
    mysqli_free_result($res);
    return $ok;
}

// Validate p_id
if(!isset($_GET['p_id']) || !is_numeric($_GET['p_id'])) {
    echo "<div class='alert alert-danger'>Invalid request: Purchase ID missing.</div>";
    include ("inc/footer.php");
    include ("inc/scripts.php");
    exit;
}

$p_id = (int)$_GET['p_id'];

// Optional joins/columns
$has_brand = table_exists_local($con, 'adm_brand');
$brandSelect = $has_brand ? "B.brand_Name AS brand_Name" : "'' AS brand_Name";
$brandJoin   = $has_brand ? "LEFT  JOIN adm_brand AS B             ON B.brand_id = I.brand_id" : "";

// Branch condition only if positive
$BR = ($branch_id > 0) ? " AND P.branch_id = {$branch_id} " : "";

/*
  NOTE:
  We use accounts_chart (suppliers stored there). adm_supplier does not exist in your dump.
*/
$sql = "
SELECT 
    P.p_id,
    P.p_Number,
    P.p_CreatedOn,
    P.p_Date,
    P.p_BillNo,
    P.p_VendorRemarks,
    P.sup_id,
    P.p_TotalAmount,
    P.p_DiscountPrice,
    P.p_NetAmount,
    P.p_Remarks,
    P.p_TotalItems,
    S.account_title AS sup_Name,
    PD.item_id,
    PD.item_BarCode,
    PD.item_IMEI,
    PD.item_Qty,
    PD.item_Rate,
    PD.item_TotalAmount,
    PD.item_DiscountPercentage,
    PD.item_DiscountAmount,
    PD.item_NetAmount,
    I.item_Name,
    I.item_Code,
    {$brandSelect},
    (
      SELECT IFNULL(SUM(pp.pp_Amount),0)
      FROM adm_purchase_payment pp
      WHERE (pp.p_id = P.p_id OR pp.pr_id = P.p_id)
    ) AS paid_amount
FROM adm_purchase AS P
INNER JOIN adm_purchase_detail AS PD ON PD.p_id = P.p_id
LEFT  JOIN accounts_chart AS S        ON S.account_id = P.sup_id
LEFT  JOIN adm_item AS I              ON I.item_id = PD.item_id
{$brandJoin}
WHERE P.p_id = {$p_id}
{$BR}
ORDER BY I.item_id
";

$result = mysqli_query($con, $sql);

if($result === false){
    echo "<div class='alert alert-danger'>Database query failed.</div>";
    if($debug){
        echo "<pre style='white-space:pre-wrap;color:#b00;font-size:12px;'><strong>MySQL Error:</strong> "
            . htmlspecialchars(mysqli_error($con)) . "\n\n<strong>SQL:</strong>\n"
            . htmlspecialchars($sql) . "</pre>";
    } else {
        echo "<div style='font-size:12px;color:#555;'>Append &debug=1 to the URL to see the error.</div>";
    }
    include ("inc/footer.php");
    include ("inc/scripts.php");
    exit;
}

if(mysqli_num_rows($result) < 1){
    echo "<div class='alert alert-warning'>No purchase detail found for this ID."
       . ($branch_id > 0 ? " Tip: your session branch_id is {$branch_id}; remove branch filter or switch branch if needed." : "")
       . "</div>";
    include ("inc/footer.php");
    include ("inc/scripts.php");
    exit;
}

$rows = [];
while($r = mysqli_fetch_assoc($result)){
    $rows[] = $r;
}
$base = $rows[0];

// Currency symbol fallback
if(!isset($currency_symbol) || $currency_symbol === '') $currency_symbol = 'Rs. ';

$paidAmount      = (float)$base['paid_amount'];
$netAmount       = (float)$base['p_NetAmount'];
$remainingAmount = $netAmount - $paidAmount;
if($remainingAmount < 0) $remainingAmount = 0;

// Choose display datetime
$displayDT = $base['p_CreatedOn'] ?: $base['p_Date'];
?>
<div id="main" role="main">
    <?php
        $breadcrumbs["Purchase List"] = "";
        include("inc/ribbon.php");
    ?>
    <style>
        .form-control { border-radius:5px!important; box-shadow:none!important; font-size:12px; }
        .summary-table th {
            background:#d65252; color:#fff; text-align:center; font-weight:600; font-size:13px;
        }
        .summary-table td { text-align:center; font-size:12px; vertical-align:middle!important; }
        .badge-total { background:#337ab7; color:#fff; padding:2px 8px; border-radius:12px; font-size:11px; }
        .badge-paid { background:#5cb85c; color:#fff; padding:2px 8px; border-radius:12px; font-size:11px; }
        .badge-remaining { background:#f0ad4e; color:#fff; padding:2px 8px; border-radius:12px; font-size:11px; }
        .notes-table th { background:#f7f7f7; text-align:center; width:30%; font-size:12px; }
        .notes-table td { font-size:12px; }
        #datatable_fixed_column thead input { width:100%; }
    </style>

    <div id="content">
        <section id="widget-grid">
            <div class="row">
                <article class="col-xs-12">
                    <div class="jarviswidget jarviswidget-color-purity" id="wid-id-1" data-widget-editbutton="false">
                        <header>
                            <span class="small_icon"><i class="fa fa-tags"></i></span>
                            <h2>Purchase Detail</h2>
                        </header>
                        <div>
                            <table class="table table-condensed table-bordered summary-table">
                                <tr>
                                    <th>Bill No.</th>
                                    <th>Date & Time</th>
                                    <th>Reference No.</th>
                                    <th>Bill Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Remaining Amount</th>
                                </tr>
                                <tr>
                                    <td><?php echo htmlspecialchars($base['p_Number'] ?? ''); ?></td>
                                    <td><?php echo $displayDT ? date('d-m-Y H:i:s', strtotime($displayDT)) : ''; ?></td>
                                    <td><?php echo htmlspecialchars($base['p_BillNo'] ?? ''); ?></td>
                                    <td><?php echo $currency_symbol . number_format($netAmount,2); ?><div><span class="badge-total">Total</span></div></td>
                                    <td><?php echo $currency_symbol . number_format($paidAmount,2); ?><div><span class="badge-paid">Paid</span></div></td>
                                    <td><?php echo $currency_symbol . number_format($remainingAmount,2); ?><div><span class="badge-remaining">Remaining</span></div></td>
                                </tr>
                            </table>

                            <table class="table table-condensed table-bordered notes-table" style="width:36%; float:right;">
                                <tr>
                                    <th>Purchase Note</th>
                                    <td><?php echo nl2br(htmlspecialchars($base['p_Remarks'] ?? '')); ?></td>
                                </tr>
                                <tr>
                                    <th>Vendor Note</th>
                                    <td><?php echo nl2br(htmlspecialchars($base['p_VendorRemarks'] ?? '')); ?></td>
                                </tr>
                                <tr>
                                    <th>Supplier</th>
                                    <td><?php echo htmlspecialchars($base['sup_Name'] ?? ''); ?></td>
                                </tr>
                            </table>

                            <div style="clear:both;"></div>

                            <div class="widget-body no-padding">
                                <table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="hasinput"><input type="text" class="form-control" placeholder="Product Code" /></th>
                                            <th class="hasinput"><input type="text" class="form-control" placeholder="Product Name" /></th>
                                            <th class="hasinput"><input type="text" class="form-control" placeholder="IMEI" /></th>
                                            <th class="hasinput"><input type="text" class="form-control" placeholder="Qty" /></th>
                                            <th class="hasinput"><input type="text" class="form-control" placeholder="Unit Cost" /></th>
                                            <th class="hasinput"><input type="text" class="form-control" placeholder="Line Total" /></th>
                                        </tr>
                                        <tr>
                                            <th style="text-align:center;">Product Code</th>
                                            <th style="text-align:center;">Product Name</th>
                                            <th style="text-align:center;">IMEI No.</th>
                                            <th style="text-align:center;">Total Quantity</th>
                                            <th style="text-align:center;">Unit Cost Price</th>
                                            <th style="text-align:center;">Total Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($rows as $r): ?>
                                            <tr>
                                                <td style="text-align:center;"><?php echo htmlspecialchars($r['item_Code'] ?? ''); ?></td>
                                                <td style="text-align:center;"><?php echo htmlspecialchars($r['item_Name'] ?? ''); ?></td>
                                                <td style="text-align:center;"><?php echo htmlspecialchars($r['item_IMEI'] ?? ''); ?></td>
                                                <td style="text-align:center;"><?php echo number_format((float)$r['item_Qty'], 2); ?></td>
                                                <td style="text-align:center;"><?php echo number_format((float)$r['item_Rate'], 2); ?></td>
                                                <td style="text-align:center;"><?php echo $currency_symbol . number_format((float)$r['item_NetAmount'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div><!-- /.widget-body -->
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </div>
</div>

<?php include ("inc/footer.php"); ?>
<?php include ("inc/scripts.php"); ?>

<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatables/dataTables.colVis.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatables/dataTables.tableTools.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatables/dataTables.bootstrap.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/datatable-responsive/datatables.responsive.min.js"></script>

<script>
$(function(){
    var responsiveHelper;
    var breakpointDefinition = { tablet:1024, phone:480 };

    var otable = $('#datatable_fixed_column').DataTable({
        sDom: "<'dt-toolbar'<'col-sm-6 hidden-xs'f><'col-sm-6 hidden-xs'<'toolbar'>>r>"+
              "t"+
              "<'dt-toolbar-footer'<'col-sm-6 hidden-xs'i><'col-sm-6'p>>",
        autoWidth: true,
        preDrawCallback: function(){
            if(!responsiveHelper){
                responsiveHelper = new ResponsiveDatatablesHelper($('#datatable_fixed_column'), breakpointDefinition);
            }
        },
        rowCallback: function(nRow){
            responsiveHelper.createExpandIcon(nRow);
        },
        drawCallback: function(oSettings){
            responsiveHelper.respond();
        }
    });

    $('#datatable_fixed_column thead th input[type=text]').on('keyup change', function(){
        otable
            .column($(this).parent().index()+':visible')
            .search(this.value)
            .draw();
    });
});
</script>
<?php include ("inc/google-analytics.php"); ?>