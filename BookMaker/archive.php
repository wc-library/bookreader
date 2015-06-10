
<!DOCTYPE html>
<html>
<head>
  <?php
    include 'config.php';

    $search = (isset($_GET['search'])) ? $_GET['search'] : '';
    $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? intval($_GET['page']) : 1;
    $db = new SQLite3($database);

    if (isset($_POST['delete'])) {
      $delete = $db->escapeString($_POST['delete']);
      $deleted = $db->query("SELECT Title FROM books WHERE Id=$delete");
      if ($deleteResult = $deleted->fetchArray())
        $deletedTitle = $deleteResult['Title'];
      $db->exec("DELETE FROM books WHERE Id=$delete");
    }

  ?>
  <title>Digital Book Archive</title>
  <style>
    body { font-family: "Times New Roman", Times, serif; font-size: 150%; word-wrap: break-word; }
    h1 { text-align: center; margin-bottom: 0px; margin-top: 10px;}
    #body { box-shadow: 10px 10px 30px #888888; border-radius: 20px; overflow: hidden; -webkit-transition: height 1s; transition: height 1s; -moz-transition: height 1s; -o-transition: height 1s; position: relative; margin: 60px auto; width: 700px; color: #101010; -webkit-animation-fill-mode: forwards; animation-fill-mode: forwards; background-image: -webkit-linear-gradient(left top, #606060, #DFDFDF); background-image: -o-linear-gradient(left top, #606060, #E0E0E0); background-image: -moz-linear-gradient(left top, #606060, #E0E0E0); background-image: linear-gradient(left top, #606060, #E0E0E0);}
    #tabs { width: 100%; height: 40px; text-align: center; padding: 0px; margin-top: 0px; }
    #tabs li { display: block; padding-top: 4px;  float: left; box-sizing: border-box; width: 50%; height: 100%; background-color: #DDD}
    #tabs li:hover { cursor: pointer; background-color: AliceBlue; }
    #tabs a {color: inherit; text-decoration: none;}
    #tabs .selected { background: none; }
    #tabs .selected:hover { cursor: default; background-color: inherit; }
    #createTab {border-top-left-radius: 20px; }
    #archiveTab {border-top-right-radius: 20px; }
    #content { padding: 0px 60px;}
    #search {margin: 20px 40px 30px; }
    #bookList {text-align: left; width: 100%; padding: 0; list-style-type: none; }
    #bookList li { font-weight: normal; font-size: 90%; }
    #bookList hr { margin: 20px inherit; }
    #bookList a { color: blue; text-decoration: none; }
    #bookList a:hover { text-decoration: underLine; }
    #bookList .author { padding-left: 20px; font-size: 80%; }
    #bookList .clickable { color: blue; }
    #bookList .clickable:hover { cursor: pointer; text-decoration: underline; }
    #bookList .options { float: right; text-align: right; position: relative;}
    #bookList .editLink { float: left; }
    #bookList .name { float: left; }
    .smallSpacing { letter-spacing: -3px; }
    .pageNav { width: 100%; text-align: center; clear: both; }
    .pageNav a { text-decoration: none; color: blue; }
    .pageNav a:hover { text-decoration: underLine; }
    #blackOut {background: black; opacity: .9; position: absolute; left: 0px; top: 0px; z-index: 1;}
    #bookFrameFrame {position: absolute; margin: auto; left: 0; right: 0; top: 0px; z-index: 2;}
    .bookTab {float: right; height: 24px; width: 60px;; box-sizing: border-box; background-color: #333; color: #EEE; border: 1px #9A9A9A solid; border-bottom: none; border-right-color: #EEE; border-top-right-radius: 4px; border-top-left-radius: 4px; text-align: center; font-size: 18px; }
    .bookTab:hover {cursor: pointer; background-color: #666; }
    #bookFrame {box-sizing: border-box; }
    #embed { position: absolute; background: orange; border: 2px GoldenRod solid; margin: auto; left: 0; right:0; top: 30%; width: 50%; height: 30%; text-align: left; z-index: 2;}
    #embed h2 {text-align: center; }
    #embed pre { bottom: 0; background: #EEE; border: 2px black solid; margin: 10px; padding: 10px;}
  </style>
  <?php echo "<style> #body { height: " . ((isset($_GET['height'])) ? $_GET['height'] : "500px") . "; }</style>";?>
  <?php
    echo "<script>";
    echo "var reader = '$reader';";
    echo "var pageNum = " . (($page) ? $page : 1) . ";";
    if (isset($deletedTitle)) { echo "alert('$deletedTitle successfully deleted')";}
    echo "</script>";
  ?>
  <script>

    function init() {
      var listHeight = (document.getElementById('bookList') != null) ? parseInt(window.getComputedStyle(document.getElementById('bookList')).height) : 0;
      var bodyHeight = Math.max(listHeight + 400, 500);
      document.getElementById('body').style.height = bodyHeight + 'px';
      document.getElementById('search').action = addGet(document.getElementById('search').action, 'height', bodyHeight + 'px');
      var anchors = document.getElementsByTagName('a');
      for (curr in anchors) {
        if (!isNaN(curr)) {
          anchors[curr].addEventListener('click', function(event) {
          event.preventDefault();
          leave(this.href);
        });
        }
      }
    }

    function blackout(elem) {
      var body = document.body, html = document.documentElement;
      var height = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight, window.innerHeight);
      var width = Math.max(body.scrollWidth, body.offsetWidth, html.clientWidth, html.scrollWidth, html.offsetWidth, window.innerWidth);

      var blackOut = document.createElement('div');
      blackOut.id = 'blackOut';
      blackOut.style.height = height + 'px';
      blackOut.style.width = width + 'px';
      blackOut.onclick = function() {undisplayBlackOut(elem); };

      body.appendChild(blackOut);
      return blackOut;
    }

    function undisplayBlackOut(elem) {
      document.body.removeChild(document.getElementById('blackOut'));
      document.body.removeChild(document.getElementById(elem));
    }

    function displayBook(id, title) {
      var blackOut = blackout('bookFrameFrame');

      var bookFrameFrame = document.createElement('div');
      bookFrameFrame.id = 'bookFrameFrame';
      var bookHeight = Math.min(600, parseInt(blackOut.style.height));
      bookFrameFrame.style.height = bookHeight + 20 + "px";
      bookFrameFrame.style.width = Math.min(800, parseInt(blackOut.style.width)) + "px";
      bookFrameFrame.style.top = (window.innerHeight / 2 - bookHeight / 2 + window.pageYOffset) + "px";

      var bookFrame = document.createElement('iframe');
      bookFrame.id = 'bookFrame';
      bookFrame.src = addGet(reader, 'bookID', id);
      bookFrame.style.height = bookHeight + "px";
      bookFrame.style.width = "100%";
      bookFrame.setAttribute('webkitallowfullscreen', 'true');
      bookFrame.setAttribute('allowfullscreen', 'true')

      var editTab = document.createElement('div');
      editTab.classList.add('bookTab');
      editTab.id = 'editTab';
      editTab.onclick = function() { document.location.href = 'index.php?edit=' + id; };
      editTab.innerHTML = 'edit';

      var deleteTab = document.createElement('div');
      deleteTab.classList.add('bookTab');
      deleteTab.id = 'deleteTab';
      deleteTab.onclick = function() { undisplayBlackOut('bookFrameFrame'); deleteBook(id, title); }
      deleteTab.innerHTML = 'delete';

      var closeTab = document.createElement('div');
      closeTab.classList.add('bookTab');
      closeTab.id = 'closeTab';
      closeTab.onclick = function() { undisplayBlackOut('bookFrameFrame'); }
      closeTab.innerHTML = 'close';

      bookFrameFrame.appendChild(closeTab);
      bookFrameFrame.appendChild(deleteTab);
      bookFrameFrame.appendChild(editTab);
      bookFrameFrame.appendChild(bookFrame);

      document.body.appendChild(bookFrameFrame);
    }

    function displayEmbed(id) {
      var blackOut = blackout('embed');

      var embed = document.createElement('div');
      embed.id = 'embed';
      embed.innerHTML = "<h2>To embed, insert the following HTML:</h2>"
      embed.innerHTML += "<pre>&ltiframe src='" + reader + "?bookID=" + id + "' allowfullscreen webkitallowfullscreen&gt&ltiframe&gt</pre>";

      document.body.appendChild(embed);
      embed.style.top = (window.innerHeight / 2 - parseInt(window.getComputedStyle(embed).height) / 2 + window.pageYOffset) + "px";
    }

    function deleteBook(id, title) {
      if (confirm("Are you sure you would like to delete '" + title + "'?")) {
        var form = document.createElement('form');
        var src = 'archive.php';
        src = addGet(src, 'page', (lastpage && lastonpage && pageNum != 1) ? pageNum - 1 : pageNum);
        src = addGet(src, 'height', document.getElementById('body').style.height);
        form.setAttribute("method", "post");
        form.setAttribute("action", src);

        var deleteVal = document.createElement('input');
        deleteVal.setAttribute('type', 'hidden');
        deleteVal.setAttribute('name', 'delete');
        deleteVal.setAttribute('value', id);

        form.appendChild(deleteVal);
        document.body.appendChild(form);
        form.submit();
      }
    }

    function leave(src) {
      var body = document.getElementById('body');
      var plainURL = (window.location.href.indexOf('?') == -1) ? window.location.href : window.location.href.substring(0, window.location.href.indexOf('?'));
      var plainSrc = (src.indexOf('?') == -1) ? src : src.substring(0, src.indexOf('?'));
      if (plainSrc == plainURL) window.location.href = addGet(src, "height", body.style.height);
      if (body.style.height == '500px') window.location.href = src;
      body.style.height = '500px';
      body.addEventListener('transitionend', function() { window.location.href = src;});
    }

    function addGet(url, key, val) { return url + ((url.indexOf('?') == -1) ? '?' : '&') + key + '=' + val; }
  </script>
</head>
<body onload="init()">
  <div id='body'>
    <ul id='tabs'>
      <a href='index.php'><li id='createTab'>Creator</li></a>
      <a><li id='archiveTab' class='selected'>Archive</li></a>
    </ul>
    <div id='content'>
      <h1>Digital Book Archive</h1>
      <form action='archive.php' method='get' id='search'>
        <input type='text' name='search' size=40 <?php if ($search) echo "value='$search'";?>>
        <input type='submit' name='submit' value='Search'>
        <a href='archive.php'><button type='button' onclick''>Reset</button></a>
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
          $result = $db->query("$query;");
          $rows = $db->query("$countQuery;");
          $row = $rows->fetchArray();
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
          $searchAddon = ($search) ? "&search=$search" : "";
          $pageString = "<div class='pageNav'>";
          $pageString .= ($page == 1) ? "<span class='smallSpacing'>&lt&lt</span>&nbsp" : "<a href=archive.php?page=1$searchAddon><span class='smallSpacing'>&lt&lt</span></a>&nbsp";
          $pageString .= ($page == 1) ? "&lt&nbsp" : "<a href=archive.php?page=" . ($page - 1) . "$searchAddon>&lt</a>&nbsp";
          while ($navIndex <= $navLast) {
            $pageString .= ($navIndex == $page) ? "$navIndex&nbsp" : "<a href='archive.php?page=$navIndex$searchAddon'>$navIndex</a>&nbsp";
            $navIndex++;
          }
          $pageString .= ($page == $numPages) ? "&gt&nbsp" : "<a href=archive.php?page=" . ($page + 1) . "$searchAddon>&gt</a>&nbsp";
          $pageString .= ($page == $numPages) ? "<span class='smallSpacing'>&gt&gt&nbsp</span>" : "<a href=archive.php?page=$numPages$searchAddon><span class='smallSpacing'>&gt&gt</span></a>&nbsp";
          $pageString .= "</div>";
          $returnedSomething = false;
          $num = $page * 10 - 9;
          while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
            if (!$returnedSomething) {
              echo $pageString;
              echo "<ul id='bookList'>";
              $returnedSomething = true;
            }
            if (strlen($res['Title']) > 30) $res['Title'] = substr($res['Title'], 0, 30) . '...';
            echo "<li>";
            echo "<div class='name'>$num) <span class='clickable' onclick=\"displayBook(" . $res['Id'] . ", '" . $res['Title'] . "')\">{$res['Title']}</span><br/>";
            if ($res['Author']) echo "<span class='author'>By: {$res['Author']}</span></div>";
            else echo '</div>';
            echo "<div class='options''><a href=\"index.php?edit={$res['Id']}\" class='editLink'>Edit</a> <span class='clickable' onclick=\"deleteBook({$res['Id']}, '{$res['Title']}')\">Delete</span><br/>";
            echo "<span class='clickable' onclick=\"displayEmbed({$res['Id']})\">Embed Code</span></div>";
            echo "<br/><br/><hr/></li>";
            $num++;
          }
          if ($returnedSomething) {
            echo "</ul>";
            echo $pageString;
          } else if ($search) {
            echo "Your search had no results, sorry";
          } else if ($page == 1) {
            echo "No books stored in the Archive";
          }

        ?>
    </div>
  </div>
</div>
<?php echo "<script> var lastpage = " . (($page === $numPages) ?  "true" : "false") . "; var lastonpage = " . (($totalCount % 10 === 1) ? "true" : "false") . "; </script>"; ?>
</body>
</html>
