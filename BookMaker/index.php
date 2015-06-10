
<!DOCTYPE html>

<html>
<head>
  <?php
    include 'config.php';

    if (isset($_POST['action'])) {
      if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        echo "<title>Digital Book Editor</title>";
      } else {
        echo "<title>Digital Book Creator</title>";
      }
      $title = $_POST['title'];
      $author = $_POST['author'];
      $description = $_POST['description'];
      $directory = $_POST['directory'];
      $firstLeft = $_POST['first_left'];
      $prefix = $_POST['prefix'];
      $extension = $_POST['extension'];
    } else if (isset($_GET['edit'])) {
      $id = $_GET['edit'];


      $db = new SQLite3($database);
      $result = $db->query("SELECT * FROM books WHERE Id=$id;");
      $result = $result->fetchArray(SQLITE3_ASSOC);

      $title = $result['Title'];
      $author = $result['Author'];
      $description = $result['Description'];
      $directory = $result['Directory'];
      $firstLeft = $result['FirstLeft'];
      $prefix = $result['Prefix'];
      $extension = $result['Extension'];
      $db->close();
      echo "<title>Digital Book Editor</title>";
    } else {
      echo "<title>Digital Book Creator</title>";
    }

  ?>
  <style>
    body { font-family: "Times New Roman", Times, serif; font-size: 150%; word-wrap: break-word; }
    #body { background-image: -webkit-linear-gradient(left top, #606060, #DFDFDF); color: #101010; box-shadow: 10px 10px 30px #888888; border-radius: 20px; position: relative; margin: 60px auto; width: 700px; height: 500px; background-image: -o-linear-gradient(left top, #606060, #E0E0E0); background-image: -moz-linear-gradient(left top, #606060, #E0E0E0); background-image: linear-gradient(left top, #606060, #E0E0E0);}
    h1 { text-align: center; margin-bottom: 0px; margin-top: 10px; }
    h2 { text-align: center; margin-bottom: 0px; margin-top: 0; font-size: 120%; font-weight: normal; }
    #tabs { width: 100%; height: 40px; text-align: center; padding: 0px; margin-top: 0px; }
    #tabs li { display: block; padding-top: 4px;  float: left; box-sizing: border-box; width: 50%; height: 100%; background-color: #DDD}
    #tabs li:hover { cursor: pointer; background-color: AliceBlue; }
    #tabs a {color: inherit; text-decoration: none;}
    #tabs .selected { background: none; }
    #tabs .selected:hover { cursor: default; background-color: inherit; }
    #createTab {border-top-left-radius: 20px; }
    #archiveTab {border-top-right-radius: 20px; }
    #content { padding: 0px 60px;}
    input[type=text] {position: relative; top: -1px;}
    fieldset { border: none; }
    #form textarea { resize: none; vertical-align: top; margin-top: 6px; height: 80px; width: 380px;}
    #set0 { margin: 10px 0px 0px; padding-left: 0; }
    #set0 label { width: 120px; display: inline-block; text-align: right; margin-right: 10px; }
    #set1 { float: left; }
    #set1 label { width: 100px; display: inline-block; text-align: right; margin-right: 10px; }
    #extensionDiv { float: right; }
    #extension { font-size: 60%; margin-left: 40px; }
    #title { font-size: 90%; }
    .required {color: Maroon; }
    hr { border-color: DimGray; }
    .tooltip { display: none; padding: 6px; font-size: 50%; }
    .small { font-size: 70%; }
    .medium { font-size: 80%; }
    .large { font-size: 90%; }
    #prefixDiv {position: relative;}
    #prefixDiv:hover .tooltip { background: GoldenRod; border-radius: 3px; bottom: -42px; display: inline; left: 60px; position: absolute; opacity: 1; }
    #prefixDiv:hover .tooltip:before { display: block; content: ""; position: absolute; top: -4px; width: 0; height: 0; border-left: 5px solid transparent; border-right: 5px solid transparent; border-bottom: 5px solid GoldenRod; }
    #radioLabel { width: 100px; display: inline-block; text-align: right; margin-right: 10px; }
    #radioDiv { clear: both; line-height: 70%; margin-left: 20px; }
    #submit { position: absolute; bottom: 20px; right: 60px; font-size: 80%; width: 200px; height: 60px; border-radius: 10px; background-color: #DDD; }
    #submit:hover { cursor: pointer; background-color: AliceBlue; }
  </style>
  <?php
    echo "<script>";
    echo "function init() {";
    if (isset($title)) {
      echo "document.getElementById('title').value = '$title';";
      echo "document.getElementById('author').value = '$author';";
      echo "document.getElementById('description').value = '$description';";
      echo "document.getElementById('directory').value = '$directory';";
      echo "document.getElementById('prefix').value = '$prefix';";
      echo "document.getElementById('$extension').setAttribute('selected', 'true');";


      if (isset($id)) {
        echo "var id = document.createElement('input');";
        echo "id.setAttribute('type', 'hidden');";
        echo "id.setAttribute('name', 'id');";
        echo "id.setAttribute('value', $id);";
        echo "document.getElementById('form').appendChild(id);";
        echo "document.getElementById('action').value = 'alter';";
        echo "document.getElementById('pageTitle').innerHTML = 'Digital Book Editor';";
        echo "document.getElementById('submit').value = 'Edit Book';";
        echo "document.getElementById('createTab').parentNode.href = 'index.php';";
        echo "document.getElementById('createTab').classList.remove('selected');";
        echo "document.getElementById('createTab').style.borderRight = '1px #303030 solid';";
        echo "document.getElementById('archiveTab').style.borderLeft = '1px #303030 solid';";
      }
    }
    echo "}";
    echo "</script>";
  ?>
</head>
<body onload='init()'>
  <div id='body'>
    <ul id='tabs'>
      <a>
        <li id='createTab' class='selected'>Creator</li>
      </a>
      <a href='archive.php'>
        <li id='archiveTab'>Archive</li>
      </a>
    </ul>
    <div id='content'>
      <h1 id='pageTitle' onclick='init()'>Digital Book Creator</h1>
      <form action='result.php' method='post' id='form'>
        <fieldset id='set0'>
          <label for='title'>Title: </label><input type='text' name='title' id='title' required><span class='required'>*</span><br/>
          <label for='author' class='medium'>Author: </label><input type='text' name='author' id='author'><br/>
          <label for='description' class='medium'>Description: </label><textarea form='form' name='description' id='description'></textarea>
      </fieldset>
      <hr>
      <fieldset id='set1'>
        <label for='directory' class='large'>Directory: </label><input type='text' name='directory' id='directory' size='24' required><span class='required'>*</span><br>
        <div id='prefixDiv'>
          <label for='prefix' class='large'>Prefix: </label><input type='text' name='prefix' id='prefix' size='8'>
          <span class="tooltip">eg. 'page001low.jpg' & 'page002low.jpg'<br/> have a prefix of 'page'</span>
        </div>
      </fieldset>
      <div id='extensionDiv'>
        <label for='extension' class='large'>File Extension: </label><br/>
        <select name='extension' id='extension'>
          <option value='jpg' id='jpg'>.jpg</option>
          <option value='png' id='png'>.png</option>
          <option value='gif' id='gif'>.gif</option>
        </select>
      </div>
      <input type='hidden' name='action' value='create' id='action'>
      <input type='submit' id='submit' value='Create Book'>
      </form>
    </div>
  </div>
</body>
</html>
