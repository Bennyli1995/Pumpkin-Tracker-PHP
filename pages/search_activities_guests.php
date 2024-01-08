<?php
include '../shared_functions/database_functions.php';
include '../shared_functions/print_functions.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

function findPumpkinPatchesByActivities($activityThreshold) {
    global $db_conn;
    if (connectToDB()) {
        // QUERY 8: Aggregation with Having - PumpkinPatch, MarketingPlan, and SpecialEvent Tables.
        $query = "SELECT p.PatchName, COUNT(a.ActivityID) AS NumberOfActivities
                  FROM PumpkinPatch p
                  JOIN Activities a ON p.PatchID = a.PatchID
                  GROUP BY p.PatchName
                  HAVING COUNT(a.ActivityID) >= :activityThreshold";

        $stmt = oci_parse($db_conn, $query);
        oci_bind_by_name($stmt, ':activityThreshold', $activityThreshold);
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
    $activityThreshold = $_POST['activityThreshold'] ?? 0;
    $patchResults = findPumpkinPatchesByActivities($activityThreshold);
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
    <h1 class="text-3xl text-center">Find Pumpkin Patches with Specified Number of Activities</h1>
    <div class="form-body">
      <form action="search_activities_guests.php" method="post">
        <div class="form-group">
          <label for="activityThreshold" class="form-label">Minimum Number of Activities:</label>
          <input type="number" id="activityThreshold" name="activityThreshold" class="form-select" min="1" required>
          <button type="submit" class="form-submit">Find Patches</button>
        </div>
      </form>
    </div>
    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
      <?php if (!empty($patchResults)): ?>
      <div class="output-container">
        <table class="table">
          <tr>
            <th>Patch Name</th>
            <th>Number of Activities</th>
          </tr>
          <?php foreach ($patchResults as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['PATCHNAME']) ?></td>
            <td><?= htmlspecialchars($row['NUMBEROFACTIVITIES']) ?></td>
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
    window.location.href =
      'https://www.students.cs.ubc.ca/~chenkai/project_a2v5h_e0p8y_y7v1z/pages/guest_search.php';
  }
  </script>
</body>

</html>