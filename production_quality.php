<?php
include('sessionCheck.php');
include 'connection.php';
include('functions.php');

$msg = "";
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;

// Handle add or update
if(isset($_POST['submit'])){
    $quality_name = trim($_POST['quality_name']);
    if($quality_name == ''){
        $msg = "<div class='alert alert-danger'>Quality name required.</div>";
    } else {
        // Check duplicate
        $check = mysqli_query($con, "SELECT id FROM production_quality WHERE quality_name='".mysqli_real_escape_string($con, $quality_name)."' AND status=1 ".($edit_id ? "AND id!=$edit_id" : ""));
        if(mysqli_num_rows($check)>0){
            $msg = "<div class='alert alert-danger'>Quality already exists!</div>";
        } else {
            if($edit_id){
                $q = mysqli_query($con, "UPDATE production_quality SET quality_name='".mysqli_real_escape_string($con, $quality_name)."' WHERE id=$edit_id");
                $msg = $q ? "<div class='alert alert-success'>Quality updated successfully.</div>" : "<div class='alert alert-danger'>Update failed!</div>";
            } else {
                $q = mysqli_query($con, "INSERT INTO production_quality (quality_name, status) VALUES ('".mysqli_real_escape_string($con, $quality_name)."',1)");
                $msg = $q ? "<div class='alert alert-success'>Quality added successfully.</div>" : "<div class='alert alert-danger'>Insert failed!</div>";
            }
        }
    }
}

// Handle edit
$row = [];
if($edit_id){
    $r = mysqli_query($con, "SELECT * FROM production_quality WHERE id=$edit_id");
    $row = mysqli_fetch_assoc($r);
}

// Handle delete
if(isset($_GET['delete_id'])){
    $delid = intval($_GET['delete_id']);
    mysqli_query($con, "UPDATE production_quality SET status=0 WHERE id=$delid");
    $msg = "<div class='alert alert-success'>Quality deleted.</div>";
}

require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Grey Fabric Quality";
include ("inc/header.php");
include ("inc/nav.php");
?>
<div id="main" role="main">
    <style>
        body { background: #f4f7fb; }
        .main-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 24px 0 #e1e8ee;
            margin: 30px auto 30px auto;
            max-width: 500px;
            padding: 38px 42px 32px 42px;
            border: 1px solid #e7ecf3;
        }
        .main-header {
            margin-bottom: 28px;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 1px solid #e7ecf3;
        }
        .main-title {
            font-size: 2.1rem;
            font-weight: 800;
            color: #253053;
            letter-spacing: 1.2px;
        }
        .form-label {
            font-weight: 700;
            color: #1d2b48;
            margin-bottom: 6px;
            letter-spacing: 0.3px;
        }
        .form-control, select.form-control {
            border-radius: 6px;
            border: 1.3px solid #cfd7e6;
            font-size: 16px;
            font-weight: 500;
            background: #f8fafc;
            transition: border-color .2s;
            margin-bottom: 2px;
        }
        .form-control:focus, select.form-control:focus {
            border-color: #5c9ded;
            box-shadow: 0 0 0 1px #5c9ded30;
            background: #fff;
        }
        .main-footer {
            text-align: center;
            margin-top: 18px;
        }
        .btn {
            border-radius: 5px !important;
            font-weight: 700;
            font-size: 1.08rem;
            letter-spacing: 0.6px;
            padding: 8px 30px !important;
        }
        .btn-success {
            background: #31b857 !important;
            border: none !important;
            color: #fff !important;
            box-shadow: 0 2px 10px #0fad3c22;
        }
        .btn-default {
            background: #f9fafb !important;
            border: 1.2px solid #d2dbe9 !important;
            color: #5c6b86 !important;
        }
        .alert {
            margin-bottom: 18px;
            font-size: 1.02rem;
        }
        .table-section {
            margin: 45px auto 20px auto;
            max-width: 650px;
        }
        .custom-table {
            border-radius: 7px;
            overflow: hidden;
            box-shadow: 0 2px 16px #e9ecef;
            background: #fff;
        }
        .custom-table thead tr th {
            background: #eaeef4;
            font-size: 1rem;
            font-weight: 700;
            color: #2f415c;
            border-bottom: 2px solid #cfd8e8;
            padding: 11px 6px;
        }
        .custom-table tbody tr td {
            font-size: 0.97rem;
            color: #374158;
            padding: 10px 5px;
        }
        .custom-table tbody tr:nth-child(odd) {
            background: #f9fafb;
        }
        .table-actions .btn {
            font-size: 0.9rem !important;
            padding: 4px 18px !important;
            margin: 2px 0;
            border-radius: 4px !important;
        }
        @media (max-width: 600px) {
            .main-card, .table-section { padding: 10px 2vw 18px 2vw; }
        }
    </style>
    <div class="main-card">
        <div class="main-header">
            <span class="main-title"><i class="fa fa-tag"></i> ADD GREY FABRIC QUALITY</span>
        </div>
        <?php if($msg) echo $msg; ?>
        <form method="post" autocomplete="off">
            <div>
                <label class="form-label">Quality Name <span style="color:red">*</span></label>
                <input type="text" name="quality_name" class="form-control" value="<?php echo isset($row['quality_name']) ? htmlspecialchars($row['quality_name']) : ''; ?>" required>
            </div>
            <div class="main-footer">
                <button type="submit" name="submit" class="btn btn-success"><?php echo $edit_id ? 'Update' : 'Add'; ?></button>
                <?php if($edit_id){ ?>
                    <a href="production_quality.php" class="btn btn-default" style="margin-left:17px;">Cancel</a>
                <?php } ?>
            </div>
        </form>
    </div>
    <div class="main-card table-section">
        <h3 style="margin-bottom:20px; color:#253053; font-weight:700; text-align:center"><i class="fa fa-list"></i> Quality List</h3>
        <div class="table-responsive">
        <table class="table table-bordered custom-table">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Quality Name</th>
                    <th style="width:110px;">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $q = mysqli_query($con, "SELECT * FROM production_quality WHERE status=1 ORDER BY quality_name");
            $i=1;
            while($r = mysqli_fetch_assoc($q)){
            ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($r['quality_name']); ?></td>
                    <td class="table-actions">
                        <a href="production_quality.php?edit_id=<?php echo $r['id']; ?>" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                        <a href="production_quality.php?delete_id=<?php echo $r['id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Delete?')"><i class="fa fa-trash"></i> Delete</a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?php include ("inc/footer.php"); ?>
<?php include ("inc/scripts.php"); ?>