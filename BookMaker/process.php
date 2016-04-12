<?php
  include 'config.php';

  // SafeGuards
  if (!isset($_POST['action']) || !isset($_POST['step'])) {

    // File Exceeds Max Upload Size
    if (empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
      $max_upload_size = min(ini_get('post_max_size'), ini_get('upload_max_filesize'));
      $exitStr = "Max File Upload Size: {$max_upload_size}.\n\nPlease contact your server administrator (di.initiatives@wheaton.edu) to upload a larger file";

      header('HTTP/1.1 500 Internal Server Error');
      header('Content-Type: text/html');
      exit($exitStr);
    }

    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: text/html');
    exit("error: Something Went Wrong");
  }

  $mysqli = new mysqli("localhost", $dbUser, $dbPass, $dbName);
  if ($mysqli->connect_error) {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: text/html');
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
  }

  /**
   * parseFilenames
   *
   * @param $zip_name Name of the zip file to extract file names from
   * @param &$err Reference to a string to write errors to
   * @return -1 if errors
   * @return Array of Arrays [[ ind (int), semantic (str), filename (str)]] representing all parsable filenames sorted by index
   */
  function parseZipFilenames($zip_name, &$err = null) {
    // Open zip file & read filenames into array
    $filenames = array();
    $hand = zip_open($zip_name);
    while ($currResource = zip_read($hand))
      array_push($filenames, zip_entry_name($currResource));

    // Remove path prefix from filenames if the images are in a directory
    if (substr($filenames[0], -1) == "/") {
      $dir = array_shift($filenames);
      foreach ($filenames as $index => $name)
        $filenames[$index] = substr($name, (strpos($name, $dir) + strlen($dir)));
    }

    // Check that all visible files have the same extension
    $ext = strrchr($filenames[0], ".");

    // Parse filenames into arrays
    $delim = $GLOBALS['delimiter']; $tmp = array();
    foreach ($filenames as $index => $name) {

      // Remove the prefix
      strtok("!" . $name, $delim);

      $filenames[$index] = array(
        'ind' => intval(strtok($delim)),
        'semantic' => strstr(strtok($delim), $ext, true),
        'filename' => $name
      );
      $tmp[$index] = $filenames[$index]['ind'];
    }

    // Sort array by indices
    array_multisort($tmp, $filenames);
    return $filenames;
  }

  /**
   * Delete a file or recursively delete a directory
   *
   * @param string $str Path to file or directory
   */
  function recursiveDelete($str) {
    if (is_file($str)) return @unlink($str);
    elseif (is_dir($str)) {
      $scan = glob(rtrim($str,'/').'/*');
      foreach($scan as $index=>$path) recursiveDelete($path);
      return @rmdir($str);
    }
  }

  // Step 1: submission of title, author, description, & zipfile (if creating)
  if ($_POST['step'] == "1") {
    // Get all Post variables
    $action = $_POST['action'];
    if ( $action == "create" && (!isset($_FILES) || !isset($_FILES['zipUpload']))) exit("No Zip File Uploaded");
    $id = (isset($_POST['id'])) ? $_POST['id'] : '';
    $title = (isset($_POST['title'])) ? $_POST['title'] : '';
    $author = (isset($_POST['author'])) ? $_POST['author'] : '0';
    $desc = (isset($_POST['description'])) ? $_POST['description'] : '0';
    $first_left = (isset($_POST['first_left'])) ? $_POST['first_left'] : '1';
    $cover = (isset($_POST['cover'])) ? $_POST['cover'] : '';


    // Parse zipped pages
    if ($action == "create") {
      $pages = parseZipFilenames($_FILES['zipUpload']['tmp_name']);
    // Zipped pages parsed


      // Replace missing indices with blank pages
      $calcTotal = $pages[count($pages) - 1]['ind'] - $pages[0]['ind'];
      $missingInd = array();
      if ( count($pages) < $calcTotal) {
        $ind = $pages[0]['ind'];
        for ($i = 0; $i <= $calcTotal; $i++) {
          if ($ind < $pages[$i]['ind']) {
            array_push($missingInd, $ind);
            array_splice($pages, $i, 0, array(array( 'ind' => $ind, 'semantic' => "Missing", 'filename' => "Blank.jpg" )));
          }
          $ind++;
        }
      }
      // Missing indices replaced by blank pages


      // Extract Img Files to tmp folder (tmp directory does not have any subdirectories)
      $zip = new ZipArchive;
      $res = $zip->open($_FILES['zipUpload']['tmp_name']);
      $tmpDir = 'tmp/';
      if ($res !== TRUE) { /*quit*/ }

      // Make sure tmp folder is empty
      recursiveDelete($tmpDir);
      $zip->extractTo($tmpDir);

      // If the the thing extracted is a directory, move all img files up into tmp directory
      $parDir = $zip->getNameIndex(0);
      if (is_dir($tmpDir . $parDir)) {
        foreach (glob($tmpDir . $parDir . "*") as $path)
          rename($path, $tmpDir . substr(strstr($path, $parDir), strlen($parDir)));
        rmdir($tmpDir . $parDir);
      }
      $zip->close();
      // Img Files extracted to tmp folder

      // Default cover img is the first image
      if (!$cover) $cover = $pages[0]['filename'];

      // Get height & width of all pages by get size of the cover
      list($width, $height) = getimagesize($tmpDir . $cover);
    }

    // Write all information to be returned
    $tmpStore = array("action" => $action, "id" => $id, "title" => $title, "author" => $author, "desc" => $desc, "width" => $width,
                      "height" => $height, "first_left" => $first_left, "cover" => $cover , "pages" => $pages );


    header('Content-Type: application/json');
    echo json_encode($tmpStore);



  // Step 2: orgainzation of pages & official write/edit of book to disk
  } elseif ($_POST['step'] == '2') {
    $action = $_POST['action'];
    $id = (isset($_POST['id'])) ? $_POST['id'] : '';
    $title = (isset($_POST['title'])) ? $_POST['title'] : '';
    $author = (isset($_POST['author'])) ? $_POST['author'] : '0';
    $desc = (isset($_POST['desc'])) ? $_POST['desc'] : '';
    $first_left = (isset($_POST['first_left'])) ? $_POST['first_left'] : '1';
    $cover = (isset($_POST['cover'])) ? $_POST['cover'] : '';
    $pages = (isset($_POST['pages'])) ? json_decode($_POST['pages'], true) : '';
    $height = (isset($_POST['height'])) ? $_POST['height'] : '0';
    $width = (isset($_POST['width'])) ? $_POST['width'] : '0';



    // Insert into MySqli database
    if ($id)
      $query = "UPDATE books SET Title='$title', Author='$author', Description='$desc', FirstLeft=$first_left, Cover='$cover' WHERE Id=$id";
    else
      $query = "INSERT INTO books (Title, Author, Description, Width, Height, FirstLeft, Cover) VALUES ('$title', '$author', '$desc', $width, $height, $first_left, '$cover');";
    $mysqli->query($query);
    if (!$id) $id = $mysqli->insert_id;


    function combinePaths($path1, $path2) {
      if ($path2 == "") return $path1;

      $path1 = explode("/", $path1);
      $path2 = explode("/", $path2);

      while (count($path2) > 0) {
        if ($path2[0] == "..")
          array_pop($path1);
        elseif (!($path2[0] == "." || $path2[0] == ""))
          array_push($path1, $path2[0]);

        array_shift($path2);
      }

      return implode("/", $path1);
    }


    if ($action == "create") {
      // Generate a handle
      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
      $currLoc = "{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
      $bookreader = $protocol . combinePaths(substr($currLoc, 0, strrpos($currLoc, "/")), $reader);
      $bookPath = $bookreader . "?bookID=$id";
      $handleGeneration = $handleGenerator . "?Action=Create&Url=" . $bookPath;

      $handle = file_get_contents($handleGeneration);

      if (!$handle || strpos($handle, "Error") !== False) {
        $removeQuery = "DELETE FROM books WHERE Id=$id;";
        $mysqli->query($removeQuery);
        die("error: Error generating handle");
      }

      $handleQuery = "UPDATE books SET Handle='$handle' WHERE Id=$id;";
      $mysqli->query($handleQuery);

      // Move files from tmp to disk
      $tmpDir = "tmp/";
      mkdir($booksDir . "Images/" . $id);
      foreach ($pages as $currPage)
        rename($tmpDir . $currPage['filename'], $booksDir . "Images/" . $id . "/" . $currPage['filename']);

    }

    // Write JSON file to disk (page order)
    $f = fopen($booksDir . "JSON/" . $id . ".json", "w");
    fwrite($f, json_encode($pages));
    fclose($f);
  }
