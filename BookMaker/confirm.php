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

  <?php

    // Move post variables into a javascript array 'post'
    $json = json_decode($_POST['json'], true);
    $action = $json['action'];
    if ($action == "edit") {
      $id = $json['id'];

      $fname = $booksDir . "JSON/" . $id . ".json";
      $hand = fopen($fname, "r");
      $pages = fread($hand, filesize($fname));
      fclose($hand);

      $imgDir = $booksDir . "Images/" . $id . "/";
    }
    $title = $json['title'];
    $author = $json['author'];

    echo  "<script>";
    echo    "var post = {$_POST['json']};";
    if (isset($pages))
      echo  "post['pages'] = $pages;";
    if (isset($imgDir))
      echo  "post['imgDir'] = '$imgDir';";
    echo  "</script>";
  ?>

  <style>
    body { background: linear-gradient(to right,#A3B58E 0%,#C6D6AE 50%,#A3B58E 100%); }
    .clickable:hover { cursor: pointer; opacity: .8 }
    #form { color: #444; font-size: 110%; }
    #left_form { color: #111; }
    #title_div { margin-top: 40px; }
    #first_pos_div { margin-top:30px; }
    #form #continue { position: absolute; top: 50%; right: 12%; transform: translateY(-50%); -webkit-transform: translateY(-50%); -moz-transform: translateY(-50%); -ms-transform: translateY(-50%); -o-transform: translateY(-50%); }
    #page_placement_div { text-align: center; }
    .coverButton { background: #fbb917; padding: 3px; border-radius: 6px; color: #555;opacity: .8; }
    #gallery_instructions { text-align: center; margin-top: 20px; margin-bottom: 26px; font-size: 120%; color: #222; }
    #gallery .coverButton { position: absolute; opacity: .1; }
    #gallery .coverButton:hover { cursor: pointer; opacity: .8; }
    #gallery .coverButton.coverButton-selected { opacity: .8; }
    #gallery .coverButton.coverButton-selected { cursor: default; }
    div.cat-header {font-size: 90%; text-align: right; }
    #blackout { position: fixed; width: 100%; height: 100%; top: 0; left: 0; z-index: 2; background-color: rgba(0, 0, 0, .1); }
    #blackout .image { height: 80%; position: absolute; left: 0; right: 0; margin-left: auto; margin-right: auto; top: 50%; transform: translateY(-50%); -ms-transform: translateY(-50%); -webkit-transform: translateY(-50%); -o-transform: translateY(-50%); -moz-transform: translateY(-50%); }
    #blackoutLoading { position: absolute; width: 10%; left: 50%; top: 30%; transform: translateX(-50%); }
  </style>
  <script>
    function displayImg() {
      var blackout = document.createElement("div");
      blackout.id = "blackout";

      var img = document.createElement("img");
      img.src = this.src;
      img.classList.add("image");
      blackout.appendChild(img);
      document.body.appendChild(blackout);

      document.body.style.overflow = "hidden";
      blackout.onclick = function() {
        document.body.style.overflow = "";
        document.body.removeChild(this);
      };
    }
  </script>
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
        <li>
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
    <div class="row">
      <div class="col-sm-10 col-sm-offset-1">

        <h1>Finalize Book <?php echo ($action == "create") ? "Creation" : "Edit";?></h1>
        <form id="form" role="form" onsubmit="return submitForm(this)">

          <!-- Left Form: Info -->
          <div id="left_form" class="col-sm-4">
            <div id="title_div" class="row">
              <div class="col-sm-4 cat-header">Title:</div>
              <div class="col-sm-8 cat-info"></div>
            </div>
            <br>
            <div id="author_div" class="row">
              <div class="col-sm-4 cat-header">Author:</div>
              <div class="col-sm-8 cat-info"></div>
            </div>
            <br>
            <div id="first_pos_div" class="row">
                <div class="col-sm-4 cat-header">First Page Placement:</div>
                <div class="btn-group col-sm-8" data-toggle="buttons">
                  <label class="btn btn-default">
                      <input type="radio" id="first_pos_left" name="first_left" value="1">Left
                  </label>
                  <label class="btn btn-default">
                      <input type="radio" id="first_pos_right" name="first_left" value="0"> Right
                  </label>
                </div>
            </div>
          </div>

          <!-- Mid Form: Cover Image -->
          <div class="col-sm-4">
            <img id="coverDisplay" class="col-sm-8 col-sm-offset-2 clickable">
          </div>

          <!-- Right Form: Submit Button -->
          <input class="btn btn-lg btn-primary" id="continue" type="submit" value='<?php echo ($action == "create") ? "Create Book" : "Save Edit"; ?>'>

        </form>
      </div>
    </div>
    <hr>
    <div class="row">

      <!-- Gallery -->
      <div class="col-sm-10 col-sm-offset-1" id="gallery">
        <p id="gallery_instructions">Update Book Page Names & Select Cover Photo</p>
      </div>
    </div>
  </div>

  <script>

    // Load in info from post & update various elements with correct initial information
    var pages = post['pages'];
    var imgDir = post['imgDir'] || "tmp/";
    document.getElementById("coverDisplay").src = imgDir + post['cover'];
    var selectedFirstLeft = document.getElementById((parseInt(post["first_left"])) ? "first_pos_left" : "first_pos_right");
    selectedFirstLeft.checked = "checked";
    selectedFirstLeft.parentNode.classList.add("active");
    document.getElementById("title_div").getElementsByClassName("cat-info")[0].innerHTML = post['title'];
    if (post['author']) document.getElementById("author_div").getElementsByClassName("cat-info")[0].innerHTML = post['author'];
    else document.getElementById("author_div").style.display = "none";

    // Number of imgs displayed per row
    var perRow = 4;
    // Display all images uploaded in the gallery div
    for (var i = 0; i < pages.length / perRow; i++) {
      var row = document.createElement("div");
      row.classList.add("row");
      row.style.marginBottom = "20px";

      document.getElementById("gallery").appendChild(row);
      for (var j = i * perRow; (j < (i + 1) * perRow) && (j < pages.length); j++) {
        var item = document.createElement("div");
        item.classList.add("col-sm-" + (12 / perRow));


        var img = document.createElement("img");
        if (pages[j]["filename"] == "Blank.jpg") {
          img.src = imgDir + pages[0]["filename"];
          img.style.visibility = "hidden";
        } else {
          var coverButton = document.createElement("div");
          coverButton.classList.add("coverButton", "glyphicon", "glyphicon-check");
          if (pages[j]["filename"] == post["cover"]) coverButton.classList.add("coverButton-selected");
          coverButton.onclick = function(evt) { setCover(evt.currentTarget.parentNode);};
          item.appendChild(coverButton);

          img.src = imgDir + pages[j]["filename"];
          img.classList.add("clickable");
        }
        img.classList.add("col-sm-10", "col-sm-offset-1");

        var txt = document.createElement("input");
        txt.type = "text";
        txt.name = "page" + j;
        txt.style.textAlign = "center";
        txt.value = pages[j]["semantic"];
        txt.classList.add("form-control");

        item.appendChild(img);
        item.appendChild(txt);
        row.appendChild(item);
      }
    }

    // Change the cover image displayed as well as updating the stored variable
    function setCover(item) {
      document.getElementsByClassName("coverButton-selected")[0].classList.remove("coverButton-selected");
      item.getElementsByClassName("coverButton")[0].classList.add("coverButton-selected");
      var newSrc = item.getElementsByTagName("img")[0].src;
      document.getElementById("coverDisplay").src = newSrc;
      document.getElementById("form_cover").value = newSrc.substr(newSrc.lastIndexOf("/") + 1);
    }

    // Display a loading gif
    function loadingScreen() {
      var blackout = $( document.createElement("div") )
        .attr("id", "blackout");

      var loading = $( document.createElement("img") )
        .attr("id", "blackoutLoading")
        .attr("src", "Assets/processing.gif");

      $( document.body ).append(blackout.append(loading));
    }

    // Remove the loading gif
    function loadingScreenClear() {
      $( "#blackout" ).remove();
    }

    // Create a hidden form element with given name and val
    function createFormElment(name, val) {
      var input = document.createElement("input");
      input.type = "hidden";
      input.id = "form_" + name;
      input.name = name;
      input.value = val;
      return input;
    }

    // Hidden submission form
    var form = document.getElementById('form');
    form.appendChild(createFormElment("action", post['action']));
    form.appendChild(createFormElment("author", post['author']));
    form.appendChild(createFormElment("cover", post['cover']));
    form.appendChild(createFormElment("desc", post['desc']));
    form.appendChild(createFormElment("height", post['height']));
    form.appendChild(createFormElment("width", post['width']));
    form.appendChild(createFormElment("id", post['id']));
    form.appendChild(createFormElment("title", post['title']));
    form.appendChild(createFormElment("step", "2"));

    // Submit form to process.php & move on to archive once finished
    function submitForm(form) {
      var gInputs = document.getElementById("gallery").getElementsByTagName("input");
      for (var i = 0; i < gInputs.length; i++) {
        if (gInputs[i].type === "text" && gInputs[i].name.indexOf("page") === 0)
          pages[parseInt(gInputs[i].name.substr(4))].semantic = gInputs[i].value;
      }
      form.appendChild(createFormElment("pages", JSON.stringify(pages)));

      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange = function() { if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

        loadingScreenClear();
        if (xmlhttp.responseText.indexOf("error") == -1)
          document.location.href = "archive.php?search=" + xmlhttp.responseText + "&display=" + xmlhttp.responseText;
        else
          alert(xmlhttp.responseText);

        //validation?
      }};
      xmlhttp.open("POST", "process.php", true);
      xmlhttp.send(new FormData(form));
      loadingScreen();

      return false;
    }

    // Set up all clickables to be clickable
    var clickables = document.getElementsByClassName("clickable");
    for (var i = 0; i < clickables.length; i++) {
        clickables[i].onclick = displayImg;
    }
  </script>
</body>
</html>
