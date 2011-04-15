<?php

class User {
  
  private $sName = '';
  private $sSID = ''; // session ID
  private $bIsReal = false;
  private $aGroups = array();
  private $aArguments = array();
  
  public function __construct($sName = null, array $aGroups = array(), array $aArguments = array()) {
    
    if ($sName) $this->setReal();
    else $this->setReal(false);
    
    $this->sSID = uniqid();
    $this->setName($sName);
    $this->setGroups($aGroups);
    $this->setArguments($aArguments);
  }
  
  public function login($bRemember = false) {
    
    $_SESSION['user'] = serialize($this);
    if ($bRemember) setcookie('sylma-user-id', $this->sSID, time() + Sylma::get('cookies/lifetime'));
    else setcookie('sylma-user-id', '');
  }
  
  public function logout() {
    
    $this->setReal(false);
    
    $_SESSION = array();
    // if (isset($_COOKIE[session_name()])) setcookie(session_name(), '', time()-42000, '/');
    // session_destroy();
    
    Controler::addMessage(t('Session détruite !'), 'report');
  }
  
  public function isReal() {
    
    return $this->bIsReal;
  }
  
  public function setReal($bValue = true) {
    
    $this->bIsReal = $bValue;
  }
  
  public function getGroups() {
    
    return $this->aGroups;
  }
  
  private function setGroups(array $aGroups) {
    
    if (is_array($aGroups)) $this->aGroups = $aGroups;
  }
  
  public function getDirectory($sPath = '') {
    
    if ($sPath && $sPath[0] == '#') {
      
      switch (substr($sPath, 1)) {
        
        case 'tmp' :
          
          $sPath = Controler::getSettings('@path-temp');
          
        break;
        
        default :
          
          dspm(xt('Unknown token directory %s for user', new HTML_Strong($sPath)), 'file/error');
          
        break;
      }
    }
    
    return Controler::getDirectory($sPath);
  }
  
  public function getName() {
    
    return $this->sName;
  }
  
  private function setName($sName) {
    
    $this->sName = $sName;
  }
  
  public function isName($sName) {
    
    return ($this->getName() == $sName);
  }
  
  public function isMember($mGroup) {
    
    if (is_array($mGroup)) {
      
      foreach ($mGroup as $sGroup) if (!$this->isMember($sGroup)) return false;
      return true;
    }
    else {
      
      return in_array($mGroup, $this->aGroups);
    }
  }
  
  public function setArgument($sKey, $sValue) {
    
    $this->aArgument[$sKey] = $sValue;
  }
  
  public function setArguments(array $aArguments = array()) {
    
    if (is_array($aArguments)) $this->aArguments = $aArguments;
  }
  
  public function getArgument($sKey) {
    
    return isset($this->aArguments[$sKey]) ? $this->aArguments[$sKey] : null;
  }
  
  public function getArguments() {
    
    return $this->aArguments;
  }
  
  public function getMode($sOwner, $sGroup, $sMode, $oOrigin = null) {
    
    $sMode = (string) $sMode;
    if ($oOrigin === null) $oOrigin = new XML_Element('null');
    
    // Validity control of the arguments
    
    if (!$sOwner) {
      
      Controler::addMessage(xt('Sécurité : "owner" inexistant ! %s', $oOrigin), 'xml/warning');
      
    } else if (strlen($sMode) < 3 || !is_numeric($sMode)) {
      
      Controler::addMessage(xt('Sécurité : "mode" invalide ! - %s', $oOrigin), 'xml/warning');
      
    } else if (!strlen($sGroup)) {
      
      Controler::addMessage(xt('Sécurité : "group" inexistant ! %s', $oOrigin), 'xml/warning');
      
    } else {
      
      // everything is ok
      
      $iOwner = intval($sMode{0});
      $iGroup = intval($sMode{1});
      $iPublic = intval($sMode{2});
      
      if ($iOwner > 7 || $iGroup > 7 || $iPublic > 7) {
        
        // check validity of mode
        Controler::addMessage(xt('Sécurité : Attribut "mode" invalide !', $oOrigin), 'xml/warning');
        
      } else {
        
        // now everything is ok
        $iMode = $iPublic;
        
        if ($sOwner == $this->isName($sOwner)) $iMode |= $iOwner;
        if ($this->isMember($sGroup)) $iMode |= $iGroup;
        return $iMode;
      }
    }
    
    return null;
  }
  
  public function parse() {
    
    $sName = $this->getArgument('full-name').' ['.$this->getName().']';
    
    //' ('.implode(', ', $this->getGroups()).')'
    return new HTML_A(SYLMA_PATH_USER_EDIT.$this->getName(), $sName);
  }
}
