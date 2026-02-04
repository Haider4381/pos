<?php
include('sessionCheck.php');
include('connection.php');
include('functions.php');
require_once ("inc/init.php");
require_once ("inc/config.ui.php");
$page_title = "Product Sets";
include ("inc/header.php");
include('lib/lib_quotation1.php');
include ("inc/nav.php");

// Handle Delete
if(isset($_POST['delete_set'])) {
    $set_id = (int)$_POST['delete_set_id'];
    mysqli_query($con, "DELETE FROM adm_itemset_detail WHERE set_id = $set_id");
    mysqli_query($con, "DELETE FROM adm_itemset WHERE set_id = $set_id");
    $msg = "Set deleted successfully!";
}

// Handle Add/Edit
if(isset($_POST['submit'])){
    $set_name = mysqli_real_escape_string($con, $_POST['set_name']);
    $set_code = mysqli_real_escape_string($con, $_POST['set_code']);
    $item_ids = $_POST['item_id'];
    $quantities = $_POST['quantity'];
    
    if(isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
        $set_id = (int)$_POST['edit_id'];
        mysqli_query($con, "UPDATE adm_itemset SET set_name='$set_name', set_code='$set_code' WHERE set_id=$set_id");
        mysqli_query($con, "DELETE FROM adm_itemset_detail WHERE set_id=$set_id");
        
        foreach($item_ids as $index => $item_id){
            $qty = (int)$quantities[$index];
            mysqli_query($con, "INSERT INTO adm_itemset_detail (set_id, item_id, quantity) VALUES ($set_id, $item_id, $qty)");
        }
        $msg = "Set updated successfully!";
    } else {
        if(mysqli_query($con, "INSERT INTO adm_itemset (set_name, set_code) VALUES ('$set_name', '$set_code')")){
            $set_id = mysqli_insert_id($con);
            foreach($item_ids as $index => $item_id){
                $qty = (int)$quantities[$index];
                mysqli_query($con, "INSERT INTO adm_itemset_detail (set_id, item_id, quantity) VALUES ($set_id, $item_id, $qty)");
            }
            $msg = "Set created successfully!";
        }
    }
}

// Get set details for editing
if(isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_query = mysqli_query($con, "SELECT * FROM adm_itemset WHERE set_id = $edit_id");
    $edit_data = mysqli_fetch_assoc($edit_query);
    
    $items_query = mysqli_query($con, "SELECT * FROM adm_itemset_detail WHERE set_id = $edit_id");
    $edit_items = mysqli_fetch_all($items_query, MYSQLI_ASSOC);
}
?>
<br/>
<br/>
<br/>

<!DOCTYPE html>
<html>
<head>
    <title>Product Sets</title>
    <style>
        .form-container { margin-bottom: 20px; }
        .item-row { margin-bottom: 10px; }
        .btn-remove { color: red; cursor: pointer; }
        .table { margin-top: 20px; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        /* Professional styling */
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8f9fa; }
        .jarviswidget header h2 { font-size: 1.5rem; }
        .form-control, .btn { border-radius: 0.25rem; }
        .form-group label { font-weight: 600; }
        /* Highlight Add Items section */
        #items-section {
            background: #e8f4fd;
            border-radius: 8px;
            padding: 18px 15px 8px 15px;
            box-shadow: 0 2px 8px rgba(34, 84, 154, 0.04);
            border: 1px solid #b6d8f6;
            margin-bottom: 18px;
        }
        #items .item-row { align-items: center; }
        .item-row select, .item-row input {
            border: 1px solid #d1d5db;
            margin-bottom: 0;
        }
        .btn-info { background: #2566a0; border: none; }
        .btn-info:hover { background: #174a75; }
        .btn-primary { background: #248a3d; border: none; }
        .btn-primary:hover { background: #17662b; }
        .btn-secondary { background: #b3b3b3; }
        .btn-danger { background: #dc3545; border: none; }
        .btn-danger:hover { background: #a71d2a; }
        .btn-sm { padding: 0.25rem 0.75rem; font-size: 0.95rem; }
        .table thead { background: #2566a0; color: #fff; }
        .table-bordered th, .table-bordered td { vertical-align: middle; }
        @media (max-width: 600px) {
            .row { flex-direction: column; }
            .col-md-6, .col-md-5, .col-md-4, .col-md-1 { width: 100%; margin-bottom: 8px; }
        }
    </style>
</head>
<body>
    <div id="content">
        <section id="widget-grid" class="">
            <div class="row">
                <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="jarviswidget jarviswidget-color-purity" id="wid-id-1" data-widget-editbutton="false">
                        <header>	
                            <span class="small_icon"><i class="fa fa-circle-o-notch"></i></span>	
                            <h2>Product</h2>
                        </header>
                        <?php if(isset($msg)): ?>
                            <div class="message success"><?php echo $msg; ?></div>
                        <?php endif; ?>

                        <!-- Form Section -->
                        <div class="form-container">
                            <h3 style="margin-bottom:12px;"><?php echo isset($edit_data) ? 'Edit Set' : 'Create New Set'; ?></h3>
                            <form method="post" action="">
                                <?php if(isset($edit_data)): ?>
                                    <input type="hidden" name="edit_id" value="<?php echo $edit_data['set_id']; ?>">
                                <?php endif; ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Set Name:</label>
                                            <input type="text" class="form-control" name="set_name" 
                                                value="<?php echo isset($edit_data) ? htmlspecialchars($edit_data['set_name']) : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Set Code:</label>
                                            <input type="text" class="form-control" name="set_code" 
                                                value="<?php echo isset($edit_data) ? htmlspecialchars($edit_data['set_code']) : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                                <!-- Add Items Section (highlighted) -->
                                <div id="items-section">
                                    <label style="font-size: 1.08rem; font-weight:600; color:#2566a0;">Add Items</label>
                                    <div id="items">
                                        <?php if(isset($edit_items) && count($edit_items) > 0): ?>
                                            <?php foreach($edit_items as $item): ?>
                                                <div class="item-row row">
                                                    <div class="col-md-6">
                                                        <select name="item_id[]" class="form-control" required>
                                                            <?php
                                                            $q = mysqli_query($con, "SELECT item_id, item_Name FROM adm_item WHERE item_Status='A'");
                                                            while($row = mysqli_fetch_assoc($q)){
                                                                $selected = ($row['item_id'] == $item['item_id']) ? 'selected' : '';
                                                                echo "<option value='{$row['item_id']}' $selected>{$row['item_Name']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="number" name="quantity[]" class="form-control" min="1" 
                                                            value="<?php echo htmlspecialchars($item['quantity']); ?>" required>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <span class="btn-remove">&times;</span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="item-row row">
                                                <div class="col-md-6">
                                                    <select name="item_id[]" class="form-control" required>
                                                        <?php
                                                        $q = mysqli_query($con, "SELECT item_id, item_Name FROM adm_item WHERE item_Status='A'");
                                                        while($row = mysqli_fetch_assoc($q)){
                                                            echo "<option value='{$row['item_id']}'>{$row['item_Name']}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="number" name="quantity[]" class="form-control" min="1" value="1" required>
                                                </div>
                                                <div class="col-md-1">
                                                    <span class="btn-remove">&times;</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-2 mb-3" style="margin-top:12px;">
                                        <button type="button" class="btn btn-info btn-sm" onclick="addItem()">Add Item</button>
                                    </div>
                                </div>
                                <div class="mt-2 mb-3">
                                    <button type="submit" name="submit" class="btn btn-primary btn-sm">Save Set</button>
                                    <?php if(isset($edit_data)): ?>
                                        <a href="set.php" class="btn btn-secondary btn-sm">Cancel</a>
                                    <?php endif; ?>
                                </div>
                                <br/>
                            </form>
                        </div>

                        <!-- Table Section -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Set Name</th>
                                        <th>Set Code</th>
                                        <th>Items</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sets_query = mysqli_query($con, "SELECT s.*, GROUP_CONCAT(CONCAT(i.item_Name, ' (', sd.quantity, ')') SEPARATOR ', ') as items 
                                                                    FROM adm_itemset s 
                                                                    LEFT JOIN adm_itemset_detail sd ON s.set_id = sd.set_id 
                                                                    LEFT JOIN adm_item i ON sd.item_id = i.item_id 
                                                                    GROUP BY s.set_id");
                                    while($row = mysqli_fetch_assoc($sets_query)):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['set_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['set_code']); ?></td>
                                        <td><?php echo htmlspecialchars($row['items']); ?></td>
                                        <td>
                                            <a href="?edit=<?php echo $row['set_id']; ?>" class="btn btn-info btn-sm">Edit</a>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="delete_set_id" value="<?php echo $row['set_id']; ?>">
                                                <button type="submit" name="delete_set" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('Are you sure you want to delete this set?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div><!-- jarviswidget -->
                </article>
            </div>
        </section>
    </div>

    <script>
    function addItem() {
        var template = document.querySelector('.item-row').cloneNode(true);
        template.querySelector('input[name="quantity[]"]').value = "1";
        document.getElementById('items').appendChild(template);
    }

    document.addEventListener('click', function(e) {
        if(e.target.classList.contains('btn-remove')) {
            if(document.querySelectorAll('.item-row').length > 1) {
                e.target.closest('.item-row').remove();
            }
        }
    });
    </script>
</body>
</html>

<?php include ("inc/footer.php"); ?>