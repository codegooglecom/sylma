<?php

class XML_Resource {
  
  protected $aRights = array();
  
  protected $sPath = '';
  protected $sName = '';
  protected $sFullPath = '';
  protected $oParent = null;
  
  private $bExist = false;
  private $bSecured = false;
  
  public function doExist($bExist = null) {
    
    if ($bExist !== null) $this->bExist = $bExist;
    return $this->bExist;
  }
  
  public function getOwner() {
    
    return $this->aRights['owner'];
  }
  
  public function getGroup() {
    
    return $this->aRights['group'];
  }
  
  public function getMode() {
    
    return $this->aRights['mode'];
  }
  
  public function getName() {
    
    return $this->sName;
  }
  
  public function isOwner() {
    
    return Controler::getUser()->getName() == $this->getOwner();
  }
  
  public function getFullPath() {
    
    return $this->sFullPath;
  }
  
  public function getParents($oTarget = null) {
    
    $oParent = $this;
    $aResult = array();
    
    while (($oParent = $oParent->getParent()) && (!$oTarget || ($oParent != $oTarget))) {
      
      array_unshift($aResult, $oParent);
    }
    
    if ($oTarget && !$oParent) return null;
    else return $aResult;
  }
  
  public function getParent() {
    
    return $this->oParent;
  }
  
  protected function getUserMode() {
    
    return $this->aRights['user-mode'];
  }
  
  protected function isSecured($bSecured = null) {
    
    if ($bSecured === null) return $this->bSecured;
    else $this->bSecured = $bSecured;
  }
  
  protected function getRights() {
    
    return $this->aRights;
  }
  
  /*
   * Extract and check validity of parameter from an XML_Element
   * @return an array of validated security parameters, with the user-mode for the result of
   * rights of the user on rights on the file
   **/
  
  protected function extractRights($oElement = null) {
    
    if ($oElement && ($oSecurity = $oElement->getByName('security', SYLMA_NS_SECURITY))) {
      
      if (Controler::useStatut('file/report')) Controler::addMessage(xt('Ressource "%s" sécurisée ', new HTML_Strong($this->getFullPath())), 'file/report');
      
      $sOwner = $oSecurity->readByName('owner', SYLMA_NS_SECURITY);
      $sGroup = $oSecurity->readByName('group', SYLMA_NS_SECURITY);
      $sMode = $oSecurity->readByName('mode', SYLMA_NS_SECURITY);
      
      $iMode = Controler::getUser()->getMode($sOwner, $sGroup, $sMode, new HTML_Strong((string) $this));
      
      if ($iMode !== null) return array('owner' => $sOwner, 'group' => $sGroup, 'mode' => $sMode, 'user-mode' => $iMode);
    }
    
    return array();
  }
  
  /**
   * Put all rights into object
   * @param array|XML_Element|null $mRights Rights to use
   * @return array Rights used
   */
  protected function setRights($mRights = null) {
    
    if (is_array($mRights)) $aRights = $mRights;
    else {
      
      $aDefaultRights = array(
        'owner' => $this->getOwner(),
        'group' => $this->getGroup(),
        'mode' => $this->getMode(),
        'user-mode' => $this->getUserMode());
      
      if (Controler::getUser())
        $aDefaultRights['user-mode'] = Controler::getUser()->getMode(
          $aDefaultRights['owner'], $aDefaultRights['group'], $aDefaultRights['mode']);
      
      if (is_object($mRights)) {
        
        if (!$aRights = $this->extractRights($mRights)) $aRights = $aDefaultRights;
        
      } else $aRights = $aDefaultRights;
    }
    
    $this->aRights = $aRights;
    $this->isSecured(true);
    
    return $aRights;
  }
  
  /**
   * Check rights arguments for update in updateRights
   */
  protected function checkRightsArguments($sOwner, $sGroup, $sMode) {
    
    if ($this->isOwner()) {
      
      $bOwner = $sOwner !== $this->getOwner();
      $bGroup = $sGroup !== $this->getGroup();
      $bMode  = $sMode !== $this->getMode();
      
      if ($bOwner || $bGroup || $bMode) {
        
        $bResult = true;
        
        // Check validity
        
        if ($bOwner) {
          
          $bOwner = false;
          dspm(t('Changement d\'utilisateur impossible pour le moment'), 'file/warning');
        }
        
        if ($bGroup && !Controler::getUser()->isMember($sGroup)) {
          
          $bResult = false;
          dspm(t('Vous n\'avez pas les droits sur ce groupe ou il n\'existe pas !'), 'file/warning');
        }
        
        $iMode = Controler::getUser()->getMode($sOwner, $sGroup, $sMode);
        
        if ($bMode && $iMode === null) {
          
          $bResult = false;
          dspm(t('Les arguments pour la mise-à-jour ne sont pas valides'), 'file/warning');
        }
        
        if ($bMode && !($iMode & MODE_READ)) {
          
          $bResult = false;
          dspm(t('Vous ne pouvez pas retirer tous les droits de lecture'), 'file/warning');
        }
        
        // all datas are ok, or not modified
        
        if ($bResult && ($bOwner || $bGroup || $bMode)) return true;
      }
      
    } else dspm('Vous n\'avez pas les droits pour faire des modifications !', 'file/warning');
    
    return false;
  }
  
  public function __toString() {
    
    return $this->getFullPath();
  }
}


