<?php

// Add ZATCA seller fields in both add and edit forms
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $zatca_seller_vat = $_POST['zatca_seller_vat'];
    $zatca_seller_legal_name = $_POST['zatca_seller_legal_name'];
    $zatca_building_no = $_POST['zatca_building_no'];
    $zatca_street = $_POST['zatca_street'];
    $zatca_district = $_POST['zatca_district'];
    $zatca_city = $_POST['zatca_city'];
    $zatca_postal_code = $_POST['zatca_postal_code'];
    $zatca_country_code = $_POST['zatca_country_code'];

    // Handle INSERT into adm_branch
    $query = "INSERT INTO adm_branch (zatca_seller_vat, zatca_seller_legal_name, zatca_building_no, zatca_street, zatca_district, zatca_city, zatca_postal_code, zatca_country_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?);";
}

// Handle update for both fields
if (isset($_POST['update'])) {
    // Prepare update query
    $query = "UPDATE adm_branch SET zatca_seller_vat = ?, zatca_seller_legal_name = ?, zatca_building_no = ?, zatca_street = ?, zatca_district = ?, zatca_city = ?, zatca_postal_code = ?, zatca_country_code = ? WHERE branch_id = ?;";
}

// Set timezone to Asia/Riyadh when creating new branch
date_default_timezone_set('Asia/Riyadh');

?>