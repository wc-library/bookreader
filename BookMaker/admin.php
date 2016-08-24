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

    #handleUpdate { margin-top: 80px; }
    #handleUpdateProcessingDisplay .book .statusImg { height: 14px; margin-right: 10px; }
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
	<li><a href="https://libportal.wheaton.edu/node/1755">Help</a></li>
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

      <h2>Users</h2>
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
        <button class="btn btn-default col-sm-4 col-sm-offset-4" id="newUserButt" data-toggle="modal" data-target="#newUserModal">
          Add a New User
        </button>
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

      <h2 id="handleUpdate">Update Handles</h2>
      <div class="row">
        <button class="btn btn-warning" id="updateHandlesButt">
          Update All Handles To Point To The Current Server
        </button>
        <div id="handleUpdateProcessingDisplay"></div>
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

        $.ajax({
          type: "POST",
          url: "admin_ajax.php",
          data: { Uid: id, Gid: newGid, Action: action },
          success: function(data) {
            accessNameSlot.find( ".val" ).html(newGroupName);
            accessNameSlot.find( ".groupsDropDown" ).remove();
            accessNameSlot.find( ".val" ).css("display", "");
            modifyButtonsSlot.find( ".saveCancelButtons" ).remove();
            modifyButtonsSlot.find( ".modifyButt" ).css("display", "");
          },
          error: function(jqXHR, error) {
            console.log(error);
          }
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

        $.ajax({
          type: "POST",
          url: "admin_ajax.php",
          data: {Uid: id, Action: action},
          success: function(data) {
            window.location.reload();
          },
          error: function(error) {
            console.log(error);
          }
        });
      }
    });

    $( "#addUserForm" ).submit(function(e) {

      $.ajax({
        type: "POST",
        url: "admin_ajax.php",
        data: $( "#addUserForm" ).serialize(),
        success: function(data) {
          window.location.reload();
        },
        error: function(jqXHR, error) {
          console.log(error);
        }
      });

      e.preventDefault();
      return false;
    });


    function updateHandle(id, handle, success, failure) {
      var action = "updateHandle";
      $.ajax({
        type: "POST",
        url: "admin_ajax.php",
        data: {Action: action, Id: id, Handle: handle},
        success: function(data) {
          if (success)
            success(data);
        },
        error: function(jqXHR, error) {
          if (failure)
            failure(error);
        }
      });
    }

    $( "#updateHandlesButt" ).hover(
      function() {
        $( this ).removeClass("btn-warning").addClass("btn-danger");
      }, function() {
        $( this ).removeClass("btn-danger").addClass("btn-warning");
      }
    ).click( function() {
      $confirmation = "Are you sure you want to update all handles? This will modify every handle stored in the database.";
      if (confirm($confirmation)) {
        var action = "retrieveHandles";

        $.ajax({
          type: "POST",
          url: "admin_ajax.php",
          data: {Action: action},
          dataType: "json",
          success: function(data) {

            var display = $( handleUpdateProcessingDisplay );

            data.forEach(function(book) {
              // Give some sort of display & processing gif for each book
              var newLine = $( "<div>", { class: "book book" + book["Id"] } ).append(
                $( "<img>", { src: "Assets/processing.gif", class: "statusImg" } ),
                $( "<span>" ).text(book["Handle"] + " (" + book["Title"] + ") ==> ")
              );

              display.append(newLine);

              updateHandle(book["Id"], book["Handle"], function(data) {
                newLine.find( ".statusImg" ).attr( "src", "Assets/success.png" );
                newLine.append( $( "<span>", { class: "alert-success" } ).text(data) );
              }, function(error) {
                newLine.find( ".statusImg" ).attr( "src", "Assets/failure.jpg" );
                newLine.append( $( "<span>", { class: 'alert-danger' } ).text(error) );
              });
            });

          },
          error: function(jqXHR, error) {
            console.log(error);
          }
        });
      }

    });
  </script>

</body>
