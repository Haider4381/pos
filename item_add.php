<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Products";
include ("inc/header.php");
include ("inc/nav.php");
$u_id = $_SESSION['u_id'];
$branch_id = $_SESSION['branch_id'];
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">

<?php $breadcrumbs["New"] = "";
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
    #img {
        max-height:200px;
        max-width:250px;
        display: none;
    }
    .img-remove-link {
        color: #a33e3e;
        font-weight: bold;
        cursor: pointer;
        margin-left: 15px;
        font-size: 13px;
    }
    .img-remove-link:hover {
        text-decoration: underline;
    }
</style>

<!-- MAIN CONTENT -->
<div id="content">
    <section id="widget-grid" class="">
        <div class="row">
            <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="jarviswidget jarviswidget-color-purity" id="wid-id-1" data-widget-editbutton="false">
                    <header>	
                        <span class="small_icon"><i class="fa fa-circle-o-notch"></i>	</span>	
                        <h2>Product</h2>
                    </header>
                    <div>
<script type="text/javascript">
// Show preview of selected image
function readURL(input){
    checkItemImage();
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('img').style.display = "block";
            $('#img').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Remove image functionality for edit case
function removeImage() {
    $("#remove_image_flag").val('1');
    $("#img").attr('src','img/demo-img.png');
    $("#img").show();
    $("#img_row_actual").hide();
    $("#item_PicPath").val('');
    $("#img_remove_link").hide();
}
</script>

<div class="widget-body no-padding">
    <div class="tab-content" >
<?php
if(isset($_POST['submit']))
{
    $item_Name=validate_input($_POST['item_Name']);
    $item_Code=validate_input($_POST['item_Code']);
    $item_CodeSr=validate_input($_POST['item_CodeSr']);
    $item_Status=validate_input($_POST['item_Status']);
    $item_Remarks=validate_input($_POST['item_Remarks']);
    $item_SalePrice=validate_input($_POST['item_SalePrice']);
    $item_PurchasePrice=test_input($_POST['item_PurchasePrice']);
    $item_MinQty=validate_input($_POST['item_MinQty']);
    $item_MaxQty=validate_input($_POST['item_MaxQty']);
    $item_PicPath = "";

    // --- IMAGE UPLOAD block (jpg/png allowed) ---
    if(isset($_FILES['item_PicPath']) && $_FILES['item_PicPath']['name']!="")
    {
        $item_PicPathName = $_FILES['item_PicPath']['name'];
        $item_PicPathTmpName = $_FILES['item_PicPath']['tmp_name'];
        $ext = strtolower(pathinfo($item_PicPathName, PATHINFO_EXTENSION));
        if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
            $newFileName = 'product_'.date("YmdHis").rand(10,99).".".$ext;
            $item_PicPath = "uploads/images/".$newFileName;
            if (!is_dir('uploads/images')) { mkdir('uploads/images', 0777, true); }
            move_uploaded_file($item_PicPathTmpName, $item_PicPath);
        }
    }

    $q="INSERT INTO adm_item (item_Name, item_Code, item_CodeSr, item_Status, item_Remarks, item_PurchasePrice, item_SalePrice, item_MinQty, item_MaxQty, u_id, branch_id, item_Image)
        VALUES('$item_Name','$item_Code','$item_CodeSr','$item_Status','$item_Remarks','$item_PurchasePrice','$item_SalePrice','$item_MinQty','$item_MaxQty', '$u_id', '$branch_id','$item_PicPath')";
    
    if(mysqli_query($con,$q))
    {
        $_SESSION['msg']="Product Created successfully";
    }
    else
    {
        $_SESSION['msg']="Problem creating Product";
    }
    echo '<script> window.location="";</script>';
    die();
}

if(isset($_POST['update']))
{
    $item_id=validate_input($_POST['item_id']);
    $item_Name=validate_input($_POST['item_Name']);
    $item_Code=validate_input($_POST['item_Code']);
    $item_Status=validate_input($_POST['item_Status']);
    $item_Remarks=validate_input($_POST['item_Remarks']);
    $item_SalePrice=validate_input($_POST['item_SalePrice']);
    $item_PurchasePrice=test_input($_POST['item_PurchasePrice']);
    $item_MinQty=validate_input($_POST['item_MinQty']);
    $item_MaxQty=validate_input($_POST['item_MaxQty']);
    $item_PicPath=$_POST['item_PicPathOld'];
    $remove_image_flag = isset($_POST['remove_image_flag']) ? $_POST['remove_image_flag'] : '0';

    // Remove image if user requested
    if($remove_image_flag == "1") {
        // Delete image file from server (optional)
        if(file_exists($item_PicPath) && !empty($item_PicPath)) {
            @unlink($item_PicPath);
        }
        $item_PicPath = "";
    }

    // If new image is uploaded, overwrite previous image
    if(isset($_FILES['item_PicPath']) && $_FILES['item_PicPath']['name']!="" )
    {
        $item_PicPathName=$_FILES['item_PicPath']['name'];
        $item_PicPathTmpName=$_FILES['item_PicPath']['tmp_name'];
        $ext = strtolower(pathinfo($item_PicPathName, PATHINFO_EXTENSION));
        if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
            $newFileName = 'product_'.date("YmdHis").rand(10,99).".".$ext;
            $item_PicPath = "uploads/images/".$newFileName;
            if (!is_dir('uploads/images')) { mkdir('uploads/images', 0777, true); }
            move_uploaded_file($item_PicPathTmpName, $item_PicPath);
        }
    }

    $q="UPDATE adm_item
        SET
        item_Name='$item_Name',
        item_Status='$item_Status',item_Remarks='$item_Remarks',
        item_PurchasePrice='$item_PurchasePrice',
        item_SalePrice='$item_SalePrice',
        item_MinQty='$item_MinQty',
        item_Image='$item_PicPath',
        item_MaxQty='$item_MaxQty'
        WHERE item_id=$item_id";
    if(mysqli_query($con,$q)) { 
        $_SESSION['msg']="Item Updated successfully";
        echo "<script>window.location='item_add.php';</script>";
        die();
    } else { $_SESSION['msg']="Problem Updating Item"; } 
} 
?>

<?php if(!empty($_SESSION['msg'])){ ?>
    <div class="alert alert-info">
    <?php echo $_SESSION['msg']; ?> 
    </div> 
<?php unset($_SESSION['msg']); } ?>

<?php if(!isset($_GET['id'])) {
    $item_Code_Q=mysqli_fetch_assoc(mysqli_query($con, "select (ifnull(MAX(item_CodeSr),0)+1) as item_CodeSr from adm_item where branch_id=$branch_id"));
    $item_CodeSr=$item_Code_Q['item_CodeSr'];
    $item_CodeSrPAD = str_pad($item_CodeSr, 3, "0", STR_PAD_LEFT);
    $code_prefix='PROC';
    $item_Code=$code_prefix.$item_CodeSrPAD;
?>
    <form id="checkout-form" class="smart-form" method="POST" enctype="multipart/form-data" onsubmit="return checkParameters();">	
    <fieldset>
    <input type="hidden" name="item_CodeSr" value="<?=$item_CodeSr?>" />
    <div class="row" style="margin-bottom: 5px;">
        <div class="col col-md-7">       
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-md-2"><label>Product Name:</label></div>
                <div class="col col-md-4"><input type="text" name="item_Name" id="item_Name" class="form-control" placeholder="Enter Product Name"></div>
                <div class="col col-md-2"><label>Product Code :</label></div>
                <div class="col col-md-4"><input type="text" name="item_Code" class="form-control" Placeholder="Product Code"  value="<?=$item_Code?>"></div>
            </div>
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-md-2"><label>Purchase Price:</label></div>
                <div class="col col-md-4"><input type="number" step="0.01" class="form-control" name="item_PurchasePrice" Placeholder="Enter Purchase Price"></div>
                <div class="col col-md-2"><label>Sale Price:</label></div>
                <div class="col col-lg-4"><input type="number"  step="0.01" class="form-control" name="item_SalePrice" Placeholder="Enter Sale Price"></div>
            </div>
            <div class="row" style="margin-bottom: 5px; display:none;">
                <div class="col col-md-2"><label>Min Qty:</label></div>
                <div class="col col-md-4"><input type="number" class="form-control" name="item_MinQty"></div>
                <div class="col col-md-2"><label>Max Qty:</label></div>
                <div class="col col-lg-4"><input type="number" class="form-control" name="item_MaxQty"></div>
            </div> 
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-md-2"><label>Notes:</label></div>
                <div class="col col-md-10"><textarea name="item_Remarks" class="form-control" style="max-width:613px; max-height:55px;" placeholder="Enter Detail About Product"></textarea> </div>
            </div>	
        </div>
        <div class="col col-md-5">
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-md-2"><label>Status:</label></div>
                <div class="col col-md-6">
                    <select name="item_Status" class="form-control"> 
                        <option value="A">Active</option>
                        <option value="I">In-Active</option>
                    </select>
                </div>
            </div>
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-md-2"><label>Picture:</label></div>
                <div class="col col-md-6">
                    <input type="file" name="item_PicPath" id="item_PicPath" onchange="readURL(this)" class="form-control" accept="image/jpeg, image/png, image/jpg">
                </div>
            </div>
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-md-2">&nbsp;</div>
                <div class="col col-md-6">
                    <img src="" id="img" style="max-height:200px; max-width:250px; display:none;">
                </div>
            </div>
        </div>
    </div>
    </fieldset>
    <footer>
        <button type="submit" class="btn btn-primary" name="submit">	Save </button>
    </footer>
    </form>
<?php
}
else
{
    $item_id=(int) $_GET['id'];
    $itemQ="SELECT * FROM adm_item WHERE item_id=$item_id and branch_id=$branch_id";
    $itemRow=mysqli_fetch_assoc(mysqli_query($con,$itemQ));
    $img_src = (file_exists($itemRow['item_Image']) && $itemRow['item_Image']!="") ? $itemRow['item_Image'] : "img/demo-img.png";
    $img_display = ($img_src != "img/demo-img.png") ? 'block' : 'none';
?>
    <form id="checkout-form" class="smart-form" novalidate="novalidate" method="POST" enctype="multipart/form-data" onsubmit="return checkParameters();">	
    <fieldset>
    <input type="hidden" name="remove_image_flag" id="remove_image_flag" value="0" />
    <div class="row" style="margin-bottom: 5px;">
        <div class="col col-md-7">       
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-md-2"><label>Product Name:</label></div>
                <div class="col col-md-4"><input type="text" name="item_Name" id="item_Name" value="<?php echo $itemRow['item_Name'];?>" class="form-control"></div>
                <div class="col col-md-2"><label>Product Code:</label></div>
                <div class="col col-md-4"><input type="text" name="item_Code" value="<?php echo $itemRow['item_Code'];?>" class="form-control" ></div>
            </div>
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-md-2"><label>Purchase Price:</label></div>
                <div class="col col-md-4"><input type="number"  step="0.01" class="form-control" name="item_PurchasePrice" value="<?php echo $itemRow['item_PurchasePrice'];?>"></div>
                <div class="col col-md-2"><label>Sale Price:</label></div>
                <div class="col col-lg-4"><input type="number"   step="0.01" class="form-control" name="item_SalePrice" value="<?php echo $itemRow['item_SalePrice'];?>"></div>
            </div>
            <div class="row" style="margin-bottom: 5px; display:none;">
                <div class="col col-md-2"><label>Min Qty:</label></div>
                <div class="col col-md-4"><input type="number" class="form-control" name="item_MinQty" value="<?php echo $itemRow['item_MinQty'];?>"></div>
                <div class="col col-md-2"><label>Max Qty:</label></div>
                <div class="col col-lg-4"><input type="number" class="form-control" name="item_MaxQty" value="<?php echo $itemRow['item_MaxQty'];?>"></div>
            </div> 
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-md-2"><label>Notes:</label></div>
                <div class="col col-md-10"><textarea name="item_Remarks" class="form-control" style="max-width:613px; max-height:55px;"><?php echo $itemRow['item_Remarks'];?></textarea> </div>
            </div>	
        </div>
        <div class="col col-md-5">
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-md-2"><label>Status:</label></div>
                <div class="col col-md-6">
                    <select name="item_Status" class="form-control"> 
                        <option value="A" <?php if($itemRow['item_Status']=='A'){ echo  "selected='selected'"; } ?> >Active</option>
                        <option value="I" <?php if($itemRow['item_Status']=='I'){ echo  "selected='selected'"; } ?> >In-Active</option>
                    </select>
                </div>
            </div>
            <div class="row" style="margin-bottom: 5px;">
                <div class="col col-md-2"><label>Picture:</label></div>
                <div class="col col-md-6">
                    <input type="hidden" name="item_PicPathOld" value="<?=$itemRow['item_Image']?>"  />
                    <input type="file" name="item_PicPath" id="item_PicPath" onchange="readURL(this)" class="form-control" accept="image/jpeg, image/png, image/jpg">
                </div>
            </div>
            <div class="row" style="margin-bottom: 5px;" id="img_row_actual">
                <div class="col col-md-2">&nbsp;</div>
                <div class="col col-md-6">
                    <img src="<?php echo $img_src;?>" id="img" style="max-height:150px; max-width:250px; float:left; display:<?php echo ($img_src!="img/demo-img.png")?'block':'none';?>;" />
                    <?php if($img_src != "img/demo-img.png" && $itemRow['item_Image']!="") { ?>
                        <span class="img-remove-link" id="img_remove_link" onclick="removeImage();">Remove Attached Image</span>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    </fieldset>
    <footer>
        <input type="hidden" name="item_id" value="<?php echo $itemRow['item_id'];?>">
        <button type="submit" class="btn btn-primary" name="update">Save</button>
    </footer>
    </form>
<?php } ?>
                </div>
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

<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/dataTables.colVis.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/dataTables.tableTools.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatables/dataTables.bootstrap.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/plugin/datatable-responsive/datatables.responsive.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    // DataTables code (unchanged)
});
function checkParameters(){
    var item_Name = $.trim($("#item_Name").val());
    if (item_Name == '')
    {
        $.smallBox({
        title : "Error",
        content : "<i class='fa fa-clock-o'></i> <i>Please Fill Item Name.</i>",
        color : "#C46A69",
        iconSmall : "fa fa-times fa-2x fadeInRight animated",
        timeout : 4000
        });
        $("#item_Name").focus();
        return false;
    }
    checkItemImage();
}
function checkItemImage(){
    var item_PicPath=document.getElementById('item_PicPath').value;
    if(item_PicPath!=='')
    {
        var ext = item_PicPath.split('.').pop().toLowerCase();
        if(ext!=='jpg' && ext!=='jpeg' && ext!=='png')
        {
            alert('Please Choose Only jpg or png File for Product Picture');
            document.getElementById('item_PicPath').value= null;
            return false;
        }
    }
}
</script>