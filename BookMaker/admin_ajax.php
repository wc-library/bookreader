<?php
  include "config.php";

  function returnSuccess($data, $contentType = 'text/html') {
    header('Content-Type: ' . $contentType);
    die($data);
  }

  function returnError($err) {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: text/html');
    die($err);
  }



  $mysqli = new mysqli("localhost", $dbUser, $dbPass, $dbName);
  if ($mysqli->connect_error) returnError('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);

  $functions = [

      // Assertion: Old Group_Id is different from new Group_Id
      "modifyUserAccess" => function($mysqli) {

        if (!isset($_POST["Uid"]))
          returnError("No Uid Specified");
        elseif (!isset($_POST["Gid"]))
          returnError("No Gid Specified");

        $uid = $_POST["Uid"];
        $gid = $_POST["Gid"];

        $modifyQuery = "UPDATE users SET Group_Id=$gid WHERE Id=$uid;";


        $mysqli->query($modifyQuery);
        $rows = mysqli_affected_rows($mysqli);

        if ($rows == 0)
          returnError("Error Trying to Update User with Id $uid to Gid $gid");
      },

      "deleteUser" => function($mysqli) {

        if (!isset($_POST["Uid"]))
          returnError("No Uid Specified");

        $uid = $_POST["Uid"];

        $deleteQuery = "DELETE FROM users WHERE Id=$uid;";

        $mysqli->query($deleteQuery);
        $rows = mysqli_affected_rows($mysqli);

        if ($rows == 0)
          returnError("Error Trying to Delete User with Id $uid");

      },

      "addUser" => function($mysqli) {

        if (!isset($_POST["cn"]))
          returnError("No cn Specified");
        elseif (!isset($_POST["Gid"]))
          returnError("No Gid Specified");

        $cn = $_POST["cn"];
        $gid = $_POST["Gid"];

        $createQuery = "INSERT INTO users (cn, Group_Id) VALUES ('$cn', $gid);";

        $mysqli->query($createQuery);
        $rows = mysqli_affected_rows($mysqli);

        if ($rows == 0)
          returnError("Error Trying to Create User with Cn $cn");

      },

      "retrieveHandles" => function($mysqli) {

        $handlesQuery = "SELECT Id, Handle, Title FROM books";
        $rows = array();

        $result = $mysqli->query($handlesQuery);
        while ($res = $result->fetch_array(MYSQLI_ASSOC))
          array_push($rows, $res);

        returnSuccess(json_encode($rows), 'application/json');

      },

      "updateHandle" => function($mysqli) {

        global $handleGenerator, $reader;

        function combinePaths($path1, $path2) {
          if ($path2 == "") return $path1;

          $path1 = explode("/", $path1);
          $path2 = explode("/", $path2);

          while (count($path2) > 0) {
            if ($path2[0] == "..")
              array_pop($path1);
            elseif (!($path2[0] == "." || $path2[0] == ""))
              array_push($path1, $path2[0]);

            array_shift($path2);
          }

          return implode("/", $path1);
        }

        if (!isset($_POST["Id"]))
          returnError("No Id Specified");
        elseif (!isset($_POST["Handle"]))
          returnError("No Handle Specified");

        $id = $_POST["Id"];
        $handle = $_POST["Handle"];

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $currLoc = "{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
        $bookreader = $protocol . combinePaths(substr($currLoc, 0, strrpos($currLoc, "/")), $reader);
        $bookPath = $bookreader . "?bookID=$id";

        $handleUpdate = $handleGenerator . "?Action=Modify&Handle=$handle&Url=$bookPath";

        $retHandle = file_get_contents($handleUpdate);
        if (!$handle || strpos($handle, "Error") !== False) {
          returnError("random error");
        } else {
          returnSuccess($bookPath);
        }
      }
  ];

  if (isset($_POST["Action"]) && array_key_exists($_POST["Action"], $functions))
    $functions[$_POST["Action"]]($mysqli);
  else
    returnError("Invalid Action");
