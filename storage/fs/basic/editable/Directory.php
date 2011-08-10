<?php

namespace sylma\storage\fs\basic\editable;
use \sylma\dom, \sylma\storage\fs;

require_once('storage/fs/basic/Directory.php');

class Directory extends fs\basic\Directory implements fs\editable\directory {
  
  public function addFreeDocument($sName, dom\document $oDocument) {
    
    $oDocument->saveFree($this, $sName);
  }
  
  public function addDirectory($sName) {
    
    $oDirectory = null;
    
    if (!$oDirectory = $this->getDirectory($sName)) {
      
      if ($sName && $this->checkRights(MODE_WRITE)) {
        
        $sPath = \Sylma::ROOT.$this.'/'.$sName;
        
        mkdir($sPath, SYLMA_DEFAULT_MODE);
        
        unset($this->aDirectories[$sName]);
        $oDirectory = $this->getDirectory($sName);
        
        //dspm(xt('Création du répertoire %s', new HTML_Strong($oDirectory)), 'file/notice');
        
        //} else dspm(xt('Création du répertoire %s impossible', new HTML_Stong($this.$sName)), 'file/error');
      }
    }
    
    return $oDirectory;
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

