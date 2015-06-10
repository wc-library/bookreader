<?php

  $bookID = (isset($_REQUEST['bookID'])) ? $_REQUEST['bookID'] : '';

  $database = 'Books/books.db';

  $db = new SQLite3($database);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>
<head>
    <title>Wheaton Reader</title>

    <link rel="stylesheet" type="text/css" href="BookReader/BookReader.css" id="BRCSS"/>
    <!-- Custom CSS overrides -->
    <link rel="stylesheet" type="text/css" href="WheatonReader.css"/>

    <script type="text/javascript" src="http://www.archive.org/includes/jquery-1.4.2.min.js"></script>
    <script type="text/javascript" src="http://www.archive.org/bookreader/jquery-ui-1.8.5.custom.min.js"></script>
    <script type="text/javascript" src="http://www.archive.org/bookreader/dragscrollable.js"></script>
    <script type="text/javascript" src="http://www.archive.org/bookreader/jquery.colorbox-min.js"></script>
    <script type="text/javascript" src="http://www.archive.org/bookreader/jquery.ui.ipad.js"></script>
    <script type="text/javascript" src="http://www.archive.org/bookreader/jquery.bt.min.js"></script>

    <script type="text/javascript" src="BookReader/BookReader.js"></script>
</head>
<body style="background-color: ##939598;">

<div id="BookReader">
    <!--Anything in this area will be replaced by a book if there is one -->
    Wheaton College Book Reader<br/>
    (Make sure the book ID was specified in the URL)<br/>

    <noscript>
    <p>
        The BookReader requires JavaScript to be enabled. Please check that your browser supports JavaScript and that it is enabled in the browser settings.  You can also try one of the <a href="http://www.archive.org/details/goodytwoshoes00newyiala"> other formats of the book</a>.
    </p>
    </noscript>
</div>

<script type="text/javascript" src="WheatonReader.js"></script>
<?php
  $result = $db->querySingle('SELECT * FROM books WHERE Id=' . $bookID . ';', true); /*Id, Title, Author, Width, Height, NumPages, Directory, FirstLeft*/
  if (isset($result)) {
    echo '<script>';
    echo 'var request = new XMLHttpRequest();';
    echo 'request.open("GET", "Books/JSON/' . $bookID . '.json", true);';
    echo 'request.onload = function(e) {';
    echo '  if (request.readyState == 4) { ';
    echo '      if (request.status == 200) { ';
    echo '         setVals(' . $result['Width'] . ', ' . $result['Height'] . ', ' . $result['NumPages'] . ', "' . $result['Title'] . '", "' . $result['Author'] . '", "' .$result['Description'] . '", "' . $result['Directory'] . '", JSON.parse(request.responseText), ' . $result['FirstLeft'] . ', "' . $result['Cover'] . '");';
    echo '         br.init();';
    echo "         runAfterInit()";
    echo '      } else {';
    echo '         console.error(request.statusText);';
    echo '      }';
    echo '  }';
    echo '};';
    echo 'request.send();';
    echo '</script>';
  }
  $db->close();
?>

</body>
</html>
