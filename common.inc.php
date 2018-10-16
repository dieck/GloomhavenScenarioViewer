<?php

# Common checks:

function existsScene($s) {
  # Check for format (three digit naming)
  if (!preg_match('/^\d\d\d$/', $s)) return 'Format error';

  # Check for location file and scenes directory
  if (!file_exists('locations.txt')) return 'Definition file error';
  if (!is_dir('scenes')) return 'Scenes directory error';
  
  # Read location file
  $found = array();
  $locations = file('locations.txt');
  
  foreach ($locations as $line) {
    $line = trim($line);
    # at this moment, $s is definetely three digits
    if (preg_match('/%\ ('.$s.'\-?\d*)/', $line, $m)) {
      $found[$m[1]] = $m[1];
    } 
  } 
  
  # Check for existing definitions
  if (count($found) == 0) return 'Definition count error';
  
  # Look for image files
  $dh = opendir('scenes');
  if (!$dh) return 'Scenes directory access error';

  # go through directory
  while (($file = readdir($dh)) !== false) {
    # look for picture files
    if (preg_match('/('.$s.'\-?\d*)\ .*\.jpg/', $file, $m)) {
      # if exists in array from locations.txt, remove
      if (in_array($m[1], $found)) {
        unset($found[$m[1]]);
      }
    }

    # found everything we were looking for? Then stop right here.
    if (count($found) == 0) break;
  }

  closedir($dh);    
   
  # return error if we didn't find all graphic files
  if (count($found) > 0) return 'Scenes file error';  
}


function findFilenames($s) {

  $jpgs = array();
  
  $dirs = scandir('scenes');
  foreach ($dirs as $d) {
    if (preg_match('/('.$s.'\-?\d*)\ .*\.jpg/', $d, $m)) {
      $jpgs[$m[1]] = $d;      
    }
  }
  
  return $jpgs; 
}

function findLocations($s) {
  $locations = file('locations.txt');
  
  $inMarker = false;
  $loc = array();

  foreach ($locations as $line) {
    $line = trim($line);
    
    if (substr($line, 0, 1) === '%') $inMarker = false;
    if ($line == '% ' . $s) $inMarker = true;
 
    if (($inMarker) and (preg_match('/^(\d+)\s(\d+)\s(\d+)\s(\d+)\s(#?\w+)\s?(.*)$/', $line, $m))) {
      $loc[] = $line;
    }


  }
 
  return $loc;                          
}

?>