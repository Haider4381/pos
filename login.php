<?php
session_start();
include('connection.php');

/* TEMP: enable errors to see exact issue; remove on production */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*
  Secure login handling with graceful fallback:
  - Preferred query uses adm_currency (LEFT JOIN) for currency_symbol.
  - If adm_currency table missing or query fails, fallback query runs without it.
  - MD5 is used to match current system's stored hashes (backward-compatible).
  - No use of mysqli_stmt_get_result (works without mysqlnd).
*/

function get_login_row($con, $with_currency, $email, $pass, $passcode) {
    if ($with_currency) {
        $sql = "SELECT u.u_id, u.u_FullName, u.branch_id, u.branch_admin,
                       b.branch_Name, b.branch_Code, b.branch_ActiveToDate, b.branch_TimeZone,
                       COALESCE(c.currency_symbol, '') AS currency_symbol
                FROM u_user u
                INNER JOIN adm_branch b ON b.branch_id = u.branch_id
                LEFT JOIN adm_currency c ON c.currency_id = b.currency_id
                WHERE u.u_Email = ? AND u.u_Password = ? AND b.branch_passcode = ?";
    } else {
        $sql = "SELECT u.u_id, u.u_FullName, u.branch_id, u.branch_admin,
                       b.branch_Name, b.branch_Code, b.branch_ActiveToDate, b.branch_TimeZone
                FROM u_user u
                INNER JOIN adm_branch b ON b.branch_id = u.branch_id
                WHERE u.u_Email = ? AND u.u_Password = ? AND b.branch_passcode = ?";
    }

    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        error_log("Login prepare error: " . mysqli_error($con));
        return null;
    }

    mysqli_stmt_bind_param($stmt, "sss", $email, $pass, $passcode);

    if (!mysqli_stmt_execute($stmt)) {
        error_log("Login execute error: " . mysqli_error($con));
        mysqli_stmt_close($stmt);
        return null;
    }

    // Use bind_result / store_result (compatible without mysqlnd)
    if ($with_currency) {
        mysqli_stmt_bind_result(
            $stmt,
            $u_id, $u_FullName, $branch_id, $branch_admin,
            $branch_Name, $branch_Code, $branch_ActiveToDate, $branch_TimeZone,
            $currency_symbol
        );
    } else {
        mysqli_stmt_bind_result(
            $stmt,
            $u_id, $u_FullName, $branch_id, $branch_admin,
            $branch_Name, $branch_Code, $branch_ActiveToDate, $branch_TimeZone
        );
    }

    mysqli_stmt_store_result($stmt);
    $rows = mysqli_stmt_num_rows($stmt);

    $row = null;
    if ($rows === 1 && mysqli_stmt_fetch($stmt)) {
        $row = array(
            'u_id'                 => $u_id,
            'u_FullName'           => $u_FullName,
            'branch_id'            => $branch_id,
            'branch_admin'         => $branch_admin,
            'branch_Name'          => $branch_Name,
            'branch_Code'          => $branch_Code,
            'branch_ActiveToDate'  => $branch_ActiveToDate,
            'branch_TimeZone'      => $branch_TimeZone
        );
        if ($with_currency) {
            $row['currency_symbol'] = $currency_symbol;
        }
    }

    mysqli_stmt_free_result($stmt);
    mysqli_stmt_close($stmt);

    return $row;
}

if (isset($_POST['login'])) {
    $admin_Email     = isset($_POST['input_Email']) ? trim($_POST['input_Email']) : '';
    $input_pass      = isset($_POST['input_Password']) ? trim($_POST['input_Password']) : '';
    $admin_Password  = md5($input_pass); // current system uses MD5
    $branch_passcode = isset($_POST['input_Passcode']) ? trim($_POST['input_Passcode']) : '';

    if ($admin_Email === '' || $input_pass === '' || $branch_passcode === '') {
        $_SESSION['msg'] = '<div class="alert alert-danger">Please fill all fields.</div>';
    } else {
        $rowCheckLogin = get_login_row($con, true,  $admin_Email, $admin_Password, $branch_passcode);
        $currency_symbol = ($rowCheckLogin && isset($rowCheckLogin['currency_symbol'])) ? $rowCheckLogin['currency_symbol'] : '';

        if (!$rowCheckLogin) {
            // Fallback without currency table
            $rowCheckLogin = get_login_row($con, false, $admin_Email, $admin_Password, $branch_passcode);
            if ($rowCheckLogin) {
                $currency_symbol = 'Rs';
            }
        }

        if ($rowCheckLogin) {
            $branch_ActiveToDate = $rowCheckLogin['branch_ActiveToDate'];
            $current_date = date('Y-m-d');
            if ($branch_ActiveToDate >= $current_date) {
                $_SESSION['u_id']            = $rowCheckLogin['u_id'];
                $_SESSION['u_Email']         = $admin_Email;
                $_SESSION['u_Password']      = $admin_Password;
                $_SESSION['admin_id']        = $rowCheckLogin['u_id'];
                $_SESSION['admin_Email']     = $admin_Email;
                $_SESSION['admin_Password']  = $admin_Password;
                $_SESSION['admin_Name']      = $rowCheckLogin['u_FullName'];
                $_SESSION['rights_Edit']     = 1;
                $_SESSION['rights_Delete']   = 1;
                $_SESSION['branch_id']       = $rowCheckLogin['branch_id'];
                $_SESSION['branch_admin']    = $rowCheckLogin['branch_admin'];
                $_SESSION['branch_Name']     = $rowCheckLogin['branch_Name'];
                $_SESSION['branch_Code']     = $rowCheckLogin['branch_Code'];
                $_SESSION['currency_symbol'] = $currency_symbol ? $currency_symbol : 'Rs';
                $_SESSION['branch_TimeZone'] = $rowCheckLogin['branch_TimeZone'];

                echo "<script>setTimeout(function(){window.location.href='dashboard?rel=login'},0);</script>";
                exit;
            } else {
                $_SESSION['msg'] = '<div class="alert alert-danger">Login Rights Expired!</div>';
            }
        } else {
            $_SESSION['msg'] = '<div class="alert alert-danger">Invalid login</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="loading">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1, user-scalable=no">
    <meta name="description" content="Secure ERP POS Login">
    <meta name="keywords" content="POS, ERP, Login">
    <meta name="author" content="Your Company">
    <title>Login - POINT OF SALE Panel</title>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <!-- Font Awesome 5.15.4 (Stable, icons = 'fas fa-...') -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
      :root{
        --bg-primary:#0f172a;
        --bg-overlay-1:rgba(9,25,56,0.78);
        --bg-overlay-2:rgba(17,94,164,0.58);
        --card-bg:rgba(255,255,255,0.10);
        --card-border:rgba(255,255,255,0.25);
        --glass-blur:12px;
        --text-strong:#0f172a;
        --text-muted:#475569;
        --brand:#2563eb;
        --brand-2:#0ea5e9;
        --ok:#22c55e;
        --warn:#ef4444;
      }
      *{box-sizing:border-box}
      html,body{height:100%}
      body{
        margin:0;
        font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,'Helvetica Neue',Arial,'Noto Sans',sans-serif;
        color:#0b1220;
        overflow:auto;
      }

      /* Fullscreen ERP-themed background with professional online image */
      .login-backdrop{
        position:fixed; inset:0;
        background: var(--bg-primary);
        background-image:
          linear-gradient(120deg, var(--bg-overlay-1), var(--bg-overlay-2)),
          url('https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=1920&auto=format&fit=crop');
        background-size:cover;
        background-position:center;
        filter: saturate(110%);
        z-index:0;
      }
      .login-backdrop::after{
        content:"";
        position:absolute; inset:0;
        background:
          radial-gradient(800px 400px at 10% 10%, rgba(255,255,255,0.10), transparent 60%),
          radial-gradient(700px 350px at 90% 90%, rgba(255,255,255,0.08), transparent 60%);
        pointer-events:none;
      }

      /* Container */
      .login-container{
        position:relative; z-index:1;
        min-height:100%;
        display:grid; place-items:center;
        padding:28px;
      }

      /* Glass card */
      .login-card{
        width:100%;
        max-width:1120px;
        min-height:540px;
        display:grid;
        grid-template-columns: 1.1fr 1fr;
        border-radius:18px;
        background:var(--card-bg);
        border:1px solid var(--card-border);
        box-shadow: 0 28px 90px rgba(0,0,0,0.35);
        backdrop-filter: blur(var(--glass-blur));
        -webkit-backdrop-filter: blur(var(--glass-blur));
        overflow:hidden;
      }

      /* Left hero panel */
      .login-hero{
        position:relative;
        color:#e2e8f0;
        background: linear-gradient(180deg, rgba(21,94,117,0.35), rgba(8,47,73,0.35));
      }
      .login-hero::before{
        content:"";
        position:absolute; inset:0;
        background: url('https://images.unsplash.com/photo-1551281044-c2c5d63b8280?q=80&w=1920&auto=format&fit=crop') center/cover no-repeat;
        opacity:0.16;
        mix-blend-mode: lighten;
      }
      .hero-inner{
        position:relative; z-index:2;
        height:100%;
        padding:48px 42px;
        display:flex; flex-direction:column; justify-content:center;
      }
      .brand-badge{
        width:64px; height:64px; border-radius:14px;
        display:grid; place-items:center;
        background: rgba(255,255,255,0.12);
        border:1px solid rgba(255,255,255,0.25);
        margin-bottom:16px;
        font-size:26px;
      }
      .hero-title{font-weight:800;font-size:40px;margin:0 0 8px 0;letter-spacing:.3px;}
      .hero-subtitle{margin:0 0 14px 0; color:#cbd5e1}
      .hero-points{list-style:none; padding:0; margin:16px 0 0 0}
      .hero-points li{margin:8px 0; font-weight:500; color:#e2e8f0}
      .hero-points i{color:#22d3ee; margin-right:8px}

      /* Right form */
      .login-form-wrap{
        background: rgba(255,255,255,0.96);
        padding:36px 34px 28px 34px;
        display:flex; flex-direction:column; justify-content:center;
      }
      .form-head h2{margin:0; font-weight:800; color:var(--text-strong); letter-spacing:.2px}
      .form-head p{margin:6px 0 22px 0; color:var(--text-muted)}

      .alert{
        background:#fff; border-left:4px solid var(--warn);
        padding:10px 12px; font-size:13px; border-radius:10px; margin:10px 0 16px 0; color:#991b1b; border:1px solid #fecaca;
      }

      .form-group{margin-bottom:14px}
      .form-group label{display:block; font-size:13px; font-weight:600; margin-bottom:6px; color:var(--text-strong)}
      .input-with-icon{position:relative}
      .input-with-icon i.fa, .input-with-icon i.fas{
        position:absolute; top:50%; left:12px; transform:translateY(-50%); color:#64748b;
      }
      .input-with-icon input{
        width:100%; height:46px;
        padding:10px 44px 10px 38px;
        border:1px solid #cbd5e1; border-radius:10px; outline:none;
        transition: border-color .18s, box-shadow .18s, background .18s;
        background:#fff; font-size:14px;
      }
      .input-with-icon input:focus{border-color:#2563eb; box-shadow:0 0 0 4px rgba(37,99,235,0.14)}
      .toggle-pass{
        position:absolute; top:50%; right:8px; transform:translateY(-50%);
        height:30px; width:36px; border:none; background:transparent; cursor:pointer; color:#64748b;
      }
      .toggle-pass:hover{color:#0f172a}

      .form-actions{
        margin-top:12px; display:flex; align-items:center; justify-content:space-between;
        gap:10px; flex-wrap:wrap;
      }
      .btn-primary-erp{
        background: linear-gradient(135deg, var(--brand), var(--brand-2));
        color:#fff; border:0; padding:11px 18px; border-radius:10px;
        font-weight:800; letter-spacing:.2px; cursor:pointer;
        box-shadow: 0 10px 24px rgba(37,99,235,0.35);
        transition: transform .08s ease, box-shadow .2s ease;
      }
      .btn-primary-erp:hover{ transform: translateY(-1px); box-shadow:0 14px 28px rgba(37,99,235,0.45) }
      .link-muted{ color:#64748b; text-decoration:none; font-size:13px }
      .link-muted:hover{ color:#0f172a }

      .form-foot{ margin-top:16px; text-align:center; color:#64748b; font-size:12px }

      /* Responsive */
      @media (max-width: 991.98px){
        .login-card{ grid-template-columns:1fr; max-width:560px; min-height:unset }
        .login-hero{ display:none }
        .login-form-wrap{ padding:28px 22px }
      }
    </style>
  </head>
  <body>

    <!-- BACKDROP -->
    <div class="login-backdrop"></div>

    <!-- CONTENT -->
    <div class="login-container">
      <div class="login-card">
        <!-- LEFT HERO -->
        <div class="login-hero">
          <div class="hero-inner">
            <div class="brand-badge"><i class="fas fa-cubes"></i></div>
            <h1 class="hero-title">ERP POS Suite</h1>
            <p class="hero-subtitle">Fast Billing • Real-time Inventory • Smart Analytics</p>
            <ul class="hero-points">
              <li><i class="fas fa-check-circle"></i> Multi-branch support</li>
              <li><i class="fas fa-check-circle"></i> Ledger & Vouchers</li>
              <li><i class="fas fa-check-circle"></i> Purchase & Sales</li>
            </ul>
          </div>
        </div>

        <!-- RIGHT FORM -->
        <div class="login-form-wrap">
          <div class="form-head">
            <h2>Welcome Back</h2>
            <p>Please login to your account</p>
          </div>

          <?php if(!empty($_SESSION['msg'])){ echo $_SESSION['msg']; unset($_SESSION['msg']);} ?>

          <form method="post" action="" class="login-form" autocomplete="off" onsubmit="return validateLogin();">
            <div class="form-group">
              <label>Username</label>
              <div class="input-with-icon">
                <i class="fas fa-user"></i>
                <input type="text" name="input_Email" id="input_Email" placeholder="Enter username/email" required>
              </div>
            </div>

            <div class="form-group">
              <label>Password</label>
              <div class="input-with-icon">
                <i class="fas fa-lock"></i>
                <input type="password" name="input_Password" id="input_Password" placeholder="Enter password" required>
                <button type="button" class="toggle-pass" tabindex="-1" aria-label="Show password" onclick="togglePass('input_Password', this)"><i class="fas fa-eye"></i></button>
              </div>
            </div>

            <div class="form-group">
              <label>Passcode</label>
              <div class="input-with-icon">
                <i class="fas fa-key"></i>
                <input type="password" name="input_Passcode" id="input_Passcode" placeholder="Branch passcode" required>
                <button type="button" class="toggle-pass" tabindex="-1" aria-label="Show passcode" onclick="togglePass('input_Passcode', this)"><i class="fas fa-eye"></i></button>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" name="login" class="btn-primary-erp"><i class="fas fa-sign-in-alt"></i> Login</button>
              <a href="javascript:void(0)" class="link-muted">Forgot Password?</a>
            </div>
          </form>

          <div class="form-foot">
            <small>© <?php echo date('Y'); ?> Your Company. All rights reserved.</small>
          </div>
        </div>
      </div>
    </div>

    <script>
      function togglePass(id, btn){
        var inp = document.getElementById(id);
        if(!inp) return;
        var isPass = inp.type === 'password';
        inp.type = isPass ? 'text' : 'password';
        var icon = btn.querySelector('i');
        if(icon){
          icon.classList.toggle('fa-eye');
          icon.classList.toggle('fa-eye-slash');
        }
      }
      function validateLogin(){
        var email = document.getElementById('input_Email').value.trim();
        var pass  = document.getElementById('input_Password').value.trim();
        var code  = document.getElementById('input_Passcode').value.trim();
        if(!email || !pass || !code){
          alert('Please fill all fields.');
          return false;
        }
        return true;
      }
    </script>
  </body>
</html>