<?php

// ok, let's do this with a little bit more "style" (and hopefully easier to maintain afterwards)


class Scenario {

  /** scenario id */
  private $s;
  
  /** part of location.txt */
  private $locations = array();

  /** name of scenario */
  public $name;
  
  /** images contained in scenario */
  public $images = array();


  public function __construct($scenarioid) {
    // validate it at least looks good...
    if (!$this->validateScenarioID($s)) {
      throw new Exception('Scenario ID not accepted.');
    }
    
    $this->s = $scenarioid;
    $this->readLocations();
    $this->parseImages();
  }
  
  
  /**
   * validate scene id 
   * @return boolean validation result
   */
  function validateScenarioID($s) {
    # Check for format (three digit naming)
    if (!preg_match('/^\d\d\d$/', $s)) return true;
    
    # WTF? A function for this one simple check? 
    # Yes, for now. Maybe there will be extensions and more naming styles. As I said, easier to maintain :)
    return false;
  }


  /** 
   * parse location file and return "interesting" part
   * @param string $s three-digit scenario id (assume validated)
   * @return all lines from % start tag for $s to next start tag (or eof)
   */
  function readLocations() {

    # check for file existence
    if (!file_exists('locations.txt')) {
      throw new Exception('Location definition error.');
    }
    
    $found_start_tag = false;
    $in_tag = false;
    $lines = array();

    $locations = file('locations.txt');
  
    foreach ($locations as $line) {
      $line = trim($line);

      if (preg_match('/^#/', $line)) {
        # ignore comments
        continue;
      }

      # checking for "end tag" before possible find of start: would override in_tag otherwise
      if (preg_match('/^%\ /', $line)) {
        # assume we are no longer in "our" tag. If we were not before, doesn't matter.
        $in_tag = false;
      }

      # at this moment, $this->s should have been parsed...
      # TODO If in the future it will not be only three digits, be aware of Regex special chars
      if (preg_match('/^%\ (' . $this->s . ')\D(.*)$/', $line, $m)) {
        $this->name = $m[2];
        $found_start_tag = true;
        $in_tag = true;
      } 
  
      # as long as we are "in" tag (after % 000, before % 000+1)  
      # look for "F" filename definitions
      if ($in_tag) {
        $this->locations[] = $line;
      }

    } 
    
  }

  /**
   * parse part of locations.txt for images
   */
  function parseImages() {

    $imagename = null;
    $locations = array();

    foreach ($this->locations as $line) {

      # look for "F" filename definitions
      if (preg_match('/^F\ (.*)$/', $line, $m)) {

        # store "previous" image
        if ( (!is_null($imagename)) and (!empty($locations))) {
          $image = new Image($this, $imagename, $locations);
          $this->images[] = $image;
        }          
        
        # starting with new image
        $imagename = $m[1];
        $locations = array();
        
      } else {

        # treat every line that is not a "F file" as location
        
        # well, maybe except empty ones, leave those out here
        if (!empty($line)) $locations[] = $line;
      }
      
    }  
    
    # store image that lasts until locations eod
    if ( (!is_null($imagename)) and (!empty($locations))) {
      $image = new Image($this, $imagename, $locations);
      $this->images[] = $image;
    }
                                        
    
  }


  /** return images content for HTML */
  public function toHTML() {
    $h = "";

    foreach ($this->images as $image) {
      $h .= $image->toHTML();
      $h .= "\n";
    }

    return $h;
  }

  /** return CSS content */
  public function toCSS() {
    $c = '<style>'."\n";

    foreach ($this->images as $image) {
      $c .= $image->toCSS();
      $c .= "\n";
    }

    $c .= '</style>'."\n";
    return $c;
  }

  public function name() {
    return htmlentities($this->name);
  }


} // class Scenario



class Image {

  /** image (file) name */
  public $name;

  /** scenario */
  public $scenario;
  
  /** part of locations.txt */
  private $locations;

  /** parsed parts to render */
  public $positions;
  
  public function __construct($scenario, $imagename, $locations) {
    $this->scenario = $scenario;
    $this->name = $imagename;
    $this->locations = $locations;

    if (!file_exists('scenes/'.$this->name.'.jpg')) {
      throw new Exception('File not found: '.htmlentities($this->name));
    }

    $this->parsePositions();
  }  

  function parsePositions() {
    $this->positions = new Positions($this, $this->locations);
  }


  public function toHTML() {
    $h = '<div class="container">'."\n";
    $h .= '<img src="scenes/'.$this->name.'.jpg" width="1366" style="z-index: 0;" />'."\n";
  
    $h .= $this->positions->toHTML();

    $h .= '</div>'."\n";
    $h .= '<hr/>'."\n";
    return $h;
  }
  
  public function toCSS() {
    return $this->positions->toCSS();
  }
  
} // class Image


class Positions {
  public $positions = array();

  public function __construct($image, $positionlines) {
    foreach ($positionlines as $positionline) {
      if (preg_match('/^(\d+)\s(\d+)\s(\d+)\s(\d+)\s(#?\w+)\s?(.*)$/', $positionline)) {
        $position = new RectPosition($image, $positionline);
        $this->positions[] = $position;
      }
      if (preg_match('/^(\d+),(\d+)\s(\d+),(\d+)\s(\d+),(\d+)\s(#?\w+)\s?(.*)$/', $positionline)) {
        $position = new RotatedPosition($image, $positionline);
        $this->positions[] = $position;
      }
    }
  }
  
  public function toHTML() {
    $h = "";
    foreach ($this->positions as $pos) {
      $h .= $pos->toHTML();
    }
    return $h;
  }

  public function toCSS() {
    $c = "";
    foreach ($this->positions as $pos) {
      $c .= $pos->toCSS();
    }
    return $c;
  }
  
} // class Positions


class RectPosition {

  public $id;
  public $class;
  public $left;
  public $top;
  public $width;
  public $height;
  public $color;
  public $name;
  public $label;
  
  private $image;

  public function __construct($image, $positionline) {
  
    $this->image = $image;

    if (preg_match('/^(\d+)\s(\d+)\s(\d+)\s(\d+)\s(#?\w+)\s?(.*)$/', $positionline, $m)) {
       $this->id = "id_".sha1($image->name.'/'.$m[0]);
       $this->left = $m[1];
       $this->top = $m[2];
       $this->width = $m[3];
       $this->height = $m[4];
       $this->color = $m[5];

       $naming = $m[6];

       # look for hard mask
       if (preg_match('/^\!\s+?(.*)?$/', $naming, $n)) {
         $this->name = '!';
         $this->label = $n[1];
         $this->class = "cl_".sha1($image->name.'/'.$m[0]);
         
       # look for invisible tags
       } else if (preg_match('/^\[(.*?)\]$/', $naming, $n)) {
         $this->name = $n[0];
         $this->label = null;
         $this->class = "cl_".sha1($image->name . $n[1]);

       # look for normal tags
       } else if (preg_match('/^(.*)$/', $naming, $n)) {
         $this->name = $n[0];
         $this->label = $n[1];
         $this->class = "cl_".sha1($image->name . $n[1]);
       
       # ok, has no tag at all
       } else {
         $this->name = null;
         $this->label = null;
         # use full positionline to build class
         $this->class = "cl_".sha1($image->name.'/'.$m[0]);
       }

     } else {
  
       throw new Exception('Position format error: ' . htmlentities($positionline));
     }

  }

  public function toHTML() {
    $h = "";
    $h .= '<div class="step" id="'.$this->id.'">';
    $h .= '<div class="stepi '.$this->class.'" id="'.$this->id.'_i" ';

    if ($this->name != '!') {    
      # ! can not be toggled: used to mask unused scenarios
      $h .= 'onClick="toggleHide(this, \''.$this->class.'\');"';
    }

    $h .= '>'."\n";
    $h .= '<p>'.htmlentities($this->label).'</p>'."\n";
    $h .= '</div></div>'."\n";
    return $h;
  }

  public function toCSS() {
    $c = "";
    $c .= '#'.$this->id.' { left: '.$this->left.'; top: '.$this->top.'; width: '.$this->width.'; height: '.$this->height.'; }'."\n";
    $c .= '#'.$this->id.'_i { width: '.$this->width.'; height: '.$this->height.'; background-color: '.$this->color.'; }'."\n";
    return $c;
  }

} // class Position


class RotatedPosition {
# Rotation? Yes, Rotation. I have troubles getting polygon shapes to work ;)

  public $id;
  public $class;

  private $ax; private $ay;
  private $bx; private $by;
  private $cx; private $cy;

  public $color;
  public $name;
  public $label;
  public $rotation;
  
  private $mx; private $my;
  private $left;  private $top;
  private $width; private $height;
  
  private $image;


  public function __construct($image, $positionline) {
  
    $this->image = $image;

    if (preg_match('/^(\d+),(\d+)\s(\d+),(\d+)\s(\d+),(\d+)\s(#?\w+)\s?(.*)$/', $positionline, $m)) {
    
       $this->id = "id_".sha1($image->name.'/'.$m[0]);
       
       $this->ax = $m[1]; $this->ay = $m[2];
       $this->bx = $m[3]; $this->by = $m[4];
       $this->cx = $m[5]; $this->cy = $m[6];
       
       $this->color = $m[7];

       $naming = $m[8];

       # look for hard mask
       if (preg_match('/^\!\s+?(.*)?$/', $naming, $n)) {
         $this->name = '!';
         $this->label = $n[1];
         $this->class = "cl_".sha1($image->name.'/'.$m[0]);
         
       # look for invisible tags
       } else if (preg_match('/^\[(.*?)\]$/', $naming, $n)) {
         $this->name = $n[0];
         $this->label = null;
         $this->class = "cl_".sha1($image->name . $n[1]);

       # look for normal tags
       } else if (preg_match('/^(.*)$/', $naming, $n)) {
         $this->name = $n[0];
         $this->label = $n[1];
         $this->class = "cl_".sha1($image->name . $n[1]);
       
       # ok, has no tag at all
       } else {
         $this->name = null;
         $this->label = null;
         # use full positionline to build class
         $this->class = "cl_".sha1($image->name.'/'.$m[0]);
       }

     } else {
  
       throw new Exception('Position format error: ' . htmlentities($positionline));
     }

     $this->math();

  }

  private function math() {
    $tanAlpha = ($this->bx - $this->ax) / ($this->ay - $this->by);
    $atan = atan($tanAlpha);
    $deg = round(rad2deg($atan) - 90);
    
    $this->rotation = $deg;
    
    $mx = abs($this->cx - $this->ax)/2 + min($this->ax, $this->cx);
    $my = abs($this->cy - $this->ay)/2 + min($this->ay, $this->cy);

    $AB = round( sqrt( pow(($this->bx - $this->ax),2) + pow(($this->by - $this->ay),2)  ) );
    $BC = round( sqrt( pow(($this->cx - $this->bx),2) + pow(($this->cy - $this->by),2)  ) );

    $this->left = round($mx - $AB/2);
    $this->top = round($my - $BC/2);
    $this->width = $AB;
    $this->height = $BC;
  }

  public function toHTML() {
    $h = "";
    $h .= '<div class="step" id="'.$this->id.'">';
    $h .= '<div class="stepi '.$this->class.'" id="'.$this->id.'_i" ';

    if ($this->name != '!') {    
      # ! can not be toggled: used to mask unused scenarios
      $h .= 'onClick="toggleHide(this, \''.$this->class.'\');"';
    }

    $h .= '>'."\n";
    $h .= '<p>'.htmlentities($this->label).'</p>'."\n";
    $h .= '</div></div>'."\n";
    return $h;
  }

  public function toCSS() {
    $c = "";
    $c .= '#'.$this->id.' { left: '.$this->left.'; top: '.$this->top.'; width: '.$this->width.'; height: '.$this->height.'; ';
    $c .= 'moz-transform: rotate('.$this->rotation.'deg); ';
    $c .= '-ms-transform: rotate('.$this->rotation.'deg); ';
    $c .= '-o-transform: rotate('.$this->rotation.'deg); '; 
    $c .= '-webkit-transform: rotate('.$this->rotation.'deg); ';
    $c .= 'transform: rotate('.$this->rotation.'deg); ';
    $c .= '}'."\n";
    $c .= '#'.$this->id.'_i { width: '.$this->width.'; height: '.$this->height.'; background-color: '.$this->color.'; }'."\n";
    return $c;
  }

} // class Position

?>