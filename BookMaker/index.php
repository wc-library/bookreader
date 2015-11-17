<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Primary styling curtosy of Bootstrap using Bootswatch's theme: SpaceLab -->
  <link rel="stylesheet" type="text/css" href="Assets/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="Assets/js/bootstrap.min.js"></script>
  <!-- Upload styling curstosy of 'http://www.abeautifulsite.net/whipping-file-inputs-into-shape-with-bootstrap-3/' -->
  <style>
    .btn-file { position: relative; overflow: hidden; }
    .btn-file input[type=file] { position: absolute; top: 0; right: 0; min-width: 100%; min-height: 100%; font-size: 100px; text-align: right; filter: alpha(opacity=0); opacity: 0; outline: none; background: white; cursor: inherit; display: block; }
  </style>
  <style>
    body { background: linear-gradient(to right,#A3B58E 0%,#C6D6AE 50%,#A3B58E 100%); }
  </style>
  <?php
    $action = ($_GET["edit"]) ? "edit" : "create";
    if ($_GET["edit"]) {
      include "config.php";
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
  <nav class="navbar navbar-default">
    <div class="container-fluid">
      <div class="navbar-header">
        <a class="navbar-brand" href="index.php">BookMaker</a>
      </div>
      <div>
        <ul class="nav navbar-nav">
          <li <?php if ($action == "create") echo "class='active'"; ?>>
            <a href="index.php">Creator</a>
          </li>
          <li><a href="archive.php">Archive</a></li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="container">
      <div class="col-sm-10 col-sm-offset-1">
        <h1>Digital Book <?php echo ($action == "create") ? "Creator" : "Editor";?></h1>
        <form class="form-horizontal" id="form" role="form" onsubmit="return submitForm(this)">
          <div class="form-group">
            <label class="control-label col-sm-2" for="title">Title:</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" name="title" id="title" <?php if ($info && $info["Title"]) echo "value='" . $info['Title'] ."'"; ?> required>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="author">Author:</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" name="author" id="author" <?php if ($info && $info["Author"]) echo "value='" . $info['Author'] ."'"; ?>>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="description">Description:</label>
            <div class="col-sm-10">
              <textarea form="form" class="form-control" name="description" id="description" rows="6"><?php if ($info && $info["Description"]) echo $info['Description']; ?></textarea>
            </div>
          </div>
          <hr>
          <?php if (!$info) { ?>
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
          <?php } ?>
          <div style="float: right;">
            <input type='submit' id='submit' class="btn btn-lg btn-default" value='continue'>
          </div>
          <input type='hidden' name='action' value=<?php echo "'$action'"; ?> id='action'>
          <?php if ($action == "edit") {
            echo "<input type='hidden' name='id' value=$id>";
            echo "<input type='hidden' name='first_left' value='" . $info['FirstLeft'] . "'>";
            echo "<input type='hidden' name='cover' value='" . $info['Cover'] . "'>";
            echo "<input type='hidden' name='first_left' value='" . $info['FirstLeft'] . "'>";
          } ?>
          <input type='hidden' name='step' value='1' id='step'>
        </form>
      </div>
    </div>
  </div>
  <script>
    var fUploads = document.getElementsByClassName("file-upload");
    for (var i = 0; i < fUploads.length; i++)
      fUploads[i].onchange = function(evt) {
        var upFile = (evt.currentTarget.files[0] === undefined) ? "" : evt.currentTarget.files[0].name;
        evt.currentTarget.parentNode.parentNode.parentNode.getElementsByTagName("input")[1].value = upFile;
      };

    function submitForm(form) {
      // Create and submit form using Ajax
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange = function() { if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

          var results = JSON.parse(xmlhttp.responseText);
          //Validation?

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
