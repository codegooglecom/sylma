<?php

namespace sylma\storage\fs\basic\security;
use \sylma\core, \sylma\dom, \sylma\storage\fs;

require_once('storage/fs/security/manager.php');

class Manager implements fs\security\manager {
  
  const FILENAME = 'directory.sml';
  const NS = 'http://www.sylma.org/storage/fs/basic/security';
  
  private $document;
  private $directory;
  
  private $controler;
  
  public function __construct(fs\directory $directory) {
    
    $this->directory = $directory;
    //$sPath = $directory->getFullPath() . '/' . self::FILENAME;
    
    if (\Controler::getUser() && ($file = $directory->getFreeFile(self::FILENAME))) {
      
      $this->document = $file->getFreeDocument();
    }
  }
  
  protected function getControler() {
    
    return $this->directory->getControler();
  }
  
  protected function getDocument() {
    
    return $this->document;
  }
  
  public function getParent() {
    
    return $this->oDirectory;
  }
  
  public function getDirectory() {
    
    $el = null;
    if ($this->getDocument()) $el = $this->getDocument()->getByName('self', $this->getControler()->getNamespace());
    
    return $this->extractRights($el);
  }
  
  public function getPropagation() {
    
    $el = null;
    if ($this->getDocument()) $el = $this->getDocument()->getByName('propagate', $this->getControler()->getNamespace());
    
    return $this->extractRights($el);
  }
  
  public function getFile($sName) {
    
    $el = null;
    if ($this->getDocument()) $el = $this->getDocument()->get('ld:file[@name="'.xmlize($sName).'"]', 'ld', $this->getControler()->getNamespace());
    
    return $this->extractRights($el);
  }
  
  /*
   * Extract and check validity of security datas in element
   * 
   * @param* dom\element $el The element to extract the values from
   * @return an array of validated security parameters
   * - @key user-mode will indicate the user's current rights on the file
   **/
  protected function extractRights(dom\element $el = null) {
    
    $aResult = array();
    
    if ($el && ($el = $el->getByName('security', self::NS))) {
      
      $sOwner = $el->readByName('owner', self::NS);
      $sGroup = $el->readByName('group', self::NS);
      $sMode = $el->readByName('mode', self::NS);
      
      $iMode = \Controler::getUser()->getMode($sOwner, $sGroup, $sMode);
      
      if ($iMode !== null) {
        
        $aResult = array(
          'owner' => $sOwner,
          'group' => $sGroup,
          'mode' => $sMode,
          'user-mode' => $iMode
        );
      }
    }
    
    return $aResult;
  }
}

