<?php
  include_once "auth.php";
  include_once "config.php";

  $mysqli = new mysqli("localhost", $dbUser, $dbPass, $dbName);
  if ($mysqli->connect_error) die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Primary styling curtosy of Bootstrap using Bootswatch's theme: SpaceLab -->
  <link rel="stylesheet" type="text/css" href="Assets/css/bootstrap.min.css">

  <?php

    $search = (isset($_GET['search'])) ? $_GET['search'] : '';
    $display = (isset($_GET['display'])) ? $_GET['display'] : '';
    $page = (isset($_GET['page'])) ? $_GET['page'] : '1';


    // Delete a book if requested
    if (isset($_POST['delete'])) {
      $deleteQuery = "DELETE FROM books WHERE Id={$_POST['delete']};";
      $mysqli->query($deleteQuery);
    }
  ?>
  <style>
    body { background: linear-gradient(to right,#A3B58E 0%,#C6D6AE 50%,#A3B58E 100%); }
    .embedCodeContainer { overflow: hidden; }
    .embedCode { resize: none; opacity: 0;
                 transform: translateY(-20%); -webkit-transform: translateY(-20%); -moz-transform: translateY(-20%); -ms-transform: translateY(20%); -o-transform: translateY(20%);
                 transition: all 1s; -webkit-transition: all 1s; -moz-transition: all 1s; -ms-transition: all 1s; -o-transition: all 1s; }
    .embedCode.displayed { opacity: 1;
                           transform: translateY(0); -webkit-transform: translateY(0); -moz-transform: translateY(0); -ms-transform: translateY(0); -o-transform: translateY(0); }
    .options .btn { margin: 2px; }
    .clickable:hover { opacity: .9; cursor: pointer; background-color: rgba(200, 200, 200, .35); }
    .no_scroll { overflow: hidden; }
    .blackout { position: fixed; width: 100%; height: 100%; z-index: 100; background-color: rgba(0, 0, 0, .6); left: 0; top: 0; }
    #bookView { margin: 0; position: absolute; top: 50%; left: 50%; margin-right: -50%; height: 80%; width: 80%;
                       transform: translate(-50%, -50%); -webkit-transform: translate(-50%, -50%); -moz-transform: translate(-50%, -50%); -ms-transform: translate(-50%, -50%); -o-transform: translate(-50%, -50%); }
    #bookView.no_center { transform: none; -webkit-transform: none; -moz-transform: none; -ms-transform: none; -o-transform: none; }
  </style>
  <?php echo "<script> var reader = '$reader'; </script>"; ?>
  <script>

    // Display a book from given id on a semi-black backdrop
    function displayBook(id) {
      var blackout = document.createElement("blackout");
      blackout.classList.add("blackout");
      blackout.onclick = unDisplayBook;

      var internal = document.createElement("iframe");
      internal.id = "bookView";
      internal.src = reader + "?bookID=" + id;
      internal.setAttribute('webkitallowfullscreen', 'true');
      internal.setAttribute('allowfullscreen', 'true')
      blackout.appendChild(internal);

      document.body.appendChild(blackout);
      document.body.classList.add("no_scroll");
    }

    // Remove book being displayed & backdrop
    function unDisplayBook() {
      var blackout = document.getElementsByClassName("blackout")[0];
      if (blackout !== null) {
        blackout.parentNode.removeChild(blackout);
        document.body.classList.remove("no_scroll");
      }
    }

    // Make embed elment drop down below the button
    function displayEmbed(elment) {
      var embedElment = elment.parentNode.getElementsByClassName("embedCode")[0];
      if (embedElment.classList.contains("displayed"))
        embedElment.classList.remove("displayed");
      else {
        embedElment.classList.add("displayed");
        selectAllTxt(embedElment);
      }
    }

    // Select all Text in an element
    function selectAllTxt(elment) {
      elment.focus();
      elment.select();
    }

    // Set a book of given Id to be deleted then reload page after confirming that they want to delete the book
    function deleteBook(id, title) {
      if (confirm("Are you sure you would like to delete '" + title + "'?")) {
        var form = document.createElement('form');
        form.setAttribute("method", "post");
        form.setAttribute("action", "archive.php");

        var deleteVal = document.createElement('input');
        deleteVal.setAttribute('type', 'hidden');
        deleteVal.setAttribute('name', 'delete');
        deleteVal.setAttribute('value', id);

        form.appendChild(deleteVal);
        document.body.appendChild(form);
        form.submit();
      }
    }

    // Remove transformations when iframe is set to fullscreen that would offset the fullscreen view
    function allowFullScreen() {
      var bookView = document.getElementById("bookView");
      if (bookView) {
        if (bookView.classList.contains("no_center"))
          bookView.classList.remove("no_center");
        else
          bookView.classList.add("no_center")
      }
    }
    document.addEventListener("fullscreenchange", allowFullScreen, false);
    document.addEventListener("webkitfullscreenchange", allowFullScreen, false);
    document.addEventListener("mozfullscreenchange", allowFullScreen, false);

  </script>
</head>
<body>

  <!-- Navbar -->
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
          <li class="active">
            <a href="archive.php">Archive</a>
          </li>
          <?php } if ($userAdminAccess) { ?>
          <li>
            <a href="admin.php">Admin</a>
          </li>
          <?php } ?>
        <li><a href="https://libmanuals.wheaton.edu/node/561">Help</a></li>

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
    <div id="content" class="col-sm-10 col-sm-offset-1">
      <h1>Digital Book Archive</h1>
      <!-- Search Bar -->
      <form action='archive.php' role="form" class="form-inline" method='get' id='search'>
        <input type='text' class="form-control" class="" name='search' size=40 <?php if ($search) echo "value='$search'";?>>
        <input type='submit' class="btn btn-default btn-sm" name='submit' value='Search'>
        <a href='archive.php'><button class="btn btn-default btn-sm" type='button'>Clear</button></a>
      </form>

      <?php

        // Get information about stored books
        $query = "SELECT * FROM books";
        $countQuery = "SELECT Count(*) AS count FROM books";
        if ($search) {
          $search = strtolower($search);
          $query .= " WHERE Id LIKE '$search' OR LOWER(Title) LIKE '%$search%' OR LOWER(Author) LIKE '%$search%'";
          $countQuery .= " WHERE Id LIKE '$search' OR LOWER(Title) LIKE '%$search%' OR LOWER(Author) LIKE '%$search%'";
        }
        $query .= " ORDER BY Id DESC LIMIT 10";
        if ($page) { $query .= " OFFSET " . (($page - 1) * 10);}
        $result = $mysqli->query("$query;");
        $rows = $mysqli->query("$countQuery;");
        $row = $rows->fetch_assoc();
        $totalCount = $row['count'];
        $numPages = intval(ceil($row['count'] / 10));
        if ($numPages > $page + 5) {
          if ($page > 4) {
            $navIndex = $page - 4;
            $navLast = $page + 5;
          } else {
            $navIndex = 1;
            $navLast = min($numPages, 10);
          }
        } else {
          $navIndex = max(1, $numPages - 9);
          $navLast = $numPages;
        }

        // Create Pagination Bars template
        $searchAddon = ($search) ? "&search=$search" : "";
        $paginationBar = "<nav><ul class='pagination'>";
        $paginationBar .= ($page == 1) ?
            "<li class='disabled'><a href='#' aria-label='Previous'><span aria-hidden='true'>&lt</span></a></li>" :
            "<li><a href=archive.php?page=" . ($page - 1) . "$searchAddon aria-label='Previous'><span aria-hidden='true'>&lt</span></a></li>";
        while ($navIndex <= $navLast) {
          $paginationBar .= ($navIndex == $page) ?
            "<li class='active'><a href='#'>$navIndex<span class='sr-only'>(current)</span></a></li>" :
            "<li><a href='archive.php?page=$navIndex$searchAddon'>$navIndex</a></li>";
          $navIndex++;
        }
        $paginationBar .= ($page == $numPages) ?
            "<li class='disabled'><a href='#' aria-label='Next'><span aria-hidden='true'>&gt</span></a></li>" :
            "<li><a href=archive.php?page=" . ($page + 1) . "$searchAddon aria-label='Next'><span aria-hidden='true'>&gt</span></a></li>";
        $paginationBar .= "</ul></nav>";


        if ($result->num_rows > 0) {
          echo $paginationBar;
          echo "<ul id='bookList' class='media-list'>";

          // Display a listing of 10 books
          while ($res = $result->fetch_assoc()) {
            if (strlen($res['Title']) > 30) $res['Title'] = substr($res['Title'], 0, 30) . '...';
            echo "<li class='media'>";
            echo "<div class='col-sm-8 clickable' onclick=\"displayBook({$res['Id']}, '{$res['Title']}')\";>";
            echo "<div class='media-left'><img class='media-object' style='width: 128px;' src='{$booksDir}Images/{$res['Id']}/{$res['Cover']}'></div>";
            echo "<div class='media-body'>";
            echo "<h4 class='media-heading'>{$res['Title']}</h4>";
            if ($res['Author']) echo "<span class='author'>By: {$res['Author']}</span><br>";
            if ($res['Description']) echo "<small class='description'>{$res['Description']}</small><br>";
            echo "</div></div>";
            echo "<div class='col-sm-4 options''>";
            if ($userWriteAccess)
              echo "<a href=\"index.php?edit={$res['Id']}\" class='editLink'><button class='btn btn-default btn-sm' type='button'>Edit</button></a>";
            if ($userWriteAccess)
            echo "<button type='button' class='clickable btn btn-warning btn-sm' onclick=\"deleteBook({$res['Id']}, '{$res['Title']}')\">Delete</button><br/>";
            if ($userReadAccess)
              echo "<button type='button' class='clickable btn btn-primary btn-sm' onclick=\"displayEmbed(this)\">Embed Code</button>";
            echo "<div class='embedCodeContainer'><textarea class='embedCode col-sm-11' rows='5' spellcheck='false'>&ltiframe src='{$res['Handle']}' allowfullscreen webkitallowfullscreen&gt&ltiframe&gt</textarea><div>";
            echo "</div></li><hr>";
          }

          echo "</ul>";
          echo $paginationBar;
        } else if ($search) {
          echo "Your search had no results, sorry";
        } else if ($page == 1) {
          echo "No books stored in the Archive";
        }

      ?>
    </div>
  </div>
</body>
</html>
