<?php
include '../shared_functions/database_functions.php';
include '../shared_functions/print_functions.php';

// Fetch all pumpkin patches with details
function fetchPumpkinPatches() {
    global $db_conn;
    $patches = [];
    if (connectToDB()) {
        $query = "SELECT * FROM PumpkinPatch ORDER BY PatchID";
        $stmt = oci_parse($db_conn, $query);
        oci_execute($stmt);
        while ($row = oci_fetch_assoc($stmt)) {
            $patches[$row['PATCHID']] = $row;
        }
        disconnectFromDB();
    }
    return $patches;
}

// Handling the update request
function handleUpdatePatchRequest($patches) {
    global $db_conn;
    
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $errorMessage = null;
    $successMessage = null;
    $patchID = $_POST['patch_id'];
    $patchDetails = $patches[$patchID] ?? null; // Get the original details

    if (!$patchDetails) {
        $errorMessage = "Error! Patch ID not found.";
        return [$errorMessage, $successMessage];
    }

    $newOwnership = isset($_POST['no_change_ownership']) ? $patches[$patchID]['PATCHOWNERSHIP'] : $_POST['new_ownership'];
    $newSize = isset($_POST['no_change_size']) ? $patches[$patchID]['PATCHSIZE'] : $_POST['new_size'];
    $newAddress = isset($_POST['no_change_address']) ? $patches[$patchID]['PATCHADDRESS'] : $_POST['new_address'];
    $newName = isset($_POST['no_change_name']) ? $patches[$patchID]['PATCHNAME'] : $_POST['new_name'];

    if (empty($newOwnership) || empty($newSize) || empty($newAddress) || empty($newName)) {
        $errorMessage = "Error! All fields must be filled out or marked as 'No change'.";
        return [$errorMessage, $successMessage];
    }

    // Check for unique PatchName
    if (!isset($_POST['no_change_name'])) { // Only check if the name is being changed
      $query = "SELECT COUNT(*) AS num FROM PumpkinPatch WHERE PatchName = :newName AND PatchID != :patchID";
      $stmt = oci_parse($db_conn, $query);
      oci_bind_by_name($stmt, ":newName", $newName);
      oci_bind_by_name($stmt, ":patchID", $patchID);
      oci_execute($stmt);
      $row = oci_fetch_assoc($stmt);

      if ($row && $row['NUM'] > 0) {
          $errorMessage = "Error! The patch name must be unique.";
          return [$errorMessage, $successMessage];
      }
    }

    if (connectToDB()) {
        // QUERY 3: UPDATE Operation - PumpkinPatch Table
        $query = "UPDATE PumpkinPatch 
          SET PatchOwnership = :newOwnership, PatchSize = :newSize, PatchAddress = :newAddress, PatchName = :newName 
          WHERE PatchID = :patchID";
        $stmt = oci_parse($db_conn, $query);
        oci_bind_by_name($stmt, ":newOwnership", $newOwnership);
        oci_bind_by_name($stmt, ":newSize", $newSize);
        oci_bind_by_name($stmt, ":newAddress", $newAddress);
        oci_bind_by_name($stmt, ":newName", $newName);
        oci_bind_by_name($stmt, ":patchID", $patchID);

        if (oci_execute($stmt)) {
            oci_commit($db_conn);
            $successMessage = "Patch updated successfully.";
        } else {
            $e = oci_error($stmt);
            $errorMessage = "Error: " . htmlentities($e['message']);
            oci_rollback($db_conn);
        }
        disconnectFromDB();
    } else {
      echo "<script type='text/javascript'>let showErrorModal = true;</script>";
    }
    return [$errorMessage, $successMessage]; 
}

$patches = fetchPumpkinPatches();

list($errorMessage, $successMessage) = [null, null];
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    list($errorMessage, $successMessage) = handleUpdatePatchRequest($patches);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Pumpkin Patch - Pumpkin Patch Management</title>
  <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>

<body class="bg-gray-100 flex-center">

  <div class="form-card">
    <button class="back-button" onclick="goBack();">
      <img src="../images/back_button.png" alt="Go Back">
    </button>
    <h1 class="text-3xl text-center">Update Pumpkin Patch</h1>
    <div class="form-body">
      <form method="POST" action="update_records.php">
        <div class="form-group">
          <label for="patch-id" class="form-label">Select Pumpkin Patch:</label>
          <select id="patch-id" name="patch_id" required class="form-select">
            <option value="">--Select a Patch to Update--</option>
            <?php foreach ($patches as $id => $patch): ?>
            <option value="<?php echo htmlspecialchars($id); ?>">
              ID: <?php echo htmlspecialchars($id); ?> - <?php echo htmlspecialchars($patch['PATCHNAME']); ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="new-ownership" class="form-label">New Ownership:</label>
          <input type="text" id="new-ownership" name="new_ownership" class="form-select">
          <input type="checkbox" class="checkbox" id="no-change-ownership" name="no_change_ownership"
            onclick="toggleNoChange('new-ownership', this);">
          <label for="no-change-ownership">No change</label>
        </div>
        <div class="form-group">
          <label for="new-size" class="form-label">New Size:</label>
          <input type="number" id="new-size" name="new_size" class="form-select">
          <input type="checkbox" class="checkbox" id="no-change-size" name="no_change_size"
            onclick="toggleNoChange('new-size', this);">
          <label for="no-change-size">No change</label>
        </div>
        <div class="form-group">
          <label for="new-address" class="form-label">New Address:</label>
          <input type="text" id="new-address" name="new_address" class="form-select">
          <input type="checkbox" class="checkbox" id="no-change-address" name="no_change_address"
            onclick="toggleNoChange('new-address', this);">
          <label for="no-change-address">No change</label>
        </div>
        <div class="form-group">
          <label for="new-name" class="form-label">New Name:</label>
          <input type="text" id="new-name" name="new_name" class="form-select">
          <input type="checkbox" class="checkbox" id="no-change-name" name="no_change_name"
            onclick="toggleNoChange('new-name', this);">
          <label for="no-change-name">No change</label>
        </div>
        <div class="form-group">
          <input type="submit" value="Update Pumpkin Patch" class="form-submit">
        </div>
      </form>
      <?php if ($errorMessage): ?>
      <div id="errorModal" class="custom-alert" style="display: flex;">
        <div class="alert-overlay" onclick="closeErrorModal()"></div>
        <div class="alert-container">
          <div class="alert-content">
            <p class="alert-title">Update Error</p>
            <p><?php echo $errorMessage; ?></p>
            <button onclick="closeErrorModal()" class="alert-button">OK</button>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($successMessage): ?>
      <div class="output-container" id="outputContainer">
        <p class="success-message"><?php echo $successMessage; ?></p>
        <?php printPumpkinPatch(); ?>
      </div>
      <div class="form-group">
        <button class="toggle-button" onclick="toggleOutput()">Hide Results</button>
      </div>
      <?php endif; ?>
    </div>
    <script>
    const closeErrorModal = () => {
      document.getElementById('errorModal').style.display = 'none';
    }

    if (typeof showErrorModal !== 'undefined' && showErrorModal) {
      document.getElementById('errorModal').style.display = 'flex';
    }

    const toggleNoChange = (fieldId, checkbox) => {
      let field = document.getElementById(fieldId);
      if (checkbox.checked) {
        // Disable the field and set its value to the original value
        field.disabled = true;
        field.value = field.getAttribute('data-original-value');
        field.classList.add('disabled-input');
      } else {
        // Enable the field for editing
        field.disabled = false;
        field.value = '';
        field.classList.remove('disabled-input');
      }
    }

    const goBack = () => {
      // https://www.students.cs.ubc.ca/~cli66/pages/owner_search.php
      // https://www.students.cs.ubc.ca/~hzhou2/project_a2v5h_e0p8y_y7v1z/pages/owner_search.php
      window.location.href =
        'https://www.students.cs.ubc.ca/~chenkai/project_a2v5h_e0p8y_y7v1z/pages/owner_search.php';
    }

    let isOutputVisible = true;
    const toggleOutput = () => {
      const outputContainer = document.getElementById('outputContainer');
      const toggleBtn = document.querySelector('.toggle-button');

      isOutputVisible = !isOutputVisible;
      outputContainer.style.display = isOutputVisible ? 'block' : 'none';
      toggleBtn.textContent = isOutputVisible ? 'Hide Results' : 'Show Results';
    }

    // Populate original values and bind the change event to the select dropdown
    window.onload = function() {
      var patches = <?php echo json_encode($patches); ?>;
      var patchSelect = document.getElementById('patch-id');
      patchSelect.onchange = function() {
        var selectedId = this.value;
        if (selectedId && patches[selectedId]) {
          var patch = patches[selectedId];
          document.getElementById('new-ownership').setAttribute('data-original-value', patch.PATCHOWNERSHIP);
          document.getElementById('new-size').setAttribute('data-original-value', patch.PATCHSIZE);
          document.getElementById('new-address').setAttribute('data-original-value', patch.PATCHADDRESS);
          document.getElementById('new-name').setAttribute('data-original-value', patch.PATCHNAME);
        }
      };
    };
    </script>
</body>

</html>