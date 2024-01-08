<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pumpkin Patch Management for Owners</title>
  <link rel="stylesheet" href="../css/styles.css">
</head>

<body class="bg-gray-100 flex-center">
  <div class="form-card">
    <button class="back-button" onclick="goBack();">
      <img src="../images/back_button.png" alt="Go Back">
    </button>
    <h1 class="text-3xl text-center">Pumpkin Patch Management For Owners</h1>
    <nav class="navbar">
      <button class="text-lg" onclick="window.location.href='create_activity.php';">Create New Activity</button>
      <button class="text-lg" onclick="window.location.href='search_records.php';">Filter Pumpkin Patch</button>
      <button class="text-lg" onclick="window.location.href='update_records.php';">Update Pumpkin Patch</button>
      <button class="text-lg" onclick="window.location.href='delete_records.php';">Delete Pumpkin Patch</button>
    </nav>
  </div>

  <script>
  const goBack = () => {
    // NEED TO CHANGE DEPENDING ON YOUR OWN LOCAL SERVER!
    // https://www.students.cs.ubc.ca/~chenkai/project_a2v5h_e0p8y_y7v1z/pages/login.php
    // https://www.students.cs.ubc.ca/~cli66/pages/owner_search.php
    // https://www.students.cs.ubc.ca/~hzhou2/project_a2v5h_e0p8y_y7v1z/pages/login.php
    window.location.href =
      'https://www.students.cs.ubc.ca/~chenkai/project_a2v5h_e0p8y_y7v1z/pages/login.php';
  }
  </script>
</body>

</html>