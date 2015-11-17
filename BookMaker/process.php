<?php
  include 'config.php';

  // SafeGuards
  if (!isset($_POST) || !isset($_POST['action']) || !isset($_POST['step'])) exit("Something Went Wrong");
  $mysqli = new mysqli("localhost", $dbUser, $dbPass, $dbName);
  if ($mysqli->connect_error) die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);

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
    $filenames = []; $hand = zip_open($zip_name);
    while ($currResource = zip_read($hand))
      $filenames[] = zip_entry_name($currResource);

    // Remove path prefix from filenames
    $dir = array_shift($filenames);
    foreach ($filenames as $index => $name)
      $filenames[$index] = substr($name, (strpos($name, $dir) + strlen($dir)));

    // Check that all visible files have the same extension
    $ext = strrchr($filenames[0], ".");

    // Parse filenames into arrays
    $delim = $GLOBALS['delimiter']; $tmp = [];
    foreach ($filenames as $index => $name) {
      strtok($name, $delim);
      $filenames[$index] = [
        'ind' => intval(strtok($delim)),
        'semantic' => strstr(strtok($delim), $ext, true),
        'filename' => $name
      ];
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

  // Get all Post variables
  if ($_POST['step'] == "1") {
    $action = $_POST['action'];
    if ( $action == "create" && (!isset($_FILES) || !isset($_FILES['zipUpload']))) exit("No Zip File Uploaded");
    $id = (isset($_POST['id'])) ? $_POST['id'] : '';
    $title = (isset($_POST['title'])) ? $_POST['title'] : '';
    $author = (isset($_POST['author'])) ? $_POST['author'] : '0';
    $desc = (isset($_POST['description'])) ? $_POST['description'] : '0';
    $first_left = (isset($_POST['first_left'])) ? $_POST['first_left'] : '1';
    $cover = (isset($_POST['cover'])) ? $_POST['cover'] : '';

    if ($action == "create") {
      // Parse zipped pages
      $pages = parseZipFilenames($_FILES['zipUpload']['tmp_name']);
      // Zipped pages parsed


      // Replace missing indices with blank pages
      $calcTotal = $pages[count($pages) - 1]['ind'] - $pages[0]['ind'];
      $missingInd = [];
      if ( count($pages) < $calcTotal) {
        $ind = $pages[0]['ind'];
        for ($i = 0; $i <= $calcTotal; $i++) {
          if ($ind < $pages[$i]['ind']) {
            $missingInd[] = $ind;
            array_splice($pages, $i, 0, [[ 'ind' => $ind, 'semantic' => "Missing", 'filename' => "Blank.jpg"]]);
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

      // Assure tmp folder is empty
      recursiveDelete($tmpDir);
      $zip->extractTo($tmpDir);

      // If the the thing extracted is a directory
      $parDir = $zip->getNameIndex(0);
      if (is_dir($tmpDir . $parDir)) {
        foreach (glob($tmpDir . $parDir . "*") as $path)
          rename($path, $tmpDir . substr(strstr($path, $parDir), strlen($parDir)));
        rmdir($tmpDir . $parDir);
      }
      $zip->close();
      // Img Files extracted to tmp folder

      if (!$cover) $cover = $pages[0]['filename'];
      list($width, $height) = getimagesize($tmpDir . $cover);
    } else {

    }

    $tmpStore = [ "action" => $action, "id" => $id, "title" => $title, "author" => $author, "desc" => $desc, "width" => $width,
                  "height" => $height, "first_left" => $first_left, "cover" => $cover , "pages" => $pages ];
    /*$f = fopen($tmpDir . "tmp.json", "w");
    fwrite($f, json_encode($tmpStore));
    fclose($f);*/
    echo json_encode($tmpStore);

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

    // Insert into MySql database
    if ($id)
      $query = "UPDATE books SET Title='$title', Author='$author', Description='$desc', FirstLeft=$first_left, Cover='$cover' WHERE Id=$id";
    else
      $query = "INSERT INTO books (Title, Author, Description, Width, Height, FirstLeft, Cover) VALUES ('$title', '$author', '$desc', $width, $height, $first_left, '$cover');";
    $mysqli->query($query);
    if (!$id) $id = $mysqli->insert_id;

    // Move files from tmp to disk
    $tmpDir = "tmp/";
    mkdir($booksDir . "Images/" . $id);
    foreach ($pages as $currPage)
      rename($tmpDir . $currPage['filename'], $booksDir . "Images/" . $id . "/" . $currPage['filename']);

    // Write JSON file to disk
    $f = fopen($booksDir . "JSON/" . $id . ".json", "w");
    fwrite($f, json_encode($pages));
    fclose($f);

    if ($id >= 0)
      echo $id;
    else
      echo "error";
  }
