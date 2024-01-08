<!--
    Print functions for outputting database search, update, and delete results to html table
-->

<?php
function printPumpkinPatch() {
    printTable("PumpkinPatch");
}

function printPatchMap() {
    printTable("PatchMap");
}

function printMapRegion() {
    printTable("MapRegion");
}

function printPatchTracksVariety() {
    printTable("PatchTracksVariety");
}

function printEquipmentLog() {
    printTable("EquipmentLog");
}

function printMarketingPlan() {
    printTable("MarketingPlan");
}

function fetchAllPumpkinPatches() {
    global $db_conn;
    $patches = [];

    if (connectToDB()) {
        $query = "SELECT * FROM PumpkinPatch ORDER BY PatchID";
        $stmt = oci_parse($db_conn, $query);
        oci_execute($stmt);

        oci_fetch_all($stmt, $patches, null, null, OCI_FETCHSTATEMENT_BY_ROW);
        disconnectFromDB();
    }
    return $patches;
}

function printTable($tableName) {
    global $db_conn;

    $query = "SELECT * FROM " . $tableName;
    $stmt = oci_parse($db_conn, $query);
    oci_execute($stmt);
    oci_fetch_all($stmt, $rows, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);

    if ($rows) {
        echo "<div class='details-container'>";
        echo "<h4 class='details-title'>" . convertToTitleCase($tableName) . " Details:</h4>";
        autogenerateTable($rows);
        echo "</div>";
    } else {
        echo "<p class='error-message'>No records found in " . convertToTitleCase($tableName) . ".</p>";
    }
}

function printKidsActivityDetails($activityID) {
  global $db_conn;

  // WHERE ActivityID = :activityID => May implement for later
  $query = "SELECT * FROM KidsActivities ORDER BY ActivityID";
  $stmt = oci_parse($db_conn, $query);
  oci_bind_by_name($stmt, ":activityID", $activityID);
  oci_execute($stmt);

  oci_fetch_all($stmt, $rows, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);

  if ($rows) {
      echo "<div class='details-container'>";
      echo "<h4 class='details-title'>Kids Activity Details:</h4>";
      autogenerateTable($rows);
      echo "</div>";
  } else {
      echo "<p class='error-message'>Error fetching kids activity details.</p>";
  }
}

function printAdultsActivityDetails($activityID) {
  global $db_conn;
  
//   WHERE ActivityID = :activityID

  $query = "SELECT * FROM AdultActivities ORDER BY ActivityID";
  $stmt = oci_parse($db_conn, $query);
  oci_bind_by_name($stmt, ":activityID", $activityID);
  oci_execute($stmt);

  oci_fetch_all($stmt, $rows, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);

  if ($rows) {
      echo "<div class='details-container'>";
      echo "<h4 class='details-title'>Adults Activity Details:</h4>";
      autogenerateTable($rows);
      echo "</div>";
  } else {
      echo "<p class='error-message'>Error fetching adults activity details.</p>";
  }
}

function printActivityDetails($activityID) {
  global $db_conn;
  // WHERE ActivityID = :activityID

  $query = "SELECT * FROM Activities ORDER BY ActivityID";
  $stmt = oci_parse($db_conn, $query);
  oci_bind_by_name($stmt, ":activityID", $activityID);
  oci_execute($stmt);

  oci_fetch_all($stmt, $rows, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);

  if ($rows) {
      echo "<div class='details-container'>";
      echo "<h4 class='details-title'>Activity Details:</h4>"; 
      autogenerateTable($rows);
      echo "</div>"; 
  } else {
      echo "<p class='error-message'>Error fetching activity details.</p>";
  }
}

function printResults($result){
    oci_fetch_all($result, $rows, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
    
    if ($rows) {
        autogenerateTable($rows);
    } else {
        echo "No results found";
    }
}

function printResultsWithArrays($patches){
    if (!empty($patches)) {
        echo '<table class="table">';
        echo '<tr>';
        foreach (array_keys($patches[0]) as $columnName) {
            echo "<th>" . convertToTitleCase($columnName) . "</th>";
        }
        echo "</tr>";
        foreach ($patches as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='text-center'>No results found</p>";
    }
}

function convertToTitleCase($string) {
  // Replace underscores with spaces and capitalize each word
  return ucwords(str_replace('_', ' ', strtolower($string)));
}

function autogenerateTable($rows){ 
  echo '<table style="width: 100%; border-collapse: collapse; margin-top: 2rem;">';
  echo '<tr style="background-color: #f7fafc; text-align: left;">';

  foreach ($rows[0] as $columnName => $value) {
      echo "<th style='padding: 8px; border: 1px solid #e2e8f0;'>" . convertToTitleCase($columnName) . "</th>";
  }

  echo "</tr>";

  foreach ($rows as $row) {
      echo "<tr>";
      foreach ($row as $value) {
          echo "<td style='padding: 8px; border: 1px solid #e2e8f0;'>" . htmlspecialchars($value) . "</td>";
      } 
      echo "</tr>";
  }

  echo "</table>";
}

?>