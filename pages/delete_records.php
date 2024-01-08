<?php
include '../shared_functions/database_functions.php';
include '../shared_functions/print_functions.php';

function handleDeletePatchRequest() {
  global $db_conn;

  // Enable error reporting
  error_reporting(E_ALL);
  ini_set('display_errors', 1);

  // Set up global variables 
  $errorMessage = null;
  $patchID = $_POST['patch_id'];
  $patchExists = false;

  if (connectToDB()) {
      // Check if PatchID exists
      $checkQuery = "SELECT 1 FROM PumpkinPatch WHERE PatchID = :patchID";
      $checkStmt = oci_parse($db_conn, $checkQuery);
      oci_bind_by_name($checkStmt, ":patchID", $patchID);
      oci_execute($checkStmt);
      if (oci_fetch_array($checkStmt)) {
          $patchExists = true;
      }

      if ($patchExists) {
          // QUERY 2: DELETE Operation - PumpkinPatch Table
          $query = "DELETE FROM PumpkinPatch WHERE PatchID = :patchID";
          $stmt = oci_parse($db_conn, $query);
          oci_bind_by_name($stmt, ":patchID", $patchID);
          $r = oci_execute($stmt);

          if (!$r) {
              $e = oci_error($stmt);
              $errorMessage = "Error: " . htmlentities($e['message']);
              oci_rollback($db_conn);
          } else {
              oci_commit($db_conn);
          }
      } else {
          echo "<script type='text/javascript'>let showPatchIdErrorModal = true;</script>";
      }
      disconnectFromDB();
  }
  
  return [$patchExists, $errorMessage];
}

list($patchExists, $errorMessage) = [false, null];
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  list($patchExists, $errorMessage) = handleDeletePatchRequest();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Delete Pumpkin Patch - Pumpkin Patch Management</title>
  <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>

<body class="bg-gray-100 flex-center">
  <div class="form-card">
    <button class="back-button" onclick="goBack();">
      <img src="../images/back_button.png" alt="Go Back">
    </button>
    <h1 class="text-3xl text-center">Delete Pumpkin Patch</h1>
    <div class="form-body">
      <form method="POST" action="delete_records.php" onsubmit="return confirmDelete();">
        <div class="form-group">
          <label for="patch-id" class="form-label">Patch ID:</label>
          <input type="number" id="patch-id" name="patch_id" required class="form-select">
        </div>
        <div class="form-group">
          <input type="submit" value="Delete Patch" class="form-submit">
        </div>
      </form>
      <div id="patchIdErrorModal" class="custom-alert" style="display: none;">
        <div class="alert-overlay" onclick="closePatchIdErrorModal()"></div>
        <div class="alert-container">
          <div class="alert-content">
            <p class="alert-title">Invalid Patch ID</p>
            <p>The provided Patch ID does not exist in the database. Please enter a valid Patch ID.</p>
            <button onclick="closePatchIdErrorModal()" class="alert-button">OK</button>
          </div>
        </div>
      </div>
      <?php if (isset($errorMessage) && $errorMessage): ?>
      <p class="error-message"><?php echo $errorMessage; ?></p>
      <?php endif; ?>
      <?php if ($patchExists && !$errorMessage): ?>
      <div class="output-container" id="outputContainer">
        <p class="success-message"> Successfully deleted Pumpkin Patch with the given ID.</p>
        <?php 
            printPumpkinPatch();
            printPatchMap();
            printMapRegion();
            printPatchTracksVariety();
            printEquipmentLog();
            printMarketingPlan();
          ?>
      </div>
      <div class="form-group">
        <button class="toggle-button" onclick="toggleOutput()">Hide Results</button>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <script>
  const goBack = () => {
    // https://www.students.cs.ubc.ca/~chenkai/project_a2v5h_e0p8y_y7v1z/pages/owner_search.php
    // https://www.students.cs.ubc.ca/~cli66/pages/owner_search.php
    // https://www.students.cs.ubc.ca/~hzhou2/project_a2v5h_e0p8y_y7v1z/pages/owner_search.php
    window.location.href = 'https://www.students.cs.ubc.ca/~chenkai/project_a2v5h_e0p8y_y7v1z/pages/owner_search.php';
  }

  const confirmDelete = () => {
    return confirm(
      'Are you sure you want to delete this pumpkin patch? It will delete all data associated with the patch.');
  }

  const closePatchIdErrorModal = () => {
    document.getElementById('patchIdErrorModal').style.display = 'none';
  }

  if (typeof showPatchIdErrorModal !== 'undefined' && showPatchIdErrorModal) {
    document.getElementById('patchIdErrorModal').style.display = 'flex';
  }

  let isOutputVisible = true;
  const toggleOutput = () => {
    const outputContainer = document.getElementById('outputContainer');
    const toggleBtn = document.querySelector('.toggle-button');

    isOutputVisible = !isOutputVisible;
    outputContainer.style.display = isOutputVisible ? 'block' : 'none';
    toggleBtn.textContent = isOutputVisible ? 'Hide Results' : 'Show Results';
  }
  </script>
</body>

</html>