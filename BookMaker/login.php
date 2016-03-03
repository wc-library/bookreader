<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <?php
    include "config.php";

    session_start();

    $mysqli = new mysqli("localhost", $dbUser, $dbPass, $dbName);
    if ($mysqli->connect_error) die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);

    // Try to login
    if (isset($_POST["user"]) && isset($_POST["pass"])) {
      try {
        //throw new Exception("Here's an Exception!");
        $user = $_POST["user"];
        $pass = $_POST["pass"];

        $connection = ldap_connect($ldapHost);		// try to make a connection

      	// if a connection could not be made, throw an exception
      	if(!$connection) {
      		throw new Exception(sprintf("Unable to connect to host '%s'.", $ldapHost), 0x5b);
      	}

        $ldapbind = ldap_bind($connection, $ldapBindUser, $ldapBindPass);

  			if(!$ldapbind) {
          error_log("Error binding to host $ldapHost using [User, Pass]: [$ldapBindUser, $ldapBindPass]");
          throw new Exception(@ldap_error($connection), @ldap_errno($connection));
        }

        // search the Active Directory for username
      	$result = @ldap_search($connection, $ldapBaseDN, "cn=" . $user);

      	// if the search fails, throw an exception
      	if(!$result) {
      		throw new Exception(@ldap_error($connection), @ldap_errno($connection));
      	}

      	// get the first (and hopefully, only) entry in the results
      	$entry = @ldap_first_entry($connection, $result);

      	@ldap_free_result($result);		// free up the memory used by the result

      	// if there are no entries, throw an exception
      	if(!$entry) {
      		throw new Exception("Invalid Username");
      	}

      	// get the display for associated with the username
      	$displaynames = @ldap_get_values($connection, $entry, "displayName");

      	// if the display name is not set, throw an exception
      	if($displaynames)
          $displayname = $displaynames[0];    // use the first entry only

        // get the cn associated with the username
        $cns = @ldap_get_values($connection, $entry, "cn");

        // if the cn is not set, throw an exception
        if(!$cns) {
      		throw new Exception(@ldap_error($connection), @ldap_errno($connection));
        }

        $cn = $cns[0];			// use the first entry only

      	$dn = @ldap_get_dn($connection, $entry);		// get the DN of the entry

      	// if there was a problem getting the DN, throw an exception
      	if(!$dn) {
      		throw new Exception(@ldap_error($connection), @ldap_errno($connection));
      	}

      	// try to bind the username to the current session and if the
      	// the username could not be bound to the current session
      	// throw an exception
      	if(!@ldap_bind($connection, $dn, $pass)) {
      		throw new Exception("Invalid Password", @ldap_errno($connection));
      	}

        // Check for authorization
        $authQuery = "SELECT * FROM users LEFT JOIN groups ON users.GROUP_ID=groups.Id WHERE cn='$cn';";
        $res = $mysqli->query($authQuery);
        if ($res->num_rows != 0) {
          $result = $res->fetch_array(MYSQLI_ASSOC);

          $_SESSION["cn"] = $cn;
          if (isset($displayname))
            $_SESSION["displayname"] = $displayname;
          $_SESSION["readAccess"] = $result["CanRead"];
          $_SESSION["writeAccess"] = $result["CanWrite"];
          $_SESSION["adminAccess"] = $result["CanAdmin"];

          header("Location: index.php");
        } else {
          throw new Exception("Unauthorized Access Attempt");
        }
      } catch (Exception $e) {
        $exception = $e;
      }
    }
  ?>

  <!-- Primary styling curtosy of Bootstrap using Bootswatch's theme: SpaceLab -->
  <link rel="stylesheet" type="text/css" href="Assets/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="Assets/js/bootstrap.min.js"></script>
  <style>
    body { background: linear-gradient(to right,#A3B58E 0%,#C6D6AE 50%,#A3B58E 100%); }
    #loginContainer{ background-color: #eee; padding: 20px; border-radius: 8px; box-shadow: 2px 2px 8px #888; }
    #loginContainer .instructions { text-align: center; font-size: 22px; }
  </style>
</head>

<body>
  <!-- NavBar -->
  <nav class="navbar navbar-default">
    <div class="container-fluid">
      <div class="navbar-header">
        <a class="navbar-brand" href="">BookMaker</a>
      </div>
    </div>
  </nav>

  <?php if (isset($exception)) { ?>

  <div class="container">
    <div class="alert alert-danger" style="text-align: center;">
      <?php echo "Error: {$exception->getMessage()}"; ?>
    </div>
  </div>

  <?php } ?>

  <!-- Content -->
  <div class="container">
    <div class="row">
      <div class="col-sm-6 col-sm-offset-3">
        <div id="loginContainer">
          <form class="form-horizontal" role="form" action="login.php" method="POST">
            <p class="instructions">Please Enter Credentials</p>
            <div class="form-group">
              <label class="control-label col-sm-2" for="user">Username:</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="user" id="user" required>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="pass">Password:</label>
              <div class="col-sm-10">
                <input type="password" class="form-control" name="pass" id="pass" formenctype=""required>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-10">
                <input class="btn btn-default" type="submit" value="submit" name="submit">
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php
    if (isset($_POST["user"])) {
      echo "<script>";
      echo "  document.getElementById('user').value = '{$_POST['user']}'";
      echo "</script>";
    }
  ?>
</body>
</html>
