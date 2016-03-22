<?php
  if (file_exists(dirname(__FILE__) . "/config.json"))
    $cconfig = json_decode(file_get_contents(dirname(__FILE__) . "/config.json"));

  $readerPath = "../WheatonReader/";
  if (substr($readerPath, -1) != "/") $readerPath .= "/";
  $reader = $readerPath . "Reader.php";
  $booksDir = $readerPath . "Books/";
  $delimiter = (isset($cconfig) && isset($cconfig->delimiter)) ? $cconfig->delimiter : "_";

  $dbName = (isset($cconfig) && isset($cconfig->dbName)) ? $cconfig->dbName : "";
  $dbUser = (isset($cconfig) && isset($cconfig->dbUser)) ? $cconfig->dbUser : "";
  $dbPass = (isset($cconfig) && isset($cconfig->dbPass)) ? $cconfig->dbPass : "";

  $ldapHost = (isset($cconfig) && isset($cconfig->ldapHost)) ? $cconfig->ldapHost :"";
  $ldapBaseDN = (isset($cconfig) && isset($cconfig->ldapBaseDN)) ? $cconfig->ldapBaseDN : "";
  $ldapBindUser =  (isset($cconfig) && isset($cconfig->ldapBindUser)) ? $cconfig->ldapBindUser : "";
  $ldapBindPass =  (isset($cconfig) && isset($cconfig->ldapBindPass)) ? $cconfig->ldapBindPass : "";

  $handleGenerator = "http://testlib2.wheaton.edu/webapi.php";

  //BookReader, root, password, ldaps://adldaps.wheaton.edu, ou=People,dc=wheaton,dc=edu, NoteswalLdapBind, 4P0DZBUa*cOmh
