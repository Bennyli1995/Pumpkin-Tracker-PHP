<?php
include '../shared_functions/database_functions.php';
include '../shared_functions/print_functions.php';

function handleCreateActivityRequest() {
  global $db_conn;

  // Enable error reporting
  // error_reporting(E_ALL);
  // ini_set('display_errors', 1);

  $errorMessage = null;
  $activityID = null;
  $patchID = $_POST['patch_id'];
  $activityName = $_POST['activity_name'];
  $duration = $_POST['activity_duration'];
  $fee = $_POST['activity_fee'];
  $activityDescription = $_POST['activity_description'];
  $isKidsActivity = isset($_POST['is_kids_activity']);
  $isAdultsActivity = isset($_POST['is_adults_activity']);

  if (connectToDB()) {
      // QUERY 1.1: INSERT Operation - Activities Table
      $query = "INSERT INTO Activities (ActivityID, PatchID, ActivityName, Duration, Fee, ActivityDescription) 
        VALUES (ACTIVITIES_SEQ.NEXTVAL, :patchID, :activityName, :duration, :fee, :activityDescription) 
        RETURNING ActivityID INTO :activityID";
      $stmt = oci_parse($db_conn, $query);
      oci_bind_by_name($stmt, ":patchID", $patchID);
      oci_bind_by_name($stmt, ":activityName", $activityName);
      oci_bind_by_name($stmt, ":duration", $duration);
      oci_bind_by_name($stmt, ":fee", $fee);
      oci_bind_by_name($stmt, ":activityDescription", $activityDescription);
      
      $activityID = 0;
      oci_bind_by_name($stmt, ":activityID", $activityID, -1, OCI_B_INT);

      $r = oci_execute($stmt);
      if (!$r) {
          $e = oci_error($stmt);
          if ($e['code'] == '02291') {
            echo "<script type='text/javascript'>let showPatchIdErrorModal = true;</script>";
          } else {
              echo "<p>Error: " . htmlentities($e['message']) . "</p>";
          }
          oci_rollback($db_conn);
          disconnectFromDB();
          return;
      }

      if ($activityID) {
          if ($isKidsActivity) {
              $guardianRequirement = $_POST['guardian_requirement'];
              // QUERY 1.2: INSERT Operation - Kids Activities Table
              $kidsQuery = "INSERT INTO KidsActivities (ActivityID, GuardianRequirement) VALUES (:activityID, :guardianRequirement)";
              $kidsStmt = oci_parse($db_conn, $kidsQuery);
              oci_bind_by_name($kidsStmt, ":activityID", $activityID);
              oci_bind_by_name($kidsStmt, ":guardianRequirement", $guardianRequirement);
              oci_execute($kidsStmt);
          }

          if ($isAdultsActivity) {
              $ageRequirement = $_POST['age_requirement'];
              $alcoholInvolvement = $_POST['alcohol_involvement'];
              // QUERY 1.3: INSERT Operation - Adults Activities Table
              $adultsQuery = "INSERT INTO AdultActivities (ActivityID, AgeRequirement, AlcoholInvolvement) VALUES (:activityID, :ageRequirement, :alcoholInvolvement)";
              $adultsStmt = oci_parse($db_conn, $adultsQuery);
              oci_bind_by_name($adultsStmt, ":activityID", $activityID);
              oci_bind_by_name($adultsStmt, ":ageRequirement", $ageRequirement);
              oci_bind_by_name($adultsStmt, ":alcoholInvolvement", $alcoholInvolvement);
              oci_execute($adultsStmt);
          }

          oci_commit($db_conn);
      } else {
        $e = oci_error($stmt);
        oci_rollback($db_conn);
        $errorMessage = "Error: " . htmlentities($e['message']);
      }
      
      disconnectFromDB();
  }
  return [$activityID, $errorMessage, $isKidsActivity, $isAdultsActivity];
}

list($activityID, $errorMessage, $isKidsActivity, $isAdultsActivity) = [null, null, false, false];
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  list($activityID, $errorMessage, $isKidsActivity, $isAdultsActivity) = handleCreateActivityRequest();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Activity - Pumpkin Patch Management</title>
  <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>

<body class="bg-gray-100 flex-center">
  <div class="form-card">
    <button class="back-button" onclick="goBack();">
      <img src="../images/back_button.png" alt="Go Back">
    </button>
    <h1 class="text-3xl text-center">Create Activity</h1>
    <div class="form-body">
      <form method="POST" action="create_activity.php" onsubmit="return validateActivityForm();">
        <div class="form-group">
          <label for="patch-id" class="form-label">Patch ID:</label>
          <input type="number" id="patch-id" name="patch_id" required class="form-select">
        </div>
        <div class="form-group">
          <label for="activity-name" class="form-label">Activity Name:</label>
          <input type="text" id="activity-name" name="activity_name" required class="form-select">
        </div>
        <div class="form-group">
          <label for="activity-duration" class="form-label">Duration (in minutes):</label>
          <input type="number" id="activity-duration" name="activity_duration"  class="form-select">
        </div>
        <div class="form-group">
          <label for="activity-fee" class="form-label">Fee:</label>
          <input type="number" id="activity-fee" name="activity_fee"  class="form-select">
        </div>
        <div class="form-group">
          <label for="activity-description" class="form-label">Description:</label>
          <textarea id="activity-description" name="activity_description"  class="form-select"></textarea>
        </div>
        <div class="form-group">
          <input type="checkbox" class="checkbox" id="is-kids-activity" name="is_kids_activity"
            onchange="toggleActivityFields()">
          <label for="is-kids-activity">This is a kids activity</label>
        </div>
        <div id="kids-activity-fields" style="display: none;">
          <div class="form-group">
            <label for="guardian-requirement" class="form-label">Guardian Requirement:</label>
            <input type="number" id="guardian-requirement" name="guardian_requirement" class="form-select">
          </div>
        </div>
        <div class="form-group">
          <input type="checkbox" class="checkbox" id="is-adults-activity" name="is_adults_activity"
            onchange="toggleActivityFields()">
          <label for="is-adults-activity">This is an adults activity</label>
        </div>
        <div id="adults-activity-fields" style="display: none;">
          <div class="form-group">
            <label for="age-requirement" class="form-label">Age Requirement:</label>
            <input type="number" id="age-requirement" name="age_requirement" class="form-select">
          </div>
          <div class="form-group">
            <label for="alcohol-involvement" class="form-label">Alcohol Involvement:</label>
            <select id="alcohol-involvement" name="alcohol_involvement" class="form-select">
              <option value="">Select an option</option>
              <option value="0">No Alcohol</option>
              <option value="1">Alcohol Involved</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <input type="submit" value="Create Activity" class="form-submit">
        </div>
        <div id="customAlert" class="custom-alert" style="display: none;">
          <div class="alert-overlay" onclick="closeCustomAlert()"></div>
          <div class="alert-container">
            <div class="alert-content">
              <p class="alert-title">Invalid Activity Type</p>
              <p>Please select at least one activity type.</p>
              <button onclick="closeCustomAlert()" class="alert-button">OK</button>
            </div>
          </div>
        </div>
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
      </form>
      <?php if ($activityID): ?>
      <div class="output-container" id="outputContainer">
        <p class="success-message">New activity created successfully with ID: <?php echo $activityID; ?>.</p>
        <?php printActivityDetails($activityID); ?>
        <?php if ($isKidsActivity): printKidsActivityDetails($activityID); endif; ?>
        <?php if ($isAdultsActivity): printAdultsActivityDetails($activityID); endif; ?>
      </div>
      <div class="form-group">
        <button class="toggle-button" onclick="toggleOutput()">Hide Results</button>
      </div>
      <?php elseif ($errorMessage): ?>
      <p class="error-message"><?php echo $errorMessage; ?></p>
      <?php endif; ?>
    </div>
  </div>
  <script>
  let isOutputVisible = true;

  const goBack = () => {
    // NEED TO CHANGE DEPENDING ON YOUR OWN LOCAL SERVER!
    // https://www.students.cs.ubc.ca/~chenkai/project_a2v5h_e0p8y_y7v1z/pages/owner_search.php
    // https://www.students.cs.ubc.ca/~cli66/pages/owner_search.php
    // https://www.students.cs.ubc.ca/~hzhou2/project_a2v5h_e0p8y_y7v1z/pages/owner_search.php
    window.location.href =
      'https://www.students.cs.ubc.ca/~chenkai/project_a2v5h_e0p8y_y7v1z/pages/owner_search.php';
  }

  const toggleOutput = () => {
    const outputContainer = document.getElementById('outputContainer');
    const toggleBtn = document.querySelector('.toggle-button');

    isOutputVisible = !isOutputVisible;
    outputContainer.style.display = isOutputVisible ? 'block' : 'none';

    toggleBtn.textContent = isOutputVisible ? 'Hide Results' : 'Show Results';
  }
  const validateActivityForm = () => {
    let isKidsActivity = document.getElementById('is-kids-activity').checked;
    let isAdultsActivity = document.getElementById('is-adults-activity').checked;
    if (!isKidsActivity && !isAdultsActivity) {
      document.getElementById('customAlert').style.display = 'block';
      return false;
    }
    return true;
  }

  const closeCustomAlert = () => {
    document.getElementById('customAlert').style.display = 'none';
  }

  const toggleActivityFields = () => {
    let isKidsActivity = document.getElementById('is-kids-activity').checked;
    let isAdultsActivity = document.getElementById('is-adults-activity').checked;
    document.getElementById('kids-activity-fields').style.display = isKidsActivity ? 'block' : 'none';
    document.getElementById('adults-activity-fields').style.display = isAdultsActivity ? 'block' : 'none';
  }

  // This function will be used to close the modal
  function closePatchIdErrorModal() {
    document.getElementById('patchIdErrorModal').style.display = 'none';
  }

  // If the PHP flag is set, show the modal
  if (typeof showPatchIdErrorModal !== 'undefined' && showPatchIdErrorModal) {
    document.getElementById('patchIdErrorModal').style.display = 'flex';
  }
  </script>
</body>

</html>