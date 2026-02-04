<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');

$page_title = "Change Password";

// CSRF token init
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$u_id      = isset($_SESSION['u_id']) ? intval($_SESSION['u_id']) : 0;
$branch_id = isset($_SESSION['branch_id']) ? intval($_SESSION['branch_id']) : 0;

if ($u_id <= 0) {
    // Safety fallback
    header("Location: login.php");
    exit;
}

$msg = "";
if (isset($_SESSION['flash_msg'])) {
    $msg = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = isset($_POST['_csrf']) ? $_POST['_csrf'] : '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
        $msg = "<div class='alert alert-danger'>Invalid request. Please try again.</div>";
    } else {
        $current_password = trim($_POST['current_password'] ?? '');
        $new_password     = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        $errors = [];

        if ($current_password === '' || $new_password === '' || $confirm_password === '') {
            $errors[] = "Please fill all required fields.";
        }
        if (strlen($new_password) < 8) {
            $errors[] = "New password must be at least 8 characters long.";
        }
        if ($new_password !== $confirm_password) {
            $errors[] = "New password and confirm password do not match.";
        }

        // Fetch stored hash
        $q = "SELECT u_Password FROM u_user WHERE u_id = ".intval($u_id)." LIMIT 1";
        $r = mysqli_query($con, $q);
        if (!$r || mysqli_num_rows($r) === 0) {
            $errors[] = "User not found.";
        } else {
            $row = mysqli_fetch_assoc($r);
            $stored = $row['u_Password'];

            // Current system appears to use MD5
            if (strtolower($stored) !== md5($current_password)) {
                $errors[] = "Current password is incorrect.";
            }
        }

        if (empty($errors)) {
            // For compatibility: store MD5 (do not break current login)
            $newHash = md5($new_password);

            $uq = "UPDATE u_user
                   SET u_Password = '".$newHash."', u_Password2 = NULL
                   WHERE u_id = ".intval($u_id)." LIMIT 1";
            if (mysqli_query($con, $uq)) {
                // Optional: regenerate session id for safety
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_regenerate_id(true);
                }
                $_SESSION['flash_msg'] = "<div class='alert alert-success'>Password changed successfully.</div>";
                header("Location: change_password.php");
                exit;
            } else {
                $msg = "<div class='alert alert-danger'>Failed to update password: ".htmlspecialchars(mysqli_error($con))."</div>";
            }
        } else {
            $msg = "<div class='alert alert-danger'>".implode("<br>", $errors)."</div>";
        }
    }
}

require_once ("inc/init.php");
require_once ("inc/config.ui.php");
include ("inc/header.php");
include ("inc/nav.php");
?>
<div id="main" role="main">
<?php $breadcrumbs["Change Password"] = ""; include("inc/ribbon.php"); ?>
<style>
body{background:#f8fafc;}
.form-control{border-radius:5px!important;font-size:13px;}
.custom-card{background:#fff;border-radius:8px;box-shadow:0 2px 10px #e4e4e4;padding:24px;margin-top:18px;max-width:700px;}
.custom-title{font-size:20px;font-weight:600;color:#444;margin-bottom:14px;}
</style>

<div id="content">
<section id="widget-grid">
<div class="row">
<article class="col-sm-12 col-md-10 col-lg-8 col-lg-offset-2">
  <div class="custom-card">
      <div class="custom-title"><i class="fa fa-key"></i> Change Password</div>
      <?php if($msg) echo $msg; ?>
      <form method="post" action="change_password.php" autocomplete="off" onsubmit="return validateCP();">
        <input type="hidden" name="_csrf" value="<?php echo htmlspecialchars($_SESSION['csrf_token']);?>">
        <div class="mb-3">
            <label class="form-label">Current Password <span style="color:red">*</span></label>
            <input type="password" name="current_password" id="current_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">New Password <span style="color:red">*</span></label>
            <input type="password" name="new_password" id="new_password" class="form-control" minlength="8" required>
            <small class="text-muted">Minimum 8 characters recommended.</small>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirm New Password <span style="color:red">*</span></label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" minlength="8" required>
        </div>
        <div class="mt-2">
            <button type="submit" class="btn btn-success">Update Password</button>
            <a href="dashboard" class="btn btn-default">Cancel</a>
        </div>
      </form>
  </div>
</article>
</div>
</section>
</div>
</div>

<?php include ("inc/footer.php"); include ("inc/scripts.php"); ?>
<script>
function validateCP(){
    var cur = document.getElementById('current_password').value.trim();
    var np  = document.getElementById('new_password').value.trim();
    var cp  = document.getElementById('confirm_password').value.trim();
    if(!cur || !np || !cp){ alert('Please fill all required fields'); return false; }
    if(np.length < 8){ alert('New password must be at least 8 characters'); return false; }
    if(np !== cp){ alert('New password and confirm password do not match'); return false; }
    if(cur === np){ if(!confirm('New password is same as current. Continue?')) return false; }
    return true;
}
</script>