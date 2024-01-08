<!-- 
	Main/login page for the pumpkin patch tracking app 
	Access link: https://www.students.cs.ubc.ca/~chenkai/project_a2v5h_e0p8y_y7v1z/pages/login.php
  DIFFERENT FOR SOMEONE ELSE!
	Individuals can login by selecting their role type
-->

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pumpkin Patch Tracker</title>
  <link rel="stylesheet" href="../css/styles.css">
</head>

<body class="bg-gray-100 flex-center">
  <div class="form-card">
    <img src="../images/eat.png" alt="Pumpkin Image" class="login-image">
    <h1 class="text-3xl text-center">Pumpkin Patch Tracker</h1>
    <p class="text-center">Please select your role type to login:</p>
    <form id="role-login-form" method="POST" action="role_redirect.php" class="form-body">
      <div class="form-group">
        <label for="role-type" class="form-label">User Type:</label>
        <select id="role-type" name="role-type" class="form-select">
          <option value="owner">Owner</option>
          <option value="guest">Guest</option>
        </select>
      </div>
      <div class="form-group">
        <input type="submit" value="Login" class="form-submit" id="login-button" name="login-button" />
      </div>
    </form>
  </div>
</body>

</html>