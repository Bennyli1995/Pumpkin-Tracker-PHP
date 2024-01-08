<!--
    Guest redirect page following the login of a guest
    redirects to the appropriate view based on role type
-->

<?php
    
    if (isset($_POST["login-button"])) { 
        userRedirect();
    } else { 
        invalidUserError();
    }

    function userRedirect() {
        $input_value =  $_POST["role-type"];
        if ($input_value == "owner") { 
            header("Location: owner_search.php");
            exit;
        } else if ($input_value == "guest") {
            header("Location: guest_search.php");
            exit;
        } else {
            invalidUserError();
        }
    }

    function invalidUserError() { 
        echo "Invalid role error"; 
        header("Location: login.php");
        exit;
    }
?>