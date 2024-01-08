<?php
include '../shared_functions/database_functions.php';
include '../shared_functions/print_functions.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

function fetchVarietyNames() {
    global $db_conn;
    $varietyNames = [];
    if (connectToDB()) {
        $query = "SELECT VarietyName FROM PumpkinVariety";
        $stmt = oci_parse($db_conn, $query);
        oci_execute($stmt);
        while ($row = oci_fetch_assoc($stmt)) {
            $varietyNames[] = $row['VARIETYNAME'];
        }
        disconnectFromDB();
    }
    return $varietyNames;
}

function findPatchByVariety($selectedVarieties) {
    global $db_conn;

    if (connectToDB()) {
        // QUERY 10: Division Query
        // -- Find patches that plants all types of tracked variety.
        $query = "SELECT pp.PatchID, pp.PatchName, pp.PatchAddress
                    FROM PumpkinPatch pp
                    WHERE NOT EXISTS (
                    (
                        SELECT VarietyID FROM PumpkinVariety
                    ) MINUS (
                        SELECT ptv.VarietyID FROM PatchTracksVariety ptv WHERE pp.PatchID = ptv.PatchID
                    )
                    )";

        $stmt = oci_parse($db_conn, $query);
        // oci_bind_by_name($stmt, $bindName, $variety);
        oci_execute($stmt);
        $results = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $results[] = $row;
        }
        disconnectFromDB();
        return $results;
    } else {
        return [];
    }
}

$patchResults = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selectedVarieties = $_POST['selectedVarieties'] ?? 0;
    $patchResults = findPatchByVariety($selectedVarieties);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pumpkin Patch Management for Guests</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="bg-gray-100 flex-center">
    <div class="form-card">
        <button class="back-button" onclick="goBack();">
            <img src="../images/back_button.png" alt="Go Back">
        </button>
        <br>
        <br>
        <h1 class="text-3xl text-center">Pumpkin Patches with All Varieties Planted</h1>
        <div class="form-body">
            <form method="POST" action="search_variety_guests.php">
                <h3>Available Varieties:</h3>
                <ul style="list-style-type: disc; margin-left: 20px;">
                    <?php 
                        $tables = fetchVarietyNames();
                        foreach ($tables as $table) {
                            echo "<li>$table</li>";
                        }
                    ?>
                </ul>
                <button type="submit" class="form-submit">Search</button>
            </form>
        </div> 
        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <?php if (!empty($patchResults)): ?>
                <div class="output-container">
                    <table class="table">
                        <tr>
                            <th>Patch ID</th>
                            <th>Patch Name</th>
                            <th>Patch Address</th>
                        </tr>
                        <?php foreach ($patchResults as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['PATCHID']) ?></td>
                                <td><?= htmlspecialchars($row['PATCHNAME']) ?></td>
                                <td><?= htmlspecialchars($row['PATCHADDRESS']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php else: ?>
                    <p style="text-align: center;">None found.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <script>
        const goBack = () => {
            window.location.href = 'https://www.students.cs.ubc.ca/~chenkai/project_a2v5h_e0p8y_y7v1z/pages/guest_search.php';
        }
    </script>
</body>
</html>
