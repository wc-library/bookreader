<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Primary styling curtosy of Bootstrap using Bootswatch's theme: SpaceLab -->
  <link rel="stylesheet" type="text/css" href="Assets/css/bootstrap.min.css">

  <?php
    include 'config.php';

    $mysqli = new mysqli("localhost", $dbUser, $dbPass, $dbName);
    if ($mysqli->connect_error) die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);

    $search = (isset($_GET['search'])) ? $_GET['search'] : '';
    $display = (isset($_GET['display'])) ? $_GET['display'] : '';
    $page = (isset($_GET['page'])) ? $_GET['page'] : '1';

    if ($_POST['delete']) {
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
    .blackout iframe { margin: 0; position: absolute; top: 50%; left: 50%; margin-right: -50%; height: 80%; width: 80%;
                       transform: translate(-50%, -50%); -webkit-transform: translate(-50%, -50%); -moz-transform: translate(-50%, -50%); -ms-transform: translate(-50%, -50%); -o-transform: translate(-50%, -50%); }
  </style>
  <?php echo "<script> var reader = '$reader'; </script>"; ?>
  <script>

    function displayBook(id) {
      var blackout = document.createElement("blackout");
      blackout.classList.add("blackout");
      blackout.onclick = unDisplayBook;

      var internal = document.createElement("iframe");
      internal.src = reader + "?bookID=" + id;
      internal.setAttribute('webkitallowfullscreen', 'true');
      internal.setAttribute('allowfullscreen', 'true')
      blackout.appendChild(internal);

      document.body.appendChild(blackout);
      document.body.classList.add("no_scroll");
    }

    function unDisplayBook() {
      var blackout = document.getElementsByClassName("blackout")[0];
      if (blackout !== null) {
        blackout.parentNode.removeChild(blackout);
        document.body.classList.remove("no_scroll");
      }
    }

    function displayEmbed(elment) {
      var embedElment = elment.parentNode.getElementsByClassName("embedCode")[0];
      if (embedElment.classList.contains("displayed"))
        embedElment.classList.remove("displayed");
      else {
        embedElment.classList.add("displayed");
        selectAllTxt(embedElment);
      }
    }

    function selectAllTxt(elment) {
      elment.focus();
      elment.select();
    }

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

  </script>
</head>
<body>
  <nav class="navbar navbar-default">
    <div class="container-fluid">
      <div class="navbar-header">
        <a class="navbar-brand" href="index.php">BookMaker</a>
      </div>
      <div>
        <ul class="nav navbar-nav">
          <li><a href="index.php">Creator</a></li>
          <li class="active"><a href="archive.php">Archive</a></li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="container">
  <div id="content" class="col-sm-10 col-sm-offset-1">
    <h1>Digital Book Archive</h1>
    <form action='archive.php' role="form" class="form-inline" method='get' id='search'>
      <input type='text' class="form-control" class="" name='search' size=40 <?php if ($search) echo "value='$search'";?>>
      <input type='submit' class="btn btn-default btn-sm" name='submit' value='Search'>
      <a href='archive.php'><button class="btn btn-default btn-sm" type='button'>Clear</button></a>
    </form>
      <?php
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

        // Create the Pagination Bars
        $searchAddon = ($search) ? "&search=$search" : "";
        $pageString = "<nav><ul class='pagination'>";
        $pageString .= ($page == 1) ?
            "<li class='disabled'><a href='#' aria-label='Previous'><span aria-hidden='true'>&lt</span></a></li>" :
            "<li><a href=archive.php?page=" . ($page - 1) . "$searchAddon aria-label='Previous'><span aria-hidden='true'>&lt</span></a></li>";
        while ($navIndex <= $navLast) {
          $pageString .= ($navIndex == $page) ?
            "<li class='active'><a href='#'>$navIndex<span class='sr-only'>(current)</span></a></li>" :
            "<li><a href='archive.php?page=$navIndex$searchAddon'>$navIndex</a></li>";
          $navIndex++;
        }
        $pageString .= ($page == $numPages) ?
            "<li class='disabled'><a href='#' aria-label='Next'><span aria-hidden='true'>&gt</span></a></li>" :
            "<li><a href=archive.php?page=" . ($page + 1) . "$searchAddon aria-label='Next'><span aria-hidden='true'>&gt</span></a></li>";
        $pageString .= "</ul></nav>";

        if ($result->num_rows > 0) {
          echo $pageString;
          echo "<ul id='bookList' class='media-list'>";

          // Display book listings
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
            echo "<a href=\"index.php?edit={$res['Id']}\" class='editLink'><button class='btn btn-default btn-sm' type='button'>Edit</button></a>";
            echo "<button type='button' class='clickable btn btn-warning btn-sm' onclick=\"deleteBook({$res['Id']}, '{$res['Title']}')\">Delete</button><br/>";
            echo "<button type='button' class='clickable btn btn-primary btn-sm' onclick=\"displayEmbed(this)\">Embed Code</button>";
            echo "<div class='embedCodeContainer'><textarea class='embedCode col-sm-11' rows='5' spellcheck='false'>&ltiframe src='$reader?bookID={$res['Id']}' allowfullscreen webkitallowfullscreen&gt&ltiframe&gt</textarea><div>";
            echo "</div></li><hr>";
          }

          echo "</ul>";
          echo $pageString;
        } else if ($search) {
          echo "Your search had no results, sorry";
        } else if ($page == 1) {
          echo "No books stored in the Archive";
        }

      ?>

  </div>
</body>
</html>