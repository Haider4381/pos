<?php
include('connection.php');
echo date('d-m-Y H:i:s');
echo '<br>';
echo $current_datetime_sql;
echo '<br>';
echo date('d-m-Y h:i:A', strtotime($current_datetime_sql));

?>