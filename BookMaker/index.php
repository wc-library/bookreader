<?php
  include_once "auth.php";
  include_once "config.php";
  if (!$userWriteAccess)
    header("Location: archive.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Primary styling curtosy of Bootstrap using Bootswatch's theme: SpaceLab -->
  <link rel="stylesheet" type="text/css" href="Assets/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="Assets/js/bootstrap.min.js"></script>
  <!-- Upload button styling curstosy of 'http://www.abeautifulsite.net/whipping-file-inputs-into-shape-with-bootstrap-3/' -->
  <style>
    .btn-file { position: relative; overflow: hidden; }
    .btn-file input[type=file] { position: absolute; top: 0; right: 0; min-width: 100%; min-height: 100%; font-size: 100px; text-align: right; filter: alpha(opacity=0); opacity: 0; outline: none; background: white; cursor: inherit; display: block; }
  </style>

  <style>
    body { background: linear-gradient(to right,#A3B58E 0%,#C6D6AE 50%,#A3B58E 100%); }
  </style>

  <?php
    $action = (isset($_GET["edit"])) ? "edit" : "create";
    // If editing a book, get information on book from MySqli DB
    if (isset($_GET["edit"])) {
      $id = $_GET["edit"];

      $mysqli = new mysqli("localhost", $dbUser, $dbPass, $dbName);
      if ($mysqli->connect_error) die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);

      $query = "SELECT * FROM books WHERE Id=$id;";
      $res = $mysqli->query($query);
      if ($res->num_rows > 0)
        $info = $res->fetch_assoc();
    }
  ?>
</head>

<body>
  <!-- NavBar -->
  <nav class="navbar navbar-default">
    <div class="container-fluid">
      <div class="navbar-header">
        <a class="navbar-brand" href="index.php">BookMaker</a>
      </div>
      <div>
        <ul class="nav navbar-nav">
          <?php if ($userWriteAccess) { ?>
          <li <?php if ($action == "create") echo "class='active'"; ?>>
            <a href="index.php">Creator</a>
          </li>
          <?php } if ($userReadAccess) { ?>
          <li>
            <a href="archive.php">Archive</a>
          </li>
          <?php } if ($userAdminAccess) { ?>
          <li>
            <a href="admin.php">Admin</a>
          </li>
          <?php } ?>
        </ul>
        <ul class="nav navbar-nav pull-right">
          <li>
            <p class="navbar-text"><?php echo $userDisplayName; ?></p>
          </li>
          <li class="divider-vertical" style="min-height: 50px; height: 100%; margin: 0 9px; border-left: 1px solid #f2f2f2; border-right: 1px solid #ffffff;"></li>
          <li>
            <a href="auth.php?logout">Sign Out</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>



  <!-- Content -->
  <div class="container">
      <div class="col-sm-10 col-sm-offset-1">
        <h1>Digital Book <?php echo ($action == "create") ? "Creator" : "Editor";?></h1>
        <form class="form-horizontal" id="form" role="form" onsubmit="return submitForm(this)">
          <div class="form-group">
            <label class="control-label col-sm-2" for="title">Title:</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" name="title" id="title" <?php if (isset($info) && $info["Title"]) echo "value='" . $info['Title'] ."'"; ?> required>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="author">Author:</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" name="author" id="author" <?php if (isset($info) && $info["Author"]) echo "value='" . $info['Author'] ."'"; ?>>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="description">Description:</label>
            <div class="col-sm-10">
              <textarea form="form" class="form-control" name="description" id="description" rows="6"><?php if (isset($info) && $info["Description"]) echo $info['Description']; ?></textarea>
            </div>
          </div>

          <hr>

          <!-- If creating, add an upload element -->
          <?php if (!isset($info)) { ?>
            <div class="form-group">
              <label class='control-label col-sm-2' for='zipUpload'>Upload: </label>
              <div class="input-group col-sm-5">
                <span class="input-group-btn">
                  <span class="btn btn-default btn-file">
                    Browse&hellip;<input type="file" class="file-upload" name="zipUpload" id="zipUpload" required>
                  </span>
                </span>
                <input type="text" class="form-control" readonly>
              </div>
            </div>
          <!-- If editing, add elements for each value to be passed along -->
          <?php } else { ?>
            <input type='hidden' name='id' value=<?php echo $id; ?>>
            <input type='hidden' name='first_left' value=<?php echo $info['FirstLeft']; ?>>
            <input type='hidden' name='cover' value=<?php echo $info['Cover']; ?>>
          <?php } ?>

          <div style="float: right;">
            <input type='submit' id='submit' class="btn btn-lg btn-default" value='continue'>
          </div>
          <input type='hidden' name='action' id='action' value=<?php echo "$action"; ?>>
          <input type='hidden' name='step' id='step' value='1'>
        </form>
      </div>

    </div>
  </div>

  <script>

    // Foreach .file-upload, once a file is uploaded, update the input element nearby to display the name uploaded
    var fUploads = document.getElementsByClassName("file-upload");
    for (var i = 0; i < fUploads.length; i++)
      fUploads[i].onchange = function(evt) {
        var upFile = (evt.currentTarget.files[0] === undefined) ? "" : evt.currentTarget.files[0].name;
        evt.currentTarget.parentNode.parentNode.parentNode.getElementsByTagName("input")[1].value = upFile;
      };

    // Submit form using Ajax & move on to confirm.php once it has run
    function submitForm(form) {
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange = function() { if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

          var results = JSON.parse(xmlhttp.responseText);

          var form = document.createElement("form");
          form.action = "confirm.php"
          form.method = "POST";
          var json = document.createElement("input");
          json.type = "hidden";
          json.name = "json";
          json.value = JSON.stringify(results);
          form.appendChild(json);
          document.body.appendChild(form);
          form.submit();

          for (var i in form.elements) form.elements[i].disabled = false;

      }};
      xmlhttp.open("POST", "process.php", true);
      xmlhttp.send(new FormData(form));

      // Disable all elements to prevent 'double-submission'
      for (var i in form.elements) form.elements[i].disabled = true;

      // Disable reloading of page by cancelling default submission
      return false;
    }
  </script>
</body>
</html>
