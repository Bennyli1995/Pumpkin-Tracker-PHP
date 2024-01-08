<?php
include '../shared_functions/database_functions.php';
include '../shared_functions/print_functions.php';

function constructQuery($conditions) {
    // QUERY 4: Selection - PumpkinPatch Table
    $query = "SELECT * FROM PumpkinPatch";
    $queryConditions = [];
    $params = [];
    $errorMessage = null;
    foreach ($conditions as $index => $condition) {
        if (empty(trim($condition['field'])) || empty(trim($condition['operator'])) || trim($condition['value']) === '') {
            $errorMessage = 'Fields cannot be empty.';
            break;
        }
        if (($condition['field'] == 'PatchID' || $condition['field'] == 'PatchSize') && !ctype_digit($condition['value'])) {
            $errorMessage = 'Integer fields must have numeric values.';
            break;
        }
        $field = trim($condition['field']);
        $operator = trim($condition['operator']);
        $logicalOperator = isset($condition['logical']) && $condition['logical'] === 'OR' ? 'OR' : 'AND';
        $param = ":value" . $index;
        $params[$param] = $operator === 'LIKE' ? "%" . $condition['value'] . "%" : $condition['value'];
        $queryConditions[] = ($index > 0 ? " $logicalOperator " : "") . "$field $operator $param";
    }
    if (!empty($queryConditions) && !$errorMessage) {
        $query .= ' WHERE ' . implode(' ', $queryConditions);
    }
    return ['query' => $query, 'params' => $params, 'error' => $errorMessage];
}

function filterPumpkinPatches() {
    global $db_conn;
    $conditions = $_POST['conditions'] ?? [];
    $constructedQuery = constructQuery($conditions);
    if ($constructedQuery['error']) {
        $_SESSION['error'] = $constructedQuery['error'];
        return [];
    }
    $query = $constructedQuery['query'];
    $params = $constructedQuery['params'];
    $patches = [];
    if (connectToDB()) {
        $stmt = oci_parse($db_conn, $query);
        foreach ($params as $placeholder => $value) {
            oci_bind_by_name($stmt, $placeholder, $params[$placeholder]);
        }
        if (!oci_execute($stmt)) {
            $e = oci_error($stmt);
            $_SESSION['error'] = htmlentities($e['message'], ENT_QUOTES);
            return [];
        }
        while ($row = oci_fetch_assoc($stmt)) {
            $patches[] = $row;
        }
        disconnectFromDB();
    } else {
        $_SESSION['error'] = 'Error connecting to the database.';
        return [];
    }
    return $patches;
}

$resultsVisible = false;
$patches = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $patches = filterPumpkinPatches();
    $_SESSION['resultsVisible'] = !empty($patches);
    $resultsVisible = $_SESSION['resultsVisible'];
}
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Filter Pumpkin Patches</title>
  <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>

<body class="bg-gray-100 flex-center">
  <div class="form-card">
    <button class="back-button" onclick="goBack();">
      <img src="../images/back_button.png" alt="Go Back">
    </button>
    <h1 class="text-3xl text-center">Filter Pumpkin Patches</h1>
    <form method="POST" action="" class="form-body">
      <div id='conditions-container'>
        <!-- Existing condition elements here -->
        <div class="condition form-group-search">
          <select name="conditions[0][field]" class="form-select-search">
            <option value="PatchID">Patch ID</option>
            <option value="PatchOwnership">Patch Ownership</option>
            <option value="PatchSize">Patch Size</option>
            <option value="PatchAddress">Patch Address</option>
            <option value="PatchName">Patch Name</option>
          </select>
          <select name="conditions[0][operator]" class="form-select-search">
            <option value="=">Equal To</option>
            <option value="<">Less Than</option>
            <option value=">">Greater Than</option>
            <option value="<=">Less Than Or Equal To</option>
            <option value=">=">Greater Than Or Equal To</option>
            <option value="LIKE">LIKE</option>
          </select>
          <input type="text" name="conditions[0][value]" class="form-input-value">
          <select name="conditions[0][logical]" class="form-select-search">
            <option value="AND">AND</option>
            <option value="OR">OR</option>
          </select>
        </div>
      </div>
      <button type="button" onclick="addCondition()" class="form-submit">Add Condition</button>
      <input type="submit" value="Filter and Get Results" class="form-submit">
      <button type="button" id="showResultsButton" class="toggle-button" onclick="toggleResultsVisibility();"
        style="display: <?php echo isset($_SESSION['resultsVisible']) ? 'block' : 'none'; ?>;">Hide Results</button>
      <div id="resultsContainer" style="display: <?php echo $resultsVisible ? 'block' : 'none'; ?>">
        <?php if ($resultsVisible) printResultsWithArrays($patches); ?>
      </div>
      <?php if ($error): ?>
      <div class="error-message"><?php echo $error; ?></div>
      <?php endif; ?>
    </form>
  </div>
  <script>
  const addCondition = () => {
    const container = document.getElementById('conditions-container');
    const newConditionIndex = container.children.length;
    const newCondition = container.children[0].cloneNode(true);


    newCondition.querySelector('select[name^="conditions"][name$="[field]"]').name =
      `conditions[${newConditionIndex}][field]`;
    newCondition.querySelector('select[name^="conditions"][name$="[operator]"]').name =
      `conditions[${newConditionIndex}][operator]`;
    newCondition.querySelector('input[name^="conditions"][name$="[value]"]').name =
      `conditions[${newConditionIndex}][value]`;
    newCondition.querySelector('select[name^="conditions"][name$="[logical]"]').name =
      `conditions[${newConditionIndex}][logical]`;

    newCondition.querySelector('select[name^="conditions"][name$="[field]"]').selectedIndex = 0;
    newCondition.querySelector('select[name^="conditions"][name$="[operator]"]').selectedIndex = 0;
    newCondition.querySelector('input[name^="conditions"][name$="[value]"]').value = '';
    newCondition.querySelector('select[name^="conditions"][name$="[logical]"]').selectedIndex = 0;

    const deleteButton = document.createElement('button');
    deleteButton.type = 'button';
    deleteButton.textContent = 'Delete';
    deleteButton.className = 'delete-button';
    deleteButton.onclick = function() {
      removeCondition(this);
    };
    newCondition.appendChild(deleteButton);

    container.appendChild(newCondition);
  }

  function removeCondition(button) {
    const condition = button.parentNode;
    condition.parentNode.removeChild(condition);
  }

  const toggleResultsVisibility = () => {
    const resultsContainer = document.getElementById('resultsContainer');
    const button = document.querySelector('.toggle-button');
    resultsContainer.style.display = resultsContainer.style.display === 'none' ? 'block' : 'none';
    button.textContent = resultsContainer.style.display === 'block' ? 'Hide Results' : 'Show Results';
  }

  const goBack = () => {
    window.location.href = 'https://www.students.cs.ubc.ca/~chenkai/project_a2v5h_e0p8y_y7v1z/pages/owner_search.php';
  }
  </script>
</body>

</html>