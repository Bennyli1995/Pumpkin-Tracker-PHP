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
    <h1 class="text-3xl text-center">Pumpkin Patch Management For Guests</h1>
    <nav class="navbar">
      <button class="text-lg" onclick="window.location.href='search_records_guests.php';">View Pumpkin Patch Projections</button>
      <button class="text-lg" onclick="window.location.href='search_map_region_guests.php';">Find Patches by Minimum Map Region Size</button>
      <button class="text-lg" onclick="window.location.href='count_events_guests.php';">Count Special Events</button>
      <button class="text-lg" onclick="window.location.href='search_activities_guests.php';">Find Patches by Minimum Activities</button>
      <button class="text-lg" onclick="window.location.href='average_activity_fee.php';">View Average Activity Fees</button>
      <button class="text-lg" onclick="window.location.href='search_variety_guests.php';">View Patches Planting All Varieties</button>
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