<?php
include('connection.php');
$item_id = intval($_GET['item_id'] ?? 0);
$result = mysqli_fetch_assoc(mysqli_query($con, "SELECT item_Image FROM adm_item WHERE item_id = $item_id LIMIT 1"));
$img = ($result && !empty($result['item_Image']) && file_exists($result['item_Image'])) ? $result['item_Image'] : "img/demo-img.png";
echo json_encode(['img'=>$img]);
?>