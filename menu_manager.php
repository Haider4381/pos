<?php
include('sessionCheck.php');
include 'connection.php';
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Menu Manager";
include ("inc/header.php");
include ("inc/nav.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$delete_id = isset($_GET['delete_id']) ? intval($_GET['delete_id']) : 0;
$msg = "";

// Role IDs for assigning menu rights (default: admin role id 1)
$default_roles = [1]; // You can enhance this to fetch all active roles if needed

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add or Edit Menu
    $menu_name = $_POST['menu_name'];
    $menu_icon = $_POST['menu_icon'];
    $menu_level = intval($_POST['menu_level']);
    $menu_parent = intval($_POST['menu_parent']);
    $menu_position = intval($_POST['menu_position']);
    $menu_submenu = intval($_POST['menu_submenu']);
    $menu_url = $_POST['menu_url'];
    $menu_active = intval($_POST['menu_active']);
    $menu_ARname = $_POST['menu_ARname'];
    $form_code = $_POST['form_code'];

    if (isset($_POST['edit_id']) && $_POST['edit_id']) {
        // Edit existing
        $id = intval($_POST['edit_id']);
        $q = "UPDATE sys_menu SET
            menu_name='$menu_name',
            menu_icon='$menu_icon',
            menu_level='$menu_level',
            menu_parent='$menu_parent',
            menu_position='$menu_position',
            menu_submenu='$menu_submenu',
            menu_url='$menu_url',
            menu_active='$menu_active',
            menu_ARname='$menu_ARname',
            form_code='$form_code'
            WHERE menu_id=$id";
        if (mysqli_query($con, $q)) {
            $msg = "<div class='alert alert-success'>Menu updated successfully.</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Error updating menu: " . mysqli_error($con) . "</div>";
        }
        // Ensure rights exist in sys_roled (for default roles)
        foreach($default_roles as $role_id) {
            $check = mysqli_query($con, "SELECT COUNT(*) as cnt FROM sys_roled WHERE menu_id=$id AND role_id=$role_id");
            $row = mysqli_fetch_assoc($check);
            if($row['cnt'] == 0) {
                mysqli_query($con, "INSERT INTO sys_roled (role_id, menu_id, roled_edit, roled_delete) VALUES ($role_id, $id, 1, 1)");
            }
        }
    } else {
        // Add new
        $q = "INSERT INTO sys_menu (
            menu_name, menu_icon, menu_level, menu_parent, menu_position, menu_submenu,
            menu_url, menu_active, menu_ARname, form_code
        ) VALUES (
            '$menu_name', '$menu_icon', '$menu_level', '$menu_parent', '$menu_position',
            '$menu_submenu', '$menu_url', '$menu_active', '$menu_ARname', '$form_code'
        )";
        if (mysqli_query($con, $q)) {
            $menu_id = mysqli_insert_id($con);
            $msg = "<div class='alert alert-success'>Menu added successfully.</div>";
            // Insert rights in sys_roled for default roles
            foreach($default_roles as $role_id) {
                mysqli_query($con, "INSERT INTO sys_roled (role_id, menu_id, roled_edit, roled_delete) VALUES ($role_id, $menu_id, 1, 1)");
            }
        } else {
            $msg = "<div class='alert alert-danger'>Error adding menu: " . mysqli_error($con) . "</div>";
        }
    }
}

if ($delete_id) {
    // Delete menu
    $q = "DELETE FROM sys_menu WHERE menu_id = $delete_id";
    if (mysqli_query($con, $q)) {
        // Also delete rights from sys_roled
        mysqli_query($con, "DELETE FROM sys_roled WHERE menu_id = $delete_id");
        $msg = "<div class='alert alert-success'>Menu deleted successfully.</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Error deleting menu: " . mysqli_error($con) . "</div>";
    }
}

// For Edit, fetch menu
$edit_row = [];
if ($action == 'edit' && $edit_id) {
    $res = mysqli_query($con, "SELECT * FROM sys_menu WHERE menu_id = $edit_id");
    $edit_row = mysqli_fetch_assoc($res);
}

// Fetch all menus for listing and parent select
$menuList = [];
$menuRes = mysqli_query($con, "SELECT menu_id, menu_name, menu_level FROM sys_menu ORDER BY menu_level, menu_position");
while ($row = mysqli_fetch_assoc($menuRes)) {
    $menuList[] = $row;
}
?>

<style>
    .form-control{
            margin-bottom: 15px;
    }
</style>

<!-- MAIN PANEL -->
<div id="main" role="main">
<div id="content">
    <section id="widget-grid">
        <div class="row">
            <article class="col-sm-12 col-md-12 col-lg-12">
                <div class="jarviswidget jarviswidget-color-blue" id="wid-id-1" data-widget-editbutton="false">
                    <header>
                        <span class="widget-icon"><i class="fa fa-cogs"></i></span>
                        <h2>Menu Manager</h2>
                    </header>
                    <div>
                        <div class="widget-body">
                            <?php if($msg) echo $msg; ?>
                            <form method="post" action="menu_manager.php" class="smart-form">
                                <input type="hidden" name="edit_id" value="<?php echo isset($edit_row['menu_id']) ? $edit_row['menu_id']:'';?>">
                                <fieldset>
                                    <div class="row">
                                        <div class="col col-lg-3">Menu Name *</div>
                                        <div class="col col-lg-6">
                                            <input type="text" name="menu_name" class="form-control" required value="<?php echo isset($edit_row['menu_name']) ? $edit_row['menu_name']:'';?>">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col col-lg-3">Menu Icon</div>
                                        <div class="col col-lg-6">
                                            <input type="text" name="menu_icon" class="form-control" value="<?php echo isset($edit_row['menu_icon']) ? $edit_row['menu_icon']:'';?>">
                                            <small>FontAwesome class e.g. fa fa-list</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col col-lg-3">Menu Level</div>
                                        <div class="col col-lg-6">
                                            <select name="menu_level" class="form-control">
                                                <option value="1" <?php if(isset($edit_row['menu_level']) && $edit_row['menu_level'] == 1) echo 'selected';?>>1 (Main)</option>
                                                <option value="2" <?php if(isset($edit_row['menu_level']) && $edit_row['menu_level'] == 2) echo 'selected';?>>2 (Sub)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col col-lg-3">Parent Menu</div>
                                        <div class="col col-lg-6">
                                            <select name="menu_parent" class="form-control">
                                                <option value="0">-- None --</option>
                                                <?php foreach($menuList as $m) { ?>
                                                    <option value="<?php echo $m['menu_id'];?>" <?php if(isset($edit_row['menu_parent']) && $edit_row['menu_parent'] == $m['menu_id']) echo 'selected';?>><?php echo $m['menu_name'];?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col col-lg-3">Menu Position</div>
                                        <div class="col col-lg-6">
                                            <input type="number" name="menu_position" class="form-control" value="<?php echo isset($edit_row['menu_position']) ? $edit_row['menu_position']:'0';?>">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col col-lg-3">Submenu?</div>
                                        <div class="col col-lg-6">
                                            <select name="menu_submenu" class="form-control">
                                                <option value="0" <?php if(isset($edit_row['menu_submenu']) && $edit_row['menu_submenu'] == 0) echo 'selected';?>>No</option>
                                                <option value="1" <?php if(isset($edit_row['menu_submenu']) && $edit_row['menu_submenu'] == 1) echo 'selected';?>>Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col col-lg-3">Menu URL</div>
                                        <div class="col col-lg-6">
                                            <input type="text" name="menu_url" class="form-control" value="<?php echo isset($edit_row['menu_url']) ? $edit_row['menu_url']:'';?>">
                                            <small>Page file name e.g. sale_add</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col col-lg-3">Active?</div>
                                        <div class="col col-lg-6">
                                            <select name="menu_active" class="form-control">
                                                <option value="1" <?php if(isset($edit_row['menu_active']) && $edit_row['menu_active'] == 1) echo 'selected';?>>Yes</option>
                                                <option value="0" <?php if(isset($edit_row['menu_active']) && $edit_row['menu_active'] == 0) echo 'selected';?>>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col col-lg-3">Arabic Name</div>
                                        <div class="col col-lg-6">
                                            <input type="text" name="menu_ARname" class="form-control" value="<?php echo isset($edit_row['menu_ARname']) ? $edit_row['menu_ARname']:'';?>">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col col-lg-3">Form Code</div>
                                        <div class="col col-lg-6">
                                            <input type="text" name="form_code" class="form-control" value="<?php echo isset($edit_row['form_code']) ? $edit_row['form_code']:'';?>">
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top:15px;">
                                        <div class="col col-lg-9">
                                            <button type="submit" class="btn btn-success" style="padding: 8px;"><?php echo $action == 'edit' ? 'Update Menu':'Add Menu'; ?></button>
                                            <?php if($action == 'edit') { ?>
                                                <a href="menu_manager.php" class="btn btn-default">Cancel</a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                            <hr>
                            <h3>Menu List</h3>
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Icon</th>
                                    <th>Level</th>
                                    <th>Parent</th>
                                    <th>Position</th>
                                    <th>Submenu</th>
                                    <th>URL</th>
                                    <th>Active</th>
                                    <th>Actions</th>
                                </tr>
                                <?php
                                $res = mysqli_query($con, "SELECT * FROM sys_menu ORDER BY menu_level, menu_position");
                                while ($row = mysqli_fetch_assoc($res)) {
                                    $parentName = '';
                                    if ($row['menu_parent']) {
                                        foreach ($menuList as $m) {
                                            if ($m['menu_id'] == $row['menu_parent']) {
                                                $parentName = $m['menu_name'];
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo $row['menu_id']; ?></td>
                                        <td><?php echo $row['menu_name']; ?></td>
                                        <td><i class="<?php echo $row['menu_icon']; ?>"></i> <?php echo $row['menu_icon']; ?></td>
                                        <td><?php echo $row['menu_level']; ?></td>
                                        <td><?php echo $parentName; ?></td>
                                        <td><?php echo $row['menu_position']; ?></td>
                                        <td><?php echo $row['menu_submenu']?'Yes':'No'; ?></td>
                                        <td><?php echo $row['menu_url']; ?></td>
                                        <td><?php echo $row['menu_active']?'Active':'Inactive'; ?></td>
                                        <td>
                                            <a href="menu_manager.php?action=edit&edit_id=<?php echo $row['menu_id']; ?>" class="btn btn-xs btn-warning">Edit</a>
                                            <a href="menu_manager.php?delete_id=<?php echo $row['menu_id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Delete this menu?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </section>
</div>
</div>
<?php include ("inc/footer.php"); ?>
<?php include ("inc/scripts.php"); ?>