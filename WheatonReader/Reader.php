<?php

  include_once '../BookMaker/config.php';
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
    Failed To Load Book (this is awkward...)<br/>

    <noscript>
    <p>
        The BookReader requires JavaScript to be enabled. Please check that your browser supports JavaScript and that it is enabled in the browser settings.
    </p>
    </noscript>
</div>

<script type="text/javascript" src="WheatonReader.js"></script>
<?php
  $query = "SELECT * FROM books WHERE Id=$bookID;";
  $result = $mysqli->query($query);
  if (isset($result)) {
    $res = $result->fetch_assoc();

    $jsonFile = $booksDir . "JSON/" . $bookID . ".json";
    $hand = fopen($jsonFile, "r");
    $pageData = fread($hand, filesize($jsonFile));
    $numPages = count(json_decode($pageData));
    fclose($hand);


    echo '<script>';
    echo "setVals({$res['Width']}, {$res['Height']}, $numPages, \"{$res['Title']}\", \"{$res['Author']}\", \"{$res['Description']}\", \"{$booksDir}Images/{$bookID}/\", $pageData, {$res['FirstLeft']}, \"{$res['Cover']}\", \"{$res['Handle']}\");";
    echo 'br.init();';
    echo "runAfterInit();";
    echo '</script>';
  }
  $mysqli->close();
?>

</body>
</html>
