<?php
  
class Explorer {
  
  public function addDirectory() {
    
    $mResult = null;
    
    $sDirectory = array_val('directory', $_POST);
    $sName = array_val('name', $_POST);
    
    if (!$sName) dspm(t('Erreur dans la requête !'), 'error');
    else {
      
      if (!$oDirectory = Controler::getDirectory($sDirectory)) dspm(xt('Répertoire %s introuvable', $sDirectory), 'error');
      else if ($mResult = $oDirectory->addDirectory($sName)) dspm(xt('Répertoire %s crée', new HTML_Strong($mResult)), 'success');
      else dspm(xt('Impossible de créer le répertoire, droits insuffisants'), 'error');
    }
    
    return $mResult;
  }
  
  public function updateDirectory() {
    
    $sPath = array_val('resource', $_POST);
    
    $oAction = new XML_Action(extractDirectory(__file__).'/resource.eml');
    $oAction->getPath()->pushIndex(Controler::getDirectory($sPath));
    
    return $oAction;
  }
  
  public function delete() {
    
    $mResult = null;
    
    $bDirectory = array_val('directory', $_POST) ? true : false;
    $sPath      = array_val('resource', $_POST);
    
    if ($bDirectory) { // directory
      
      if (!$oDirectory = Controler::getDirectory($sPath)) dspm(t('Répertoire introuvable'), 'error');
      else $mResult = $oDirectory->delete();
      
    } else { // file
      
      if (!$oFile = Controler::getFile($sPath)) dspm(t('Fichier introuvable'), 'error');
      else $mResult = $oFile->delete(); // update
    }
    
    return $mResult;
  }
  
  public function updateRights() {
    
    $bResult = false;
    
    $bDirectory = array_val('directory', $_POST) ? true : false;
    $sPath      = array_val('resource', $_POST);
    $sMode      = array_val('mode', $_POST);
    $sOwner     = array_val('owner', $_POST);
    $sGroup     = array_val('group', $_POST);
    
    if (!$sPath || !$sOwner || $sMode === '' || $sGroup === '') dspm(t('Erreur dans la requête !'), 'error');
    else {
      
      if ($bDirectory) { // directory
        
        if (!$oDirectory = Controler::getDirectory($sPath)) dspm(txt('Répertoire %s introuvable', $sPath), 'error');
        else if ($bResult = $oDirectory->updateRights($sOwner, $sGroup, $sMode))
            dspm(t('Mise-à-jour des droits du répertoire effectuée'), 'success');
      }
      else { // file
        
        if (!$oFile = Controler::getFile($sPath)) dspm(t('Fichier introuvable'), 'error');
        else if ($bResult = $oFile->updateRights($sOwner, $sGroup, $sMode))
          dspm(t('Mise-à-jour des droits du fichier effectuée'), 'success');
      }
    } 
    
    if (!$bResult) dspm(txt('Impossible de modifier les droits d\'accès', $sPath), 'error');
    
    return booltoint($bResult);
  }
  
  public function updateName() {
    
    $bResult = false;
    
    $bDirectory = array_val('directory', $_POST) ? true : false;
    $sPath      = array_val('resource', $_POST);
    $sName      = array_val('name', $_POST);
    
    if (!$sName) dspm(t('Erreur dans la requête !'), 'error');
    else {
      
      if ($bDirectory) { // directory
        
        if (!$oDirectory = Controler::getDirectory($sPath)) dspm(t('Répertoire introuvable'), 'error');
        else $oResult = $oDirectory->rename($sName);
        
      } else { // file
        
        if (!$oFile = Controler::getFile($sPath)) dspm(t('Fichier introuvable'), 'error');
        else $oResult = $oFile->rename($sName); // update
      }
    }
    
    return (string) $oResult;
  }
}
