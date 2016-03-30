<?php
  include_once "auth.php";
  include_once "config.php";
  if (!$userAdminAccess)
    header("Location: archive.php");

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
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="Assets/js/bootstrap.min.js"></script>

  <style>
    body { background: linear-gradient(to right,#A3B58E 0%,#C6D6AE 50%,#A3B58E 100%); }
  </style>

  <style>
    #usersWrapper { background-color: #eee; padding: 20px; border-radius: 8px; box-shadow: 2px 2px 8px #888; min-height: 100px; margin-bottom: 30px;}
    .userRow .modifyButt { opacity: 0; }
    .userRow:hover .modifyButt { opacity: .6; }
    .userRow:hover .modifyButt:hover { opacity: 1; cursor: pointer; }

    .userRow .groupsDropDown { width: inherit; }
    .usersWrapper .table-responsive { transition: all 2s; }
  </style>
</head>

<body>
  <!-- NavBar -->
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
          <li>
            <a href="archive.php">Archive</a>
          </li>
          <?php } if ($userAdminAccess) { ?>
          <li class="active">
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
    <div class="col-sm-8 col-sm-offset-2">
      <h1>Admin Panel</h1>

      <div class="row">
        <div id="usersWrapper">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>User</th>
                  <th>Access Group</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $query = "SELECT users.Id, users.cn, groups.name AS AccessName FROM users LEFT JOIN groups ON users.GROUP_ID=groups.Id;";
                  $result = $mysqli->query($query);
                  while ($res = $result->fetch_array(MYSQLI_ASSOC)) {
                    echo "<tr class='userRow'>";
                    echo "  <td class='id'><span class='val'>{$res["Id"]}</span></td>";
                    echo "  <td class='cn'><span class='val'>{$res["cn"]}</span></td>";
                    echo "  <td class='accessName'><span class='val'>{$res["AccessName"]}</span></td>";
                    echo "  <td class='modifyButtons'>";
                    echo "    <span class='modifyButt modifyEdit glyphicon glyphicon-pencil' title='Modify'></span>";
                    echo "    <span class='modifyButt modifyDelete glyphicon glyphicon-remove' title='Delete'></span>";
                    echo "  </td>";
                    echo "</tr>";
                  }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="row">
        <button class="btn btn-default col-sm-4 col-sm-offset-4" id="newUserButt" data-toggle="modal" data-target="#newUserModal">Add a New User</button>
      </div>

      <div class="modal fade" id=newUserModal role="dialog">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">Add a New User</h4>
            </div>
            <form action="admin_ajax.php" method="POST" role="form" id="addUserForm">
              <input type="hidden" name="Action" value="addUser">
              <div class="modal-body">
                <div class="row">
                  <div class="col-sm-6 form-group">
                    <label for="cn">User</label>
                    <input type="text" class="form-control" id="cn" name="cn" required>
                  </div>
                  <div class="col-sm-6 form-group">
                    <label for="Gid">Access Group</label>
                    <select class="form-control" id="Gid" name="Gid">
                      <option value="0">Admin</option>
                      <option value="1">Write</option>
                      <option value="2" selected="selected">Read</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <input type="submit" class="btn btn-primary">
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>

  <?php
    // Pass the values of groups into javascript from mysql
    $query = "SELECT Id, Name FROM groups";
    $result = $mysqli->query($query);
    $groupsInfo = array();
    while ($res = $result->fetch_array(MYSQLI_ASSOC))
      array_push($groupsInfo, $res);

    echo "<script>";
    echo "var groupsInfo = " . json_encode($groupsInfo) . ";";
    echo "</script>";
  ?>

  <script>
    function createDropDownHtml(selectedName) {
      var dropDownHtml = "<select class='form-control groupsDropDown'>";
      for (var i = 0; i < groupsInfo.length; i++) {
        dropDownHtml += "<option value='" + groupsInfo[i]["Id"] + "' ";
        if (selectedName == groupsInfo[i]["Name"])
          dropDownHtml += "selected='selected'";
        dropDownHtml += ">" + groupsInfo[i]["Name"] + "</option>";
      }
      dropDownHtml += "</select>";

      return dropDownHtml;
    }

    $( ".modifyEdit" ).click(function() {
      var userRow = $( this ).closest( ".userRow" );
      var id = userRow.find( ".id" ).text();

      // Make the userRow 'editable'
      var accessNameSlot = userRow.find( ".accessName" );
      var accessName = accessNameSlot.find( ".val" ).text();
      accessNameSlot.find( ".val" ).css("display", "none");

      accessNameSlot.append(createDropDownHtml(accessName));

      var saveCancelHtml = [
        "<div class='saveCancelButtons'>",
          "<div class='col-sm-6'>",
            "<button class='btn btn-primary col-sm-12 saveButt'>Save</button>",
          "</div>",
          "<div class='col-sm-6'>",
            "<button class='btn btn-default col-sm-12 cancelButt'>Cancel</button>",
          "</div>",
        "</div>"
      ].join("");

      var modifyButtonsSlot = userRow.find( ".modifyButtons" );
      modifyButtonsSlot.find( ".modifyButt" ).css("display", "none");

      modifyButtonsSlot.append(saveCancelHtml);
      modifyButtonsSlot.find( ".saveButt" ).click(function() {
        var action = "modifyUserAccess";
        var newGid = accessNameSlot.find( ".groupsDropDown" ).val();
        var newGroupName = accessNameSlot.find( ".groupsDropDown option:selected" ).text();

        $.post("admin_ajax.php", { Uid: id, Gid: newGid, Action: action }, function(error) {
          if (!error)
            accessNameSlot.find( ".val" ).html(newGroupName);
          else
            console.log(error);

          accessNameSlot.find( ".groupsDropDown" ).remove();
          accessNameSlot.find( ".val" ).css("display", "");
          modifyButtonsSlot.find( ".saveCancelButtons" ).remove();
          modifyButtonsSlot.find( ".modifyButt" ).css("display", "");
        });

      });
      modifyButtonsSlot.find( ".cancelButt" ).click(function() {
        accessNameSlot.find( ".groupsDropDown" ).remove();
        accessNameSlot.find( ".val" ).css("display", "");
        modifyButtonsSlot.find( ".saveCancelButtons" ).remove();
        modifyButtonsSlot.find( ".modifyButt" ).css("display", "");
      });
    });

    // Delete Button
    $( ".modifyDelete" ).click(function() {
      var userRow = $( this ).closest( ".userRow" );
      var id = userRow.find( ".id" ).text();
      var cn = userRow.find( ".cn" ).text();


      if (confirm("Delete User: '" + cn + "'?")) {
        var action = "deleteUser";

        $.post("admin_ajax.php", {Uid: id, Action: action}, function(error) {

          if (!error)
            window.location.reload();
          else
            console.log(error);

        });
      }
    });

    $( "#addUserForm" ).submit(function(e) {

      $.post("admin_ajax.php", $( "#addUserForm" ).serialize(), function(error) {

        if (!error)
          window.location.reload();
        else
          console.log(error);

      });

      e.preventDefault();
      return false;
    });
  </script>

</body>
