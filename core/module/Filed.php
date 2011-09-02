<?php

namespace sylma\core\module;
use \sylma\core, \sylma\storage\fs;

require_once('core/argument/Filed.php');
require_once('core/functions/Paths.php');
require_once('Argumented.php');

abstract class Filed extends Argumented {
  
  protected $directory = null;
  
  protected function setArguments($mArguments = null, $bMerge = true) {
    
    if ($mArguments !== null) {
      
      if (is_string($mArguments)) {
        
        $file = $this->getFile($mArguments, true);
        $this->arguments = new core\argument\Filed((string) $file);
      }
      else {
        
        parent::setArguments($mArguments, $bMerge);
      }
    }
    else {
      
      $this->arguments = null;
    }
    
    return $this->getArguments();
  }
  
  public function create($sName, array $aArguments = array(), $sDirectory = '') {
    
    return parent::create($sName, $aArguments, $this->getDirectory());
  }
  
  /**
   * Set the current directory
   * @param fs\directory|string $mPath An object or string to set as default directory
   */
  protected function setDirectory($mDirectory) {
    
    if (is_string($mDirectory)) $this->directory = extract_directory($mDirectory);
    else $this->directory = $mDirectory;
  }
  
  /**
   * @return fs\directory The current directory
   */
  public function getDirectory() {
    
    return $this->directory;
  }
  
  /**
   * Get a file object relative to the current module's directory. (See @method setDirectory())
   *
   * @param string $sPath The relative or absolute path to the file
   * @return fs\file|null The file corresponding to the path given, or NULL if none found
   */
  protected function getFile($sPath, $bDebug = false) {
    
    if (!$fs = \Sylma::getControler('fs')) {
      
      $this->throwException(txt('File controler not yet loaded. Cannot load file %s', $sPath));
    }
    
    return $fs->getFile($sPath, $this->getDirectory(), $bDebug);
  }
}


