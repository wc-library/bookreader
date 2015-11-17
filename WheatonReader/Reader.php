<?php

  include '../BookMaker/config.php';
  $mysqli = new mysqli("localhost", $dbUser, $dbPass, $dbName);
  if ($mysqli->connect_error) die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);

  $bookID = (isset($_REQUEST['bookID'])) ? $_REQUEST['bookID'] : '';

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
        The BookReader requires JavaScript to be enabled. Please check that your browser supports JavaScript and that it is enabled in the browser settings.
    </p>
    </noscript>
</div>

<script type="text/javascript" src="WheatonReader.js"></script>
<?php
  /* Query Categories: Id, Title, Author, Width, Height, NumPages, Directory, FirstLeft*/
  $query = "SELECT * FROM books WHERE Id=$bookID;";
  $result = $mysqli->query($query);
  /*print_r($result->fetch_assoc());
  /*$result = $db->querySingle('SELECT * FROM books WHERE Id=' . $bookID . ';', true); /*Id, Title, Author, Width, Height, NumPages, Directory, FirstLeft*/
  if (isset($result)) {
    $res = $result->fetch_assoc();

    $jsonFile = $booksDir . "JSON/" . $bookID . ".json";
    $hand = fopen($jsonFile, "r");
    $pageData = fread($hand, filesize($jsonFile));
    $numPages = count(json_decode($pageData));
    fclose($imageFiles);


    echo '<script>';
    echo "setVals({$res['Width']}, {$res['Height']}, $numPages, \"{$res['Title']}\", \"{$res['Author']}\", \"{$res['Description']}\", \"{$booksDir}Images/{$bookID}/\", $pageData, {$res['FirstLeft']}, \"{$res['Cover']}\");";
    echo 'br.init();';
    echo "runAfterInit();";
    echo '</script>';
  }
  $db->close();
?>

</body>
</html>
