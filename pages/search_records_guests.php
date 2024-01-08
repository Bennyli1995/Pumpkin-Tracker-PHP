<?php
include '../shared_functions/database_functions.php';
include '../shared_functions/print_functions.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

function fetchTableNames() {
    global $db_conn;
    $tables = [];
    if (connectToDB()) {
        $query = "SELECT table_name FROM user_tables";
        $stmt = oci_parse($db_conn, $query);
        oci_execute($stmt);
        while ($row = oci_fetch_assoc($stmt)) {
            $tables[] = $row['TABLE_NAME'];
        }
        disconnectFromDB();
    }
    return $tables;
}

function fetchSelectedData($tableName, $selectedAttributes) {
    global $db_conn;
    $result = [];
    if (connectToDB()) {
        $tableName = strtoupper($tableName);
        $selectedAttributes = array_map('strtoupper', $selectedAttributes);
        // QUERY 5: Projection - dynamically determined table
        $query = "SELECT " . implode(', ', $selectedAttributes) . " FROM " . $tableName;
        $stmt = oci_parse($db_conn, $query);
        oci_execute($stmt);
        while ($row = oci_fetch_assoc($stmt)) {
            $result[] = $row;
        }
        disconnectFromDB();
    }
    return $result;
}

$attributes = [];
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $selectedTable = $_POST['selectedTable'] ?? '';
    $selectedAttributes = $_POST['attributes'] ?? [];
    if (!empty($selectedTable) && !empty($selectedAttributes)) {
        $attributes = fetchSelectedData($selectedTable, $selectedAttributes);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dynamic Table Projection</title>
  <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>

<body class="bg-gray-100 flex-center">
  <div class="form-card">
    <button class="back-button" onclick="goBack();">
      <img src="../images/back_button.png" alt="Go Back">
    </button>
    <h1 class="text-3xl text-center">Pumpkin Patch Projections</h1>
    <br>
    <form method="POST" action="">
      <select name="selectedTable" id="tableSelect" onchange="loadAttributes()">
        <option value="">Select Table</option>
        <?php 
                    $tables = fetchTableNames();
                    foreach ($tables as $table) {
                        echo "<option value='$table'>$table</option>";
                    }
                ?>
      </select>
      <div id="attributeContainer"></div>
      <input type="submit" value="Show" class="form-submit">
    </form>
    <?php if (!empty($attributes)): ?>
    <div class="output-container">
      <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <tr style="background-color: #f7fafc; text-align: left;">
          <?php foreach (array_keys($attributes[0]) as $columnName): ?>
          <th style="padding: 8px; border: 1px solid #e2e8f0;">
            <?= convertToTitleCase($columnName) ?>
          </th>
          <?php endforeach; ?>
        </tr>
        <?php foreach ($attributes as $row): ?>
        <tr>
          <?php foreach ($row as $value): ?>
          <td style="padding: 8px; border: 1px solid #e2e8f0;">
            <?= htmlspecialchars($value) ?>
          </td>
          <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
    <?php endif; ?>
  </div>


  <script>
  const goBack = () => {
            window.location.href = 'https://www.students.cs.ubc.ca/~chenkai/project_a2v5h_e0p8y_y7v1z/pages/guest_search.php';
  }

  const loadAttributes = () => {
    const tableName = document.getElementById('tableSelect').value;
    const attributeContainer = document.getElementById('attributeContainer');
    attributeContainer.innerHTML = '';

    if (tableName) {
      fetch('fetch_attributes.php?table=' + tableName)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(attributes => {
          if (attributes.error) {
            throw new Error(attributes.error);
          }
          attributes.forEach(attr => {
            const div = document.createElement('div');
            div.className = 'form-group';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'checkbox';
            checkbox.name = 'attributes[]';
            checkbox.value = attr;
            checkbox.id = 'attr-' + attr;

            const label = document.createElement('label');
            label.className = 'form-label';
            label.htmlFor = 'attr-' + attr;
            label.appendChild(document.createTextNode(attr));

            div.appendChild(checkbox);
            div.appendChild(label);

            attributeContainer.appendChild(div);
          });
        })
        .catch(error => console.error('Error:', error));
    }
  }
  </script>
</body>

</html>