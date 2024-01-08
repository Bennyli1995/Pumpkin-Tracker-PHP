<?php
include '../shared_functions/database_functions.php';
include '../shared_functions/print_functions.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

function minAvgActivityFee() {
    global $db_conn;
    if (connectToDB()) {
        // QUERY 9: Nested Aggregation with Group By.
        $query = "SELECT p.PatchName, ROUND(AVG(a.Fee), 2) AS avg_fee
        FROM PumpkinPatch p
        JOIN Activities a ON p.PatchID = a.PatchID
        GROUP BY p.PatchName
        HAVING AVG(a.Fee) >= (
            SELECT MIN(AVG(a2.Fee))
            FROM Activities a2
            JOIN PumpkinPatch p2 ON a2.PatchID = p2.PatchID
            GROUP BY p2.PatchID
        )
        ORDER BY AVG(a.Fee)";

        $stmt = oci_parse($db_conn, $query);
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

$minAvgActivityFee = minAvgActivityFee();
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
    <h1 class="text-3xl text-center">Average Pumpkin Patch Activity Fees</h1>
        <div class="text-center">
            <p class='text-xl'>Pumpkin Patch Activity Fees sorted from Minimum to Maximum Average Fees</p>
        </div>
        <?php if (!empty($minAvgActivityFee)): ?>
            <div class="output-container">
                <table class="table">
                    <tr>
                        <th>Patch Name</th>
                        <th>Sorted Average Activity Fee</th>
                    </tr>
                    <?php foreach ($minAvgActivityFee as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['PATCHNAME']) ?></td>
                            <td><?= htmlspecialchars($row['AVG_FEE']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>  
        <?php else: ?>
            <p>No patches found with minimum average fees.</p>
        <?php endif; ?>
  </div>
  <script>
  const goBack = () => {
    window.location.href = 'https://www.students.cs.ubc.ca/~chenkai/project_a2v5h_e0p8y_y7v1z/pages/guest_search.php';
  }
  </script>
</body>

</html>
