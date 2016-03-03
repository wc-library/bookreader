<?php
  include "config.php";

  $mysqli = new mysqli("localhost", $dbUser, $dbPass, $dbName);
  if ($mysqli->connect_error) die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);

  $functions = [

      // Assertion: Old Group_Id is different from new Group_Id
      "modifyUserAccess" => function($mysqli) {

        if (!isset($_POST["Uid"]))
          die("No Uid Specified");
        elseif (!isset($_POST["Gid"]))
          die("No Gid Specified");

        $uid = $_POST["Uid"];
        $gid = $_POST["Gid"];

        $modifyQuery = "UPDATE users SET Group_Id=$gid WHERE Id=$uid;";


        $mysqli->query($modifyQuery);
        $rows = mysqli_affected_rows($mysqli);

        if ($rows == 0)
          echo "Error Trying to Update User with Id $uid to Gid $gid";
      },

      "deleteUser" => function($mysqli) {

        if (!isset($_POST["Uid"]))
          die("No Uid Specified");

        $uid = $_POST["Uid"];

        $deleteQuery = "DELETE FROM users WHERE Id=$uid;";

        $mysqli->query($deleteQuery);
        $rows = mysqli_affected_rows($mysqli);

        if ($rows == 0)
          echo "Error Trying to Delete User with Id $uid";

      },

      "addUser" => function($mysqli) {

        if (!isset($_POST["cn"]))
          die("No cn Specified");
        elseif (!isset($_POST["Gid"]))
          die("No Gid Specified");

        $cn = $_POST["cn"];
        $gid = $_POST["Gid"];

        $createQuery = "INSERT INTO users (cn, Group_Id) VALUES ('$cn', $gid);";

        $mysqli->query($createQuery);
        $rows = mysqli_affected_rows($mysqli);

        if ($rows == 0)
          echo "Error Trying to Create User with Cn $cn";

      }
  ];

  if (isset($_POST["Action"]) && array_key_exists($_POST["Action"], $functions))
    $functions[$_POST["Action"]]($mysqli);
  else
    die("Invalid Action");
