<?php
include '../shared_functions/database_functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

function fetchAttributesForTable($tableName) {
    global $db_conn;
    $attributes = [];
    if (connectToDB()) {
        // Convert the table name to uppercase
        $tableName = strtoupper($tableName);

        $query = "SELECT column_name FROM user_tab_cols WHERE table_name = :tableName";
        $stmt = oci_parse($db_conn, $query);
        oci_bind_by_name($stmt, ':tableName', $tableName);
        if (oci_execute($stmt)) {
            while ($row = oci_fetch_assoc($stmt)) {
                $attributes[] = $row['COLUMN_NAME'];
            }
        } else {
            $error = oci_error($stmt);
            return ['error' => $error];
        }
        disconnectFromDB();
    } else {
        return ['error' => 'Database connection failed'];
    }
    return $attributes;
}

if (isset($_GET['table'])) {
    $tableName = $_GET['table'];
    header('Content-Type: application/json');
    echo json_encode(fetchAttributesForTable($tableName));
}
?>