<?php require_once("common.inc.php");

  if (!$_REQUEST['scene']) {
    header("Location: scene.php");
    die('<a href="scene.php">Set Scenario</a>');
  } else {
    $sceneerror = existsScene($_REQUEST['scene']);
    if ($sceneerror) {
      header("Location: scene.php");
      die('<a href="scene.php">Set Scenario</a>');
    }
  }
?>
<html>

<head>
  <title>Gloomhaven Scenario Viewer</title>

<style>

.container {
    position: relative;
}

.step {
    position: absolute;
    z-index: 2;
    border-color: rgba(102, 102, 51, 0.5);
}

.stepi {
    position: absolute;
    background-color: #ff0000;
    z-index: 1;
}

.stepi p {
    text-align: center;
    position: relative;
    top: 25%;
    -ms-transform: translateY(-25%);
    -webkit-transform: translateY(-25%);
    transform: translateY(-25%);
    font-size: 25px;
	font-family: Verdana, sans-serif;
}

</style>

<?php

  $images = findImages($_REQUEST['scene']);

  print '<style>'."\n";
  
  foreach ($images as $id => $imgfile) {

   // # Left Top Width Height Color Shown Text
   // 50 50 200 150 red Step 1

   $positions = findOverlays($id);
   foreach ($positions as $pos) {
     $pos = trim($pos);
     // ignored # lines
     if (substr($pos, 0, 1) == '#') continue;
     
     if (preg_match('/^(\d+)\s(\d+)\s(\d+)\s(\d+)\s(#?\w+)\s?(.*)$/', $pos, $m)) {
       $id = "id_".sha1($page.'/'.$m[0]);
       $left = $m[1];
       $top = $m[2];
       $width = $m[3];
       $height = $m[4];
       $color = $m[5];
       $name = $m[6];

       print '#'.$id.' { left: '.$left.'; top: '.$top.'; width: '.$width.'; height: '.$height.'; }'."\n";
       print '#'.$id.'_i { width: '.$width.'; height: '.$height.'; background-color: '.$color.'; }'."\n";

     }
     
   } // foreach $pos
   
   } // foreach $page

   print '</style>'."\n";

?>


<script>
function toggleHide(e, cl) {
  p = e.parentElement;
  o = e.style.opacity;
  
  if ((o == "") || (o != 0)) {	
    e.style.opacity = 0;
    p.style.borderStyle = "dotted";

    // all same named elements    
    var cles = document.getElementsByClassName(cl);
    for (var i = 0; i < cles.length; i++) {
        cles[i].style.opacity = 0;
        cles[i].parentElement.style.borderStyle = "dotted";
    } 
    
  } else {
    e.style.opacity = 1;
    p.style.borderStyle = "none";

    // all same named elements    
    var cles = document.getElementsByClassName(cl);
    for (var i = 0; i < cles.length; i++) {
        cles[i].style.opacity = 1;
        cles[i].parentElement.style.borderStyle = "none";
    } 

  }
  
}
</script>

<body>

<div class="heading">
<?php
      print '<h1>GH</h1>'."\n";
?>
<a href="scene.php">Anderes Szenario</a> 
</div>

<?php
  $images = findImages($_REQUEST['scene']);
 
  foreach ($images as $id => $line) {
    
    print '<div class="container">'."\n";

    $imgfile = findFilename($id);

    print '<img src="scenes/'.$imgfile.'" width="1366" style="z-index: 0;" />'."\n";
   
   // # Left Top Width Height Color Shown Text
   // 50 50 200 150 red Step 1

   $positions = findOverlays($id);
   foreach ($positions as $pos) {
     $pos = trim($pos);
     // ignored # lines
     if (substr($pos, 0, 1) == '#') continue;
     
     if (preg_match('/^(\d+)\s(\d+)\s(\d+)\s(\d+)\s(#?\w+)\s?(.*)$/', $pos, $m)) {
       $id = "id_".sha1($page.'/'.$m[0]);
       $left = $m[1];
       $top = $m[2];
       $width = $m[3];
       $height = $m[4];
       $color = $m[5];
       $name = $m[6];

       if ($name != "") {
         preg_match('/^\[?(.*?)\]?$/', $name, $n);
         $cl = "cl_".sha1($page . $n[1]);
       } else {
         $cl = "cl_".sha1($page.'/'.$m[0]);
       }

       print '<div class="step" id="'.$id.'"><div class="stepi '.$cl.'" id="'.$id.'_i" onClick="toggleHide(this, \''.$cl.'\');">'."\n";
       print '<p>';
       $showName = preg_replace('/\[.*?\]/', null, $name);
       print htmlentities($showName);
       print '</p>'."\n";
       print '</div></div>'."\n";
     }
     
   } // foreach $pos
   
   print '</div>'."\n";

   print '<hr/>'."\n";
   
  } // foreach $page

?>

</body>

</html>
