<?php
include '../shared_functions/database_functions.php';
include '../shared_functions/print_functions.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

function findPumpkinPatchesByMapRegionSize($sizeThreshold) {
    global $db_conn;
    if (connectToDB()) {
        // QUERY 6: Join - PumpkinPatch, PatchMap, and MapRegion Tables.
        // -- Find the name & address of the pumpkin patch(es) containing the map region sizes >= x.
        $query = "SELECT pp.PatchName, pp.PatchAddress, COUNT(mr.RegionID) AS RegionCount
        FROM PumpkinPatch pp
        JOIN PatchMap pm ON pp.PatchID = pm.PatchID
        JOIN MapRegion mr ON pm.MapID = mr.MapID
        WHERE mr.RegionSize >= :sizeThreshold
        GROUP BY pp.PatchName, pp.PatchAddress";

        $stmt = oci_parse($db_conn, $query);
        oci_bind_by_name($stmt, ':sizeThreshold', $sizeThreshold);
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
    $sizeThreshold = $_POST['sizeThreshold'] ?? 0;
    $patchResults = findPumpkinPatchesByMapRegionSize($sizeThreshold);
    // echo '<pre>';
    // print_r($patchResults);
    // echo '</pre>';
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
        <div class="form-group">
        <button class="back-button" onclick="goBack();">
            <img src="../images/back_button.png" alt="Go Back">
        </button>
        </div>
        <br>
        <br>
        <h1 class="text-3xl text-center">Find Pumpkin Patches with Specified Minimum Map Region Size</h1>
        <div class="form-body">
            <form action="search_map_region_guests.php" method="post">
                <div class="form-group">
                    <label for="sizeThreshold" class="form-label">Minimum Map Region Size:</label>
                    <input type="number" id="sizeThreshold" name="sizeThreshold" class="form-select" min="1" required>
                    <button type="submit" class="form-submit">Show Patches</button>
                </div>
            </form>
        </div>
        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <?php if (!empty($patchResults)): ?>
                <div class="output-container">
                    <table class="table">
                        <tr>
                            <th>Patch Name</th>
                            <th>Patch Address</th>
                            <th>Number of Matching Map Regions</th>
                        </tr>
                        <?php foreach ($patchResults as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['PATCHNAME']) ?></td>
                                <td><?= htmlspecialchars($row['PATCHADDRESS']) ?></td>
                                <td><?= htmlspecialchars($row['REGIONCOUNT']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">No results found.</p>
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
