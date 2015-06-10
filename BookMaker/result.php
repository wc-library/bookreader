

<!DOCTYPE html>
<html>
<head>
  <?php
  include 'config.php';

  if (!isset($_POST) || !isset($_POST['action'])) {
    exit("No Book Chosen");
  }

  $action = $_POST['action'];
  $title = (isset($_POST['title'])) ? $_POST['title'] : '';
  $author = (isset($_POST['author'])) ? $_POST['author'] : '';
  $description = (isset($_POST['description'])) ? $_POST['description'] : '';
  $directory = (isset($_POST['directory'])) ? $_POST['directory'] : '';
  if ($directory[strlen($directory) - 1] !== '/') { $directory .= '/'; }   //make sure directory ends with a '/'
  $prefix = strtolower(trim((isset($_POST['prefix'])) ? $_POST['prefix'] : ''));
  $extension = (isset($_POST['extension'])) ? $_POST['extension'] : '';
  $first_left = (isset($_POST['first_left'])) ? $_POST['first_left'] : '0';
  $page_fix = (isset($_POST['page_fix'])) ? $_POST['page_fix'] : '';
  $id = (isset($_POST['id'])) ? $_POST['id'] : '';
  $pageOrder = (isset($_POST['pageOrder'])) ? json_decode($_POST['pageOrder']) : '';
  $cover = (isset($_POST['cover'])) ? $_POST['cover'] : '';

  $failure = 0;
  $filenames = scandir($directory);
  if (!$filenames) {
    $failure = 1;
    $msg = "Unable to read directory: '$directory'";
  }
  foreach ($filenames as $key => $val)
    $filenames[$key] = Array($val, $val);

  $invalids = Array(Array(), Array());
  foreach ($filenames as $key => $val) {
    //Remove any filename with the wrong extension
    if (substr($val[1], strrpos($val[1], '.')) !== ('.' . $extension)) {
      if ($val[1] !== '.' && $val[1] !== '..' && $val[1] !== '.DS_Store' && $val[1] !== 'Thumbs.db')   //Do not warn about common files
        $invalids[0][] = $val[1];
      unset($filenames[$key]);
      continue;
    }

    //Remove any filename without the prefix
    $val[0] = strtolower(trim($val[0]));
    if ($val[0] && $prefix !== '' && (strpos($val[0], $prefix) === false)) {
      $invalids[1][] = $val[1];
      unset($filenames[$key]);
      continue;
    }

    //Isolate the index as an integer & store in slot 0, keeping the full filename in slot 1
    $index = ($prefix === '') ? 0 : strpos($val[0], $prefix) + strlen($prefix);
    $len = strlen($val[0]);
    while ((!is_numeric(substr($val[0], $index, 1))) && ($index < $len))
      $index++;
    //Remove this one because the prefix was not a prefix
    if ($index === strlen($val[0])) {
      $invalids[1][] = $val[1];
      unset($filenames[$key]);
      continue;
    }
    $tmp = $val[0][$index];
    while (is_numeric(substr($val[0], ++$index, 1)))
      $tmp .= $val[0][$index];

    $filenames[$key][0] = intval($tmp);
  }

  asort($filenames);
  $filenames = array_values($filenames);
  $invalidFiles = '';
  $invalidsExist = 0;
  if (isset($invalids[0][0])) { //Invalid Extension
    $invalidFiles .= "Files with an extension other than '$extension':<br/><ol>";
    foreach ($invalids[0] as $key => $val) {
      $invalidFiles .= "<li>'$val'</li>";
    }
    $invalidFiles .= "</ol>";
    $invalidsExist = 1;
  }
  if (isset($invalids[1][0])) { //Invalid Prefix
    $invalidFiles .= "Files that had issues with the prefix '$prefix':<br/><ol>";
    foreach ($invalids[1] as $key => $val) {
      $invalidFiles .= "<li>'$val'</li>";
    }
    $invalidFiles .= "</ol>";
    $invalidsExist = 1;
  }


  //Check for missing pages
  $missing = Array();
  if (isset($filenames[0])) {
    $diff = $filenames[0][0];
    $accountedFor = 0;
    $key = 0;
    while ($key < count($filenames)) {
      if (($filenames[$key][0] - $accountedFor) !== ($key + $diff)) {
        if (isset($page_fix) && $page_fix === 'add_blanks') {
            $index = count($filenames) - 1;
            while ($index >= $key) {
              $filenames[$index + 1] = $filenames[$index--];
            }
            $filenames[$key] = Array($key + $diff, 'white');
        } else {
          $missing[] = $key + $accountedFor;
          $accountedFor++;
        }
      }
      $key++;
    }

    $dimensions = getimagesize($directory . $filenames[0][1]);

    if ($pageOrder) {
      if (!$cover)
        $cover = $filenames[0][1];
      $before = 1;
      $i = 0;
      $numPages = count($filenames);
      while ($i < count($pageOrder)) {
        if ($pageOrder[$i] === 'Sorted Pages') {
          $before = 0;
        } else if ($before) {
          $index = $numPages + $i - 1;
          while ($index >= $i)
            $filenames[$index + 1] = $filenames[$index--];
          $filenames[$i] = Array('', $pageOrder[$i]);
        } else {
          $filenames[$numPages + $i - 1] = Array('', $pageOrder[$i]);
        }
        $i++;
      }
    }

    $width = $dimensions[0];
    $height = $dimensions[1];
    $numPages = (count($filenames));
    $expectedNumPages = $filenames[count($filenames) - 1][0] - $filenames[0][0] + 1;


    $numMiss = count($missing);
    if ($numMiss !== 0 && isset($page_fix) && !($page_fix === 'ignore_missing')) {
      $failure = 2;
      $msg = ($numMiss === 1) ? '1 page missing: ' . $missing[0] : $numMiss . ' pages missing: ' . $missing[0];
      $i = 1;
      while ($i < $numMiss)
        $msg .= ', ' . $missing[$i++];
    }
  } else if (!$failure) {
    $failure = 3;
    $msg = "No images in '$directory' fit the criteria for book pages. Prefix: '$prefix' Extension: '$extension'";
  }
  ?>
  <title>Digital Book Result</title>
  <style>
    body { font-family: "Times New Roman", Times, serif; font-size: 150%; word-wrap: break-word; overflow: hidden; }
    h1 { text-align: center; margin-bottom: 0px;}
    #body { box-shadow: 10px 10px 30px #888888; border-radius: 20px; overflow: hidden; -webkit-transition: height 1s; transition: height 1s; position: relative; margin: 60px auto; width: 700px; height: 500px; color: #101010; -webkit-animation-fill-mode: forwards; animation-fill-mode: forwards; background-image: -webkit-linear-gradient(left top, #606060, #DFDFDF); background-image: -o-linear-gradient(left top, #606060, #E0E0E0); background-image: -moz-linear-gradient(left top, #606060, #E0E0E0); background-image: linear-gradient(left top, #606060, #E0E0E0);}
    #tabs { width: 100%; height: 40px; text-align: center; padding: 0px; margin-top: 0px; }
    #tabs li { display: block; padding-top: 4px;  float: left; box-sizing: border-box; width: 50%; height: 100%; background-color: #DDD}
    #tabs li:hover { cursor: pointer; background-color: AliceBlue; }
    #tabs a { color: inherit; text-decoration: none;}
    #tabs .selected { background: none; }
    #tabs .selected:hover { cursor: default; background-color: inherit; }
    #createTab { border-top-left-radius: 20px; border-right: 1px #303030 solid; }
    #archiveTab { border-top-right-radius: 20px; border-left: 1px #303030 solid; }
    #content { padding: 0px 60px; }
    #invalids { padding: 10px; }
    #invalids ol { margin-top: 0px; }
    #title { text-align: center; }
    #title h1 { font-size: 160%; margin-bottom: 2px; }
    #title h2 { font-size: 120%; font-weight: normal; margin-top: 2px; margin-bottom: 2px; }
    #displayButton { position: absolute; bottom: 40px; margin: auto; left: 0; right: 0; font-size: 100%; width: 300px; height: 90px; border-radius: 10px; background-color: #DDD; }
    #displayButton:hover { cursor: pointer; background-color: AliceBlue; }
    #blackOut { background: black; opacity: .9; position: absolute; left: 0px; top: 0px; z-index: 1; }
    #bookFrameFrame {position: absolute; margin: auto; left: 0; right: 0; top: 0px; z-index: 2;}
    .bookTab {float: right; height: 24px; width: 60px;; box-sizing: border-box; background-color: #333; color: #EEE; border: 1px #9A9A9A solid; border-bottom: none; border-right-color: #EEE; border-top-right-radius: 4px; border-top-left-radius: 4px; text-align: center; font-size: 18px; }
    .bookTab:hover {cursor: pointer; background-color: #666; }
    #bookFrame {box-sizing: border-box; }
    #nonNumericFrame { position: absolute; margin: auto; left: 0; right: 0; width: 900px; background-color: #689A10; padding: 20px; z-index: 2; border: 2px #BBB solid; border-radius: 6px; }
    #nonNumericFrame h2 {margin-top: 0; margin-bottom:20px; }
    #nonNumericFrame p { font-size: 70%; }
    #nonNumericFrame button { font-size: 80%; width: 200px; height: 60px; border-radius: 10px; background-color: #DDD; margin-top: 20px; }
    #nonNumericFrame button:hover { cursor: pointer; background-color: AliceBlue; }
    #nonNumericFrame #cancel { float: left; }
    #nonNumericFrame #submit { float: right; }
    #nonNumericFrame #radioDiv { }
    #nonNumericList { vertical-align: middle; padding: 0; margin: 0px; margin-top: 20px; float: left; width: 100%; list-style-type: none; border: 4px solid black; box-sizing: border-box;}
    #nonNumericList li { height: 40px; width: 50%; box-sizing: border-box; position: relative; margin: 0px; float: left; background: #EEE; border: 1px solid black; }
    #nonNumericList li:hover { cursor: pointer; background-color: AliceBlue; }
    .thumbnail { height: 90%; margin: 2px; vertical-align: middle;}
    .thumbnail:hover { cursor: default; }
    .imgDisplay { position: absolute; height: 600%; z-index: 3; border: 1px #111 solid; background: white; }
    .imgDisplay {cursor: default; }
    .pagePath { font-size: 80%; margin-left: 10px; line-height: 40px; }
    .pageOptions { float: right; height: 100%; font-size: 60%; text-align: right; padding-left: 2%; border-left: 2px #AAA solid; background-color: #FF7F50; position: absolute; right: 0; top: 0;}
    .pageOptions:hover { cursor: default; }
    .removeButton { height: 50%; vertical-align: middle; }
    .removeButton:hover { cursor: pointer; }
    .coverBox { margin-bottom: 0; margin-top: 0; height: 50%; vertical-align: middle; }
    .coverBox:hover {cursor: pointer; }
    #nonNumericList .unmovable { background-color: lightgray; text-align: center; }
    #nonNumericList .unmovable:hover { cursor: default; background-color: lightgray; }
    #nonNumericList .static {background-color: gray; text-align: center; }
    #nonNumericList .static:hover { cursor: default; background-color: gray; }
    #nonNumericList .tmp {background-color: #333; }
    #nonNumericList .tmp:hover { cursor: default; background-color: #333; }
    .movingLi { position: absolute; box-sizing: border-box; margin: 0px; background: AliceBlue; border: 1px solid black; list-style-type: none; z-index: 3; overflow: hidden; }
    .movingLi:active {cursor: move; }
    .unselectable { -webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; }
    #output { border: 1px solid; text-align: center; padding: 10px 0px; }
    .failure { background-color: #FCC; border-color: #F00; color: #900; margin-bottom: 30px;}
    .success { background-color: #CFC; border-color: #0F0; color: #090; }

    #embed { background: #EEE; border: 2px black solid; margin-top: 6px; position: relative; padding: 10px; font-size: 85%; }

    .failOption  { font-size: 60%; width: 30%; height: 40px; border-radius: 10px; background-color: #DDD; margin-bottom: 10px; }
    .failOption:hover { cursor: pointer; background-color: AliceBlue; }

    #extender { position: absolute; bottom: 10px; right: 10px; font-size: 70%; color: blue; }
    #extender:hover { cursor: pointer; text-decoration: underLine; }

    .clickable { color: blue; }
    .clickable:hover { cursor: pointer; text-decoration: underline; }

  </style>
  <?php echo "<script> var reader = '$reader';</script>"; ?>
  <script>
    function init() {
      if (document.getElementById('invalids') !== null && document.getElementById('invalids').offsetTop + document.getElementById('invalids').offsetHeight > 500) {
        document.getElementById('extender').innerHTML = '&#9660;more';
        document.getElementById('extender').onclick = more;
      }
    }

    function more() {
      document.getElementById('extender').innerHTML = '&#9650;less';
      document.getElementById('extender').onclick = less;
      document.getElementById('body').style.height = document.getElementById('invalids').offsetTop + document.getElementById('invalids').offsetHeight + 'px';
    }

    function less() {
      document.getElementById('extender').innerHTML = '&#9660;more';
      document.getElementById('extender').onclick = more;
      document.getElementById('body').style.height = '';
    }

    function blackout(elem, removable, resizeFunction) {
      var body = document.body, html = document.documentElement;
      var height = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight, window.innerHeight);
      var width = Math.max(body.scrollWidth, body.offsetWidth, html.clientWidth, html.scrollWidth, html.offsetWidth, window.innerWidth);

      var blackOut = document.createElement('div');
      blackOut.id = 'blackOut';
      blackOut.style.height = height + 'px';
      blackOut.style.width = width + 'px';
      if (removable != true)
        blackOut.onclick = function() {undisplayBlackOut(elem); };

      if (resizeFunction)
        document.body.onresize = function() {
          blackOut.style.height = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight, window.innerHeight) + "px";
          blackOut.style.width = Math.max(body.scrollWidth, body.offsetWidth, html.clientWidth, html.scrollWidth, html.offsetWidth, window.innerWidth) + "px";
          resizeFunction();
        };

      body.appendChild(blackOut);
      return blackOut;
    }

    function undisplayBlackOut(elem) {
      document.body.removeChild(document.getElementById('blackOut'));
      document.body.removeChild(document.getElementById(elem));
    }

    function goBack() {
      if (document.getElementById('resubmitId') != null) {
        document.getElementById('resubmit').action = 'index.php?edit=' + document.getElementById('resubmitId').value;
      } else {
        document.getElementById('resubmit').action = 'index.php';
      }
      document.getElementById('resubmit').submit();
    }

    function insertBlanks() {
      document.getElementById('resubmitPageFix').value = 'add_blanks';
      document.getElementById('resubmit').submit();
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

    function nonNumericPages(parity, directory, nonNumerics) {
      /*parity: numPages: 1 for odd | 0 for even; directory: directory of images; nonNumerics: array of filenames*/
      var spotsForPages = (parity == 1) ? 3 : 4;
      var resizeFrame = function() { document.getElementById('nonNumericFrame').style.top = (window.innerHeight / 2 - document.getElementById('nonNumericFrame').offsetHeight / 2 + window.pageYOffset) + "px"; };
      var blackOut = blackout('nonNumericFrame', true, resizeFrame);

      var frame = document.createElement('div');
      frame.id='nonNumericFrame';
      frame.innerHTML = "Left, Right";

      var list = document.createElement('ol');
      list.id ='nonNumericList';

      for (i = 0; i < 2 * Math.round((nonNumerics.length + spotsForPages + 1) / 2); i++) {
        var li = document.createElement('li');
        li.id = "li" + i;
        li.classList.add('unselectable');
        if (i < nonNumerics.length) {
          var thumbnail = document.createElement('img');
          thumbnail.src = directory + nonNumerics[i];//'../WheatonReader/Books/wall_street.large.jpg';
          thumbnail.classList.add('thumbnail');
          thumbnail.onmouseover = function() {
            var img = document.createElement('img');
            img.classList.add('imgDisplay');
            img.src = this.src;
            this.parentNode.appendChild(img);
            img.style.top = (this.offsetTop - img.offsetHeight) + "px";
            img.style.left = (this.offsetLeft - img.offsetWidth / 2) + "px";
          }
          thumbnail.onmouseout = function() { this.parentNode.removeChild(this.parentNode.querySelector('.imgDisplay')); }
          var path = document.createElement('span');
          path.classList.add('pagePath');
          path.innerHTML = nonNumerics[i];
          var options = document.createElement('div');
          options.classList.add('pageOptions');
          options.onmousedown = function(event) { event.stopPropagation(); };
          var remove = document.createElement('span');
          remove.innerHTML = "Remove";
          var removeIcon = document.createElement('img');
          removeIcon.src = 'Assets/remove.png';
          removeIcon.title = 'Remove';
          removeIcon.classList.add('removeButton');
          removeIcon.onclick = function() {
            if (confirm('Are you sure?')) {
              list.removeChild(this.parentNode.parentNode);
              var static = document.createElement('li');
              static.classList.add('static');
              list.appendChild(static);
              var numStatic = 0;
              for (var i = 0; i < list.childNodes.length; i++)
                if (list.childNodes[i].classList.contains('static'))
                  numStatic++;
              if (numStatic > 2) {
                list.removeChild(list.childNodes[list.childNodes.length - 1]);
                list.removeChild(list.childNodes[list.childNodes.length - 1]);
              }
              updateBorders(list);
            }
          };
          var cover = document.createElement('span');
          cover.innerHTML = '<br/>Cover';
          var coverBox = document.createElement('input');
          coverBox.type = 'checkbox';
          coverBox.value = '';
          coverBox.classList.add('coverBox');
          coverBox.onclick = function() { var covers = document.getElementsByClassName('coverBox'); for (var i = 0; i < covers.length; i++) { if (covers[i] != this) covers[i].checked = false; }};
          options.appendChild(remove);
          options.appendChild(removeIcon);
          options.appendChild(cover);
          options.appendChild(coverBox);
          li.appendChild(thumbnail);
          li.appendChild(path);
          li.appendChild(options);
          li.onmousedown = function(e) {
            e = e || window.event;
            if (e.button == 0 && this.parentNode == list) {
              index = indexLi(list, this);
              var movingWidth = this.offsetWidth;
              var movingHeight = this.offsetHeight;
              var movingLi = list.removeChild(this);
              var tmp = document.createElement('li');
              tmp.classList.add("tmp");
              list.insertBefore(tmp, list.childNodes[index]);
              movingLi.style.width = movingWidth + "px";
              movingLi.style.height = movingHeight + "px";
              movingLi.style.left = e.pageX - movingWidth / 2 + "px";
              movingLi.style.top = e.pageY - movingHeight / 2 + "px";
              movingLi.classList.add('movingLi');
              var possible = -1;
              var followMouse = function(event) {                     //Move mouse
                event = event || window.event;
                movingLi.style.left = event.pageX - movingLi.offsetWidth / 2 + "px";
                movingLi.style.top = event.pageY - movingLi.offsetHeight / 2 + "px";
                var overlapIndex = overlapList(event, movingLi, list, frame.offsetLeft, frame.offsetTop);
                if (overlapIndex != -1) {
                  if (list.childNodes[overlapIndex].classList.contains('static')) {
                    if (overlapIndex == 0)
                      overlapIndex = 1;
                    else {
                      while (list.childNodes[overlapIndex].classList.contains('static'))
                        overlapIndex--;
                    }
                  }
                  if (list.childNodes[overlapIndex].classList.contains('unmovable') &&
                  ((overlapIndex > indexLi(list, tmp) && overlapIndex < list.childNodes.length - 1 && list.childNodes[overlapIndex + 1].classList.contains('unmovable')) ||
                  (overlapIndex < indexLi(list, tmp) && overlapIndex > 0 && list.childNodes[overlapIndex - 1].classList.contains('unmovable')))) {
                    list.removeChild(tmp);
                    list.insertBefore(tmp, list.childNodes[index]);
                    possible = -1;
                  } else if (possible != overlapIndex) {
                    list.removeChild(tmp);
                    list.insertBefore(tmp, list.childNodes[overlapIndex]);
                    possible = overlapIndex;
                  }
                } else {
                  if (possible != -1) {
                    list.removeChild(tmp);
                    list.insertBefore(tmp, list.childNodes[index]);
                    possible = -1;
                  }
                }
                updateBorders(list);
              }
              document.body.appendChild(movingLi);
              document.addEventListener('mousemove', followMouse );
              movingLi.onmouseup = function() {                         //Drop
                document.removeEventListener('mousemove', followMouse);
                document.body.removeChild(movingLi);
                movingLi.removeAttribute('style');
                movingLi.classList.remove('movingLi');
                list.removeChild(tmp);
                if (possible == -1)
                  list.insertBefore(movingLi, list.childNodes[index]);
                else
                  list.insertBefore(movingLi, list.childNodes[possible]);
                updateBorders(list);
              };
            }

          };
        } else if (i - nonNumerics.length < spotsForPages) {
          li.classList.add('unmovable');
          li.innerHTML = 'Sorted Pages';
        } else {
          li.classList.add('static');
        }
        list.appendChild(li);
      }
      frame.innerHTML =
      ["<h2>Page Ordering</h2>",
       "<p>Below are listed the files that were the correct extension but did not have the correct prefix, these may be cover pages, etc.<br/>",
       "Please order them as they should be (left side for left pages) relative to the sorted pages and indicate the cover (thumbnail) photo.</p><hr>",
       "<div id='radioDiv'>",
          "First page is on the:",
          "<input type='radio' name='first_left' value='1' id='radioL' checked><label for='radioL'>Left</label>",
          "<input type='radio' name='first_left' value='1' id='radioR'><label for='radioR'>Right</label>",
       "</div>"].join('');

      updateBorders(list);
      frame.appendChild(list);
      var cancel = document.createElement('button');
      cancel.id = 'cancel';
      cancel.type = 'button';
      cancel.innerHTML = 'Cancel';
      cancel.onclick = goBack;
      frame.appendChild(cancel);
      var submit = document.createElement('button');
      submit.id = 'submit';
      submit.type = 'button';
      submit.innerHTML = 'Submit';
      submit.onclick = submitNonNumeric;
      frame.appendChild(submit);
      document.body.appendChild(frame);
      list.style.height = (list.childNodes.length / 2 * list.childNodes[0].offsetHeight + 8 + "px");
      frame.style.top = (window.innerHeight / 2 - frame.offsetHeight / 2 + window.pageYOffset) + "px";

      document.getElementById('radioL').addEventListener('change', function() {
        var toMove = list.removeChild(list.childNodes[0]);
        list.appendChild(toMove);
        updateBorders(list);
      });
      document.getElementById('radioR').addEventListener('change', function() {
        var toMove = list.removeChild(list.childNodes[list.childNodes.length - 1]);
        list.insertBefore(toMove, list.childNodes[0]);
        updateBorders(list);
      });
    }

    function updateBorders(list) {
      children = list.childNodes;
      for (var i = 0; i < children.length; i++) {
        children[i].style.cssText = "";
        if (children[i].classList.contains('unmovable') || children[i].classList.contains('static')) {
          var currClass = (children[i].classList.contains('unmovable') ? 'unmovable' : 'static');
          if ((i % 2 == 1) && (children[i - 1] != null) && (children[i - 1].classList.contains(currClass)))   //remove left border?
            children[i].style.borderLeftWidth = 0;
          if ((i % 2 == 0) && (children[i + 1] != null) && (children[i + 1].classList.contains(currClass)))   //remove right border?
            children[i].style.borderRightWidth = 0;
          if ((children[i - 2] != null) && (children[i - 2].classList.contains(currClass)))                   //remove top border?
            children[i].style.borderTopWidth = 0;
          if ((children[i + 2] != null) && (children[i + 2].classList.contains(currClass)))                   //remove bottom border?
            children[i].style.borderBottomWidth = 0;
      }
    }
      list.style.height = (list.childNodes.length / 2 * list.childNodes[0].offsetHeight + 8 + "px");
  }

    function indexLi(ol, li) {
      var nodes = ol.children;
      var num = 0;
      for (i = 0; i < nodes.length; i++) {
        if (nodes[i] == li) return num;
        else if (nodes[i].nodeType == 1) num++;
      }
      return -1;
    }

    //Returns the list item that the element most hovers over (or -1 if none)
    //This function assumes that list if absolutely or relatively positioned and elem has the same 'positioned parent' as list
    function overlapList(mouseevent, elem, list, frameOffsetLeft, frameOffsetTop) {
      var frameOffsetLeft = frameOffsetLeft || 0;
      var frameOffsetTop = frameOffsetTop || 0;
      if ((elem.offsetLeft + elem.offsetWidth > frameOffsetLeft + list.offsetLeft) && (elem.offsetLeft < frameOffsetLeft + list.offsetLeft + list.offsetWidth) &&
          (elem.offsetTop + elem.offsetHeight > frameOffsetTop + list.offsetTop) && (elem.offsetTop < frameOffsetTop + list.offsetTop +  list.offsetHeight)) {
        var offset = (mouseevent.pageX < frameOffsetLeft + list.childNodes[0].offsetWidth) ? 0 : 1;
        for (var i = offset; i < list.childNodes.length; i += 2) {
          if (mouseevent.pageY >= frameOffsetTop + list.childNodes[i].offsetTop && mouseevent.pageY < frameOffsetTop + list.childNodes[i].offsetTop + list.childNodes[i].offsetHeight)
            return i;
        }
      }
      return -1;
    }

    function submitNonNumeric() {
      var li = document.getElementById('nonNumericList').childNodes;
      var firstleft = (li[0].classList.contains('static')) ? '0' : '1';
      var pageOrder = new Array();
      var cover = '';
      var index = 0;
      for (var i = 0; i < li.length; i++) {
        if (!(li[i].classList.contains('unmovable') || li[i].classList.contains('static'))) {
          pageOrder[index++] = li[i].querySelector('.pagePath').innerHTML;
          if (li[i].querySelector('.coverBox').checked == true)
            cover = li[i].querySelector('.pagePath').innerHTML;
        } else if (li[i].classList.contains('unmovable') && pageOrder[index - 1] != 'Sorted Pages' || index == 0) {
          pageOrder[index++] = 'Sorted Pages';
        }
      }
      var passablePageOrder = (JSON.stringify(pageOrder));
      document.getElementById('resubmitFirstLeft').value = firstleft;
      var resubmitPageOrder = document.createElement('input');
      resubmitPageOrder.type = 'hidden';
      resubmitPageOrder.name = 'pageOrder';
      resubmitPageOrder.value = passablePageOrder;
      var resubmitCover = document.createElement('input');
      resubmitCover.type = 'hidden';
      resubmitCover.name = 'cover';
      resubmitCover.value = cover;
      document.getElementById('resubmit').appendChild(resubmitPageOrder);
      document.getElementById('resubmit').appendChild(resubmitCover);
      document.getElementById('resubmit').submit();
    }

    function deleteBook(id, title) {
      if (confirm("Are you sure you would like to delete '" + title + "'?")) {
        var form = document.createElement('form');
        form.setAttribute("method", "post");
        form.setAttribute("action", 'archive.php');

        var deleteVal = document.createElement('input');
        deleteVal.setAttribute('type', 'hidden');
        deleteVal.setAttribute('name', 'delete');
        deleteVal.setAttribute('value', id);

        form.appendChild(deleteVal);
        document.body.appendChild(form);
        form.submit();
      }
    }

    function addGet(url, key, val) { return url + ((url.indexOf('?') == -1) ? '?' : '&') + key + '=' + val; }

  </script>
</head>
<body onload='init()'>
  <div id='body'>
    <ul id='tabs'>
      <a href='index.php'>
        <li id='createTab'>Creator</li>
      </a>
      <a href='archive.php'>
        <li id='archiveTab'>Archive</li>
      </a>
    </ul>
    <div id='content'>
      <?php
        if ($action === 'create') $noun = 'Creation';
        else if ($action === 'alter') $noun = 'Edit';

        $resubmit = '<form action="result.php" method="post" id="resubmit">';
        $resubmit .= '<input type="hidden" name="action" value="' . $action . '">';
        $resubmit .= '<input type="hidden" name="title" value="' . $title . '">';
        $resubmit .= '<input type="hidden" name="author" value="' . $author . '">';
        $resubmit .= '<input type="hidden" name="description" value = "' . $description . '">';
        $resubmit .= '<input type="hidden" name="directory" value="' . $directory . '">';
        $resubmit .= '<input type="hidden" name="prefix" value="' . $prefix . '">';
        $resubmit .= '<input type="hidden" name="extension" value="' . $extension . '">';
        $resubmit .= '<input type="hidden" name="page_fix" value="ignore_missing" id="resubmitPageFix">';
        $resubmit .= '<input type="hidden" name="first_left" value="' . $first_left . '" id="resubmitFirstLeft">';
        if ($id) $resubmit .= '<input type="hidden" name="id" value="' . $id . '" id="resubmitId">';
        $resubmit .= '</form>';

        if ($failure) {
          echo "<div id='output' class='failure'>Book $noun Failed!<br/>$msg<br/></div>";
          echo "<button type='button' onclick='goBack()' class='failOption'>Go back to $noun</button><br/>";

          echo $resubmit;

          if ($failure === 2) {

            echo "<hr>";

            echo "Alternatively, resubmit, but this time fix missing pages by:<br/>";
            echo "<button type='button' onclick='insertBlanks()' class='failOption'>Inserting Blank Pages</button>";
            echo "<button type='button' onclick='document.getElementById(\"resubmit\").submit()' class='failOption'>Ignoring Them</button>";


            if ($invalidFiles) {
              echo "<hr>";
              echo "<h2>Files that were unusable</h2>";
              echo "<div id='invalids'>$invalidFiles</div>";
            }
          }
        } else if (!$pageOrder/*$invalids[1][0]*/) {    //There are other pages that don't have the right prefix

            echo $resubmit;
            echo "<script>nonNumericPages(" . ($numPages % 2) . ", '$directory', ['" . implode("','",$invalids[1]) . "']);</script>";

        } else {

          $db = new SQLite3($database);
          $title = $db->escapeString($title);
          $author = $db->escapeString($author);
          $description = $db->escapeString($description);
          $width = $db->escapeString($width);
          $height = $db->escapeString($height);
          $numPages = $db->escapeString($numPages);
          $directory = $db->escapeString($directory);
          $first_left = $db->escapeString($first_left);
          $prefix = $db->escapeString($prefix);
          $extension = $db->escapeString($extension);
          $cover = $db->escapeString($cover);
          switch ($action) {
            case 'create':
              $stmt = "INSERT INTO books (Title, Author, Description, Directory, Prefix, Extension, Width, Height, NumPages, FirstLeft, Cover) VALUES ('$title', '$author', '$description', '$directory', '$prefix', '$extension', $width, $height, $numPages, $first_left, '$cover');";
              $db->exec($stmt);
              $id = $db->lastInsertRowId();
              break;
            case 'alter':
              $id = $db->escapeString($id);
              $stmt = "UPDATE books SET Title='$title', Author='$author', Description='$description', Directory='$directory', Prefix='$prefix', Extension='$extension', Width=$width, Height=$height, NumPages=$numPages, FirstLeft=$first_left, Cover='$cover' WHERE Id=$id;";
              $db->exec($stmt);
              break;
            }
          $db->close();
          $fp = fopen($booksDir . '/JSON/' . $id . '.json', 'w');
          fwrite($fp, json_encode($filenames));
          fclose($fp);

          echo "<div id='output' onclick=\"nonNumericPages(1, '', ['a','b','c','d','e','f']);\" class='success'>Book $noun Successful!<br/></div>";
          echo "<div id='title'>";
          echo "<h1>$title</h1>";
          if ($author) echo "<h2>By: $author</h2>";
          echo "</div>";
          echo "Embed HTML:<br/>";
          echo "<pre id='embed'><code>&ltiframe src='" . $reader . "?bookID=" . $id . "' allowfullscreen webkitallowfullscreen&gt&lt/iframe&gt</code></pre>";

          echo "<button type='button' onclick=\"displayBook($id, '$title')\" id='displayButton'>Display Book</button></div>";
        }
      ?>
    </div>
    <div id='extender'></div>
  </div>
</body>
</html>
