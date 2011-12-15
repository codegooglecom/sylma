<?php

namespace sylma\parser\action;
use \sylma\core, \sylma\dom, \sylma\parser, \sylma\storage\fs;

require_once('parser/action.php');
require_once('core/module/Controled.php');

abstract class Basic extends core\module\Controled {
  
  const FILE_CONTROLER = 'fs';
  private $sName;
  
  public function setName($sName) {
    
    $this->sName = $sName;
  }
  
  public function getName($sName) {
    
    return $this->sName;
  }
  
  /**
   *
   * @param string $sPath An absolute path to the file
   * @param integer $iMode R/W/E Access mode
   * @param string $sOutput Type of content expected
   *   - 'xml'
   *   - 'txt'
   * @return dom\handler|string The content of the file 
   */
  protected function parseFile($sPath, $iMode = \Sylma::MODE_READ, $sOutput = 'xml') {
    
    $mResult = null;
    
    $fs = \Sylma::getControler(self::FILE_CONTROLER);
    
    if ($file = $fs->getFile($path, $this->getFile()->getParent())) {
      
      switch ($sOutput) {
        
        case 'txt' :
          
          $mResult = $file->read();
          
        break;
        
        case 'xml' :
          
          $mResult = $file->getDocument($iMode);
          
        break;
        
        default :
          
          $this->getControler()->throwException(txt('Unknown output file content type : %s', $sOutput));
      }
    }
    
    return $mResult;
    
  }
}
