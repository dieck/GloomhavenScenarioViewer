<?php
  require_once("common.inc.php");

  if ($_REQUEST['scene']) {
    $sceneerror = existsScene($_REQUEST['scene']);
    if ($sceneerror) die($sceneerror);
    
    # At this point, $_REQUEST['scene'] is definitely 3 digits
    header("Location: index.php?scene=".$_REQUEST['scene']);
    die('<a href="index.php?scene='.$_REQUEST['scene'].'">Start</a>');
  }

?>
<html>
<head>
  <title>Gloomhaven Scenario Viewer - Choose Scene</title>
</head>

<body>
  <p class="header">Gloomhaven Scenario Viewer - Choose Scene</p>
  
  <div class="list">
    <ul>
<?php

  $d = scandir("scenes");
  foreach ($d as $f) {
    if (preg_match('/^(\d\d\d)\ (.*)\.jpg$/', $f, $m)) {
      $num = $m[1];
      $txt = htmlentities($m[2]);
      print '<li><a href="?scene='.$num.'">'.$num.': '.$txt.'</a></li>'."\n";
    }
  }

?>  
    </ul>
  </div>
</body>
</html>