<?php

namespace sylma\core\module;
use \sylma\core, \sylma\storage\fs;

require_once('core/argument/Filed.php');
require_once('core/functions/Path.php');
require_once('Argumented.php');

abstract class Filed extends Argumented {
  
  protected $directory = null;
  protected static $argumentClass = 'sylma\core\argument\Filed';
  
  protected function setArguments($mArguments = null, $bMerge = true) {
    
    if ($mArguments !== null) {
      
      if (is_string($mArguments)) {
        
        if (!$file = $this->getFile($mArguments, true)) {
          
          $this->throwException(txt('Settings not found in @file %s', $mArguments));
        }
        
        parent::setArguments(new static::$argumentClass((string) $file));
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
    
    if (is_string($mDirectory)) $this->directory = core\functions\path\extractDirectory($mDirectory);
    else $this->directory = $mDirectory;
    
    if (!$this->getDirectory()) $this->throwException(txt('Cannot use %s as a directory'), $mDirectory);
  }
  
  /**
   * @return fs\directory The current directory
   */
  protected function getDirectory() {
    
    return $this->directory;
  }
  
  /**
   * Get a file object relative to the current module's directory. (See @method setDirectory())
   *
   * @param string $sPath The relative or absolute path to the file
   * @return fs\file|null The file corresponding to the path given, or NULL if none found
   */
  protected function getFile($sPath, $bDebug = true) {
    
    $fs = \Sylma::getControler('fs');
    
    if (!$directory = $this->getDirectory()) {
      
      $this->throwException(t('No directory defined'), array(), 3);
    }
    
    return $fs->getFile($sPath, $directory, $bDebug);
  }
}


