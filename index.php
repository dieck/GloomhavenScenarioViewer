<?php 
  
  require_once("classes.inc.php");

  if (!$_REQUEST['scene']) {
    header("Location: scene.php");
    die('<a href="scene.php">Set Scenario</a>');
  } else {
  
    $Scenario = new Scenario($_REQUEST['scene']);
    
  }
?>
<html>

<head>
  <title>Gloomhaven Scenario Viewer - <?=$Scenario->name();?></title>
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
  print $Scenario->toCSS();
?>

<script>
function toggleHide(e, cl, cl2) {
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
    
    // second class to show: Marker clicks to show Text
    if (typeof cl2 !== "undefined") {
      var cles = document.getElementsByClassName(cl2);
      for (var i = 0; i < cles.length; i++) {
          cles[i].style.opacity = 0;
          cles[i].parentElement.style.borderStyle = "dotted";
      } 
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

    // second class to show: Marker clicks to show Text
    if (typeof cl2 !== "undefined") {
      var cles = document.getElementsByClassName(cl2);
      for (var i = 0; i < cles.length; i++) {
          cles[i].style.opacity = 1;
          cles[i].parentElement.style.borderStyle = "none";
      } 
    }
  }
  
}
</script>

<body>

<div class="heading">
<h1><?=$Scenario->name();?></h1>
<a href="scene.php">Anderes Szenario</a> 
</div>

<?php
  print $Scenario->toHTML();
?>

</body>

</html>
