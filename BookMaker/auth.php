<?php
  session_start();
  if (isset($_GET["logout"])) {
    unset($_SESSION["cn"]);
    unset($_SESSION["displayname"]);
    unset($_SESSION["readAccess"]);
    unset($_SESSION["writeAccess"]);
    unset($_SESSION["adminAccess"]);
  }

  if (isset($_SESSION["cn"])) {
    $userCN = $_SESSION["cn"];
    $userReadAccess = $_SESSION["readAccess"];
    $userWriteAccess = $_SESSION["writeAccess"];
    $userAdminAccess = $_SESSION["adminAccess"];

    if (isset($_SESSION["displayname"]))
      $userDisplayName = $_SESSION["displayname"];
    else
      $userDisplayName = $_SESSION["cn"];
  } else
    header("Location: login.php");
