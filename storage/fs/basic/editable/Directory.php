<?php

namespace sylma\storage\fs\basic\editable;
use \sylma\dom, \sylma\storage\fs;

require_once(dirname(__dir__) . '/Directory.php');
require_once(dirname(dirname(__dir__)) . '/editable/directory.php');

class Directory extends fs\basic\Directory implements fs\editable\directory {
  
  public function addFreeDocument($sName, dom\document $oDocument) {
    
    $oDocument->saveFree($this, $sName);
  }
  
  /**
   * Add or get a directory depends it exists or not
   * 
   * @param type $sName
   * @return fs\directory
   */
  public function addDirectory($sName) {
    
    $result = null;
    
    if (!$sName) {
      
      $this->throwException(t('No name defined for new directory'));
    }
    
    if (!$result = $this->getDirectory($sName)) {
      
      if (!$this->checkRights(MODE_WRITE)) {
        
        $this->throwException(t('You have no rights to add a directory into this directory'));
      }
      
      $sPath = \Sylma::ROOT.$this.'/'.$sName;
      
      if (!mkdir($sPath, SYLMA_DEFAULT_MODE)) {
        
        $this->throwException(txt('Cannot create directory called %s', $sName));
      }
      
      $result = $this->updateDirectory($sName);
    }
    
    return $result;
  }
  
  public function updateRights($sOwner, $sGroup, $sMode) {
    
    if ($this->checkRightsArguments($sOwner, $sGroup, $sMode)) {
      
      return $this->getSettings()->updateDirectory($sOwner, $sGroup, $sMode);
    }
    
    return false;
  }
  
  public function rename($sNewName) {
    
    $oResult = null;
    
    if ($this->checkRights(\Sylma::MODE_WRITE)) {
      
      if (rename($this->getRealPath(), $this->getParent()->getRealPath().'/'.$sNewName)) {
        
        $oResult = $this->getParent()->updateDirectory($sNewName);
        $this->log('Resource renommé');
        
      } else \Controler::addMessage(t('Impossible de renommer le répertoire !'), 'warning');
    }
    
    return $oResult;
  }
  
  public function delete($bDeleteChildren = false) {
    
    $bResult = false;
    
    if ($this->checkRights(\Sylma::MODE_WRITE)) {
      
      if ($bDeleteChildren) {
        
        if ($this === $this->getControler()->getDirectory()) dspm('Impossible de supprimer le répertoire principal !', 'file/error');
        else {
          
          $this->browse(array(), array(), 1);
          
          foreach ($this->aFiles as $oFile) if ($oFile) $oFile->delete();
          foreach ($this->aDirectories as $oDirectory) $oDirectory->delete(true);
        }
      }
      
      $bResult = rmdir(\Sylma::ROOT . $this);
      
      $this->getParent()->updateDirectory($this->getName());
    }
    
    return $bResult;
  }
}

