<?php
include '../shared_functions/database_functions.php';
include '../shared_functions/print_functions.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

function fetchPatchNames() {
    global $db_conn;
    $patchNames = [];
    if (connectToDB()) {
        $query = "SELECT PatchName FROM PumpkinPatch";
        $stmt = oci_parse($db_conn, $query);
        oci_execute($stmt);
        while ($row = oci_fetch_assoc($stmt)) {
            $patchNames[] = $row['PATCHNAME'];
        }
        disconnectFromDB();
    }
    return $patchNames;
}

function countEvents($selectedPatch) {
    global $db_conn;
    if (connectToDB()) {
        // QUERY 7: Aggregation with Group By - PumpkinPatch, MarketingPlan, and SpecialEvent Tables.
        // -- Find the number of special events hosted by the selected pumpkin patch
        $query = "SELECT pp.PatchID, pp.PatchName, COUNT(se.EventID) AS EventCount
        FROM PumpkinPatch pp
        JOIN MarketingPlan mp ON pp.PatchID = mp.PatchID
        JOIN SpecialEvent se ON mp.PlanName = se.PlanName
        WHERE pp.PatchName = :selectedPatch
        GROUP BY pp.PatchID, pp.PatchName";

        $stmt = oci_parse($db_conn, $query);
        oci_bind_by_name($stmt, ':selectedPatch', $selectedPatch);
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
    $selectedPatch = $_POST['selectedPatch'] ?? 0;
    $patchResults = countEvents($selectedPatch);
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
        <h1 class="text-3xl text-center">Show Number Of Special Events Hosted by Patch</h1>
        <div class="form-body">
            <form method="POST" action="count_events_guests.php">
                <select name="selectedPatch" id="selectedPatch">
                    <option value="">Select Pumpkin Patch</option>
                    <?php 
                        $tables = fetchPatchNames();
                        foreach ($tables as $table) {
                            echo "<option value='$table'>$table</option>";
                        }
                    ?>
                </select>
                <button type="submit" class="form-submit">Show Event Count</button>
            </form>
        </div> 
        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <?php if (!empty($patchResults)): ?>
                <div class="output-container">
                    <table class="table">
                        <tr>
                            <th>Patch ID</th>
                            <th>Patch Name</th>
                            <th>Number of Special Events Hosted</th>
                        </tr>
                        <?php foreach ($patchResults as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['PATCHID']) ?></td>
                                <td><?= htmlspecialchars($row['PATCHNAME']) ?></td>
                                <td><?= htmlspecialchars($row['EVENTCOUNT']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php else: ?>
                    <p style="text-align: center;">No special events found.</p>
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
