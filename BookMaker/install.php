<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Primary styling curtosy of Bootstrap using Bootswatch's theme: SpaceLab -->
  <link rel="stylesheet" type="text/css" href="Assets/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="Assets/js/bootstrap.min.js"></script>

  <?php include "config.php"; ?>
  <?php

    // Find out what already exists in mysql
    $preExisting = array();

    $mysqli = new mysqli("localhost", $dbUser, $dbPass);
    if (!$mysqli->connect_error) {
      if ($mysqli->select_db($dbName)) {

        $preExisting["db"] = $dbName;

        $result = $mysqli->query("SHOW TABLES LIKE 'books';");
        if ($result->num_rows == 1)
          $preExisting["books"] = "books";

        $result = $mysqli->query("SHOW TABLES LIKE 'users';");
        if ($result->num_rows == 1)
          $preExisting["users"] = "users";

        $result = $mysqli->query("SHOW TABLES LIKE 'groups';");
        if ($result->num_rows == 1)
          $preExisting["groups"] = "groups";
      }
    }
  ?>
</head>
<body>
  <div class="container">
    <div class="col-sm-10 col-sm-offset-1">
      <h1>Install BookMaker</h1>
      <?php
        if (!isset($_POST["submit"])) {
      ?>
        <form class="form-horizontal" role="form" action="install.php" method="POST">
          <div class="row">
            <div class="col-sm-11 col-sm-offset-1">
              <br>
              <?php
                if (!empty($preExisting)) {
                  echo "<div class='alert alert-warning'>";
                  echo "Warning(s):";
                  echo "<ul>";
                    echo "<li>Database already exists at '{$preExisting['db']}'.</li>";
                  if (isset($preExisting["books"]) && $preExisting["books"])
                    echo "<li>Table 'books' already exists.</li>";
                  if (isset($preExisting["groups"]) && $preExisting["groups"])
                    echo "<li>Table 'groups' already exists.</li>";
                  if (isset($preExisting["users"]) && $preExisting["users"])
                    echo "<li>Table 'users' already exists.</li>";
                  echo "</ul>";
                  echo "<br>";
                  echo "<em>Existing tables will <span style='text-decoration: underline'>not</span> be overwritten</em>";
                  echo "</div>";
                }
              ?>
              <p>Update All Config Entries</p>
              <ul>
                <li>Reader Path: A path to the directory containing the Wheaton Reader</li>
                <li>Delimiter: Delimiter used for separating info in page file names</li>
                <li>SQL Database: Database to be created (or used if it already exists)</li>
                <li>SQL Username: Username for MySQL access</li>
                <li>SQL Password: Password for MySQL access</li>
                <li>Admin CN: The cn of the <em>first</em> user to be have admin priviledges</li>
              </ul>
              <br>
            </div>
          </div>
          <div class="row">
            <div class="form-group">
              <label class="control-label col-sm-2" for="delimiter">Delimiter:</label>
              <div class="col-sm-6">
                <input type="text" class="form-control" name="delimiter" id="delimiter" value=<?php echo $delimiter; ?> required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="form-group">
              <label class="control-label col-sm-2" for="dbName">SQL Database:</label>
              <div class="col-sm-6">
                <input type="text" class="form-control" name="dbName" id="dbName" value=<?php echo $dbName ?> required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="form-group">
              <label class="control-label col-sm-2" for="dbUser">SQL Username:</label>
              <div class="col-sm-6">
                <input type="text" class="form-control" name="dbUser" id="dbUser" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="form-group">
              <label class="control-label col-sm-2" for="dbPass">SQL Password:</label>
              <div class="col-sm-6">
                <input type="password" class="form-control" name="dbPass" id="dbPass" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="form-group">
              <label class="control-label col-sm-2" for="adminCN">Admin CN:</label>
              <div class="col-sm-6">
                <input type="text" class="form-control" name="adminCN" id="adminCN" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="form-group">
              <label class="control-label col-sm-2" for="ldapHost">Ldap Host:</label>
              <div class="col-sm-6">
                <input type="text" class="form-control" name="ldapHost" id="ldapHost" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="form-group">
              <label class="control-label col-sm-2" for="ldapBaseDN">Ldap Base Domain:</label>
              <div class="col-sm-6">
                <input type="text" class="form-control" name="ldapBaseDN" id="ldapBaseDN" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="form-group">
              <label class="control-label col-sm-2" for="ldapBindUser">Ldap Bind User:</label>
              <div class="col-sm-6">
                <input type="text" class="form-control" name="ldapBindUser" id="ldapBindUser" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="form-group">
              <label class="control-label col-sm-2" for="ldapBindPass">Ldap Bind Pass:</label>
              <div class="col-sm-6">
                <input type="text" class="form-control" name="ldapBindPass" id="ldapBindPass" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-1 col-sm-offset-7">
              <input type="submit" class="btn btn-default" name="submit" value="submit">
            </div>
          </div>
        </form>
      <?php
        } else {

          function absolutePath($filename) {
            // $filename is already absolute
            if ($filename[0] == "/") return $filename;

            // $filename is relative
            $absPath = dirname(__FILE__);
            while (substr($filename, 0, 3) == "../") {
              $filename = substr($filename, 3);
              $absPath = substr($absPath, 0, strrpos($absPath, "/"));
            }
            $absPath .= "/" . $filename;

            return $absPath;
          }

          $writingFiles = ["config.json", "tmp/", $_POST["readerPath"] . "Books/Images/", $_POST["readerPath"] . "Books/JSON/"];
          $notWritable = [];
          foreach ($writingFiles as $file)
            if (!is_writable($file))
              array_push($notWritable, absolutePath($file));

          if (count($notWritable) > 0) {
          ?>
            <div class="col-sm-offset-1">
              <p>Specified file(s) must be writable by the apache client</p>
              <ul>
              <?php
                foreach ($notWritable as $file)
                  echo "<li>" . $file . "</li>";
              ?>
              </ul>
            </div>
          <?php
          } else {
            $json = [];
            foreach ($_POST as $key => $val)
              if ($key !== "submit" && $key !== "adminCN")
                $json[$key] = $val;

            $mysqli = new mysqli("localhost", $json["dbUser"], $json["dbPass"]);
            if ($mysqli->connect_error) die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);

            $fp = fopen("config.json", "w");
            fwrite($fp, json_encode($json));
            fclose($fp);

            $createDB = "CREATE DATABASE IF NOT EXISTS " . $json["dbName"] . ";";
            $mysqli->query($createDB);
            $mysqli->select_db($json["dbName"]);

            $createBooksTable = join("", [
              "CREATE TABLE IF NOT EXISTS books",
              "(",
                "Id INTEGER PRIMARY KEY AUTO_INCREMENT,",
                "Title VARCHAR(255),",
                "Author VARCHAR(255),",
                "Description VARCHAR(255),",
                "Width INTEGER,",
                "Height INTEGER,",
                "FirstLeft TINYINT,",
                "Cover VARCHAR(255)",
              ");"
            ]);
            $createGroupsTable = join("", [
              "CREATE TABLE IF NOT EXISTS groups",
              "(",
                "Id INTEGER PRIMARY KEY,",
                "Name VARCHAR(255),",
                "CanRead TINYINT,",
                "CanWrite TINYINT,",
                "CanAdmin TINYINT",
              ");"
            ]);
            $createUsersTable = join("", [
              "CREATE TABLE IF NOT EXISTS users",
              "(",
                "Id INTEGER PRIMARY KEY AUTO_INCREMENT,",
                "cn VARCHAR(255),",
                "Group_Id INTEGER,",
                "FOREIGN KEY (Group_Id) REFERENCES groups(Id)",
              ");"
            ]);

            $insertGroups = join("", [
              "INSERT INTO groups",
                "(Id, Name, CanRead, CanWrite, CanAdmin)",
              "VALUES",
                "(0, 'Admin', 1, 1, 1),",
                "(1, 'Write', 1, 1, 0),",
                "(2, 'Read', 1, 0, 0);"
            ]);

            $insertUser = join("", [
              "INSERT INTO users",
                "(cn, Group_Id)",
              "VALUES",
                "('{$_POST['adminCN']}', 0);"
            ]);

            // Create Books Table if it doesn't exist
            if ($mysqli->query("SHOW TABLES LIKE 'books';")->num_rows == 0) {
              $mysqli->query($createBooksTable);
            }

            // Create and fill Groups Table if it doesn't exist
            if ($mysqli->query("SHOW TABLES LIKE 'groups';")->num_rows == 0) {
              $mysqli->query($createGroupsTable);
              $mysqli->query($insertGroups);
            }

            // Create and fill Users Table if it doesn't exist
            if ($mysqli->query("SHOW TABLES LIKE 'users';")->num_rows == 0) {
              $mysqli->query($createUsersTable);
              $mysqli->query($insertUser);
            }
            ?>
              <div class="col-sm-offset-1">
                <p>BookMaker Succesfully Installed</p>
                <a class="btn btn-default" href="index.php">Continue to BookMaker</a>
              </div>
            <?php
          }
        }
      ?>
    </div>
  </div>
</body>
</html>
