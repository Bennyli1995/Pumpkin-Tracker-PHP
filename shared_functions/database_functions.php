<?php

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = NULL;
$show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered

function debugAlertMessage($message) {
    global $show_debug_alert_messages;

    if ($show_debug_alert_messages) {
        echo "<script type='text/javascript'>alert('" . $message . "');</script>";
        }
    }

// database query functions
function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
    global $db_conn, $success;

    $statement = oci_parse($db_conn, $cmdstr);

    if (!$statement) {
        echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
        $e = oci_error($db_conn); // For oci_parse errors pass the connection handle
        echo htmlentities($e['message']);
        $success = False;
    }

    $r = oci_execute($statement, OCI_DEFAULT);
    if (!$r) {
        echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
        $e = oci_error($statement); // For oci_execute errors pass the statement handle
        echo htmlentities($e['message']);
        $success = False;
    }

    return $statement;
}

function executeBoundSQL($cmdstr, $list) {
/* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
In this case you don't need to create the statement several times. Bound variables cause a statement to only be
parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection.
See the sample code below for how this function is used */

global $db_conn, $success;
$statement = oci_parse($db_conn, $cmdstr);

    if (!$statement) {
        echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
        $e = oci_error($db_conn);
        echo htmlentities($e['message']);
        $success = False;
    }

    foreach ($list as $tuple) {
        foreach ($tuple as $bind => $val) {
            // echo $val;
            // echo "<br>".$bind."<br>";
            oci_bind_by_name($statement, $bind, $val);
            unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
        }

        $r = oci_execute($statement, OCI_DEFAULT);
        if (!$r) {
            echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
            $e = oci_error($statement); // For oci_execute errors, pass the statement handle
            echo htmlentities($e['message']);
            echo "<br>";
            $success = False;
        }
    }
}

// database connection functions
function connectToDB() {
    global $db_conn;

    $env = parse_ini_file('../.env');
    
    $db_conn = oci_connect($env['DB_USER'], $env['DB_PASS'], $env['DB_CONNECTION_STRING']);

    if ($db_conn) {
        debugAlertMessage("Database is Connected");
        return true;
    } else {
        debugAlertMessage("Cannot connect to database");
        $e = oci_error();
        echo htmlentities($e['message']);
        return false;
    }
}


function disconnectFromDB() {
    global $db_conn;

    debugAlertMessage("Disconnected from database");
    oci_close($db_conn);
}
?>