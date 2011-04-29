<?php

class XUser extends User {
  
  public function __construct() {
    
    // $this->setSchema($this->getDocument('user.xsd'));
    parent::__construct();
  }
  
  public function logout() {
    
    $this->setReal(false);
    
    $_SESSION = array();
    // if (isset($_COOKIE[session_name()])) setcookie(session_name(), '', time()-42000, '/');
    // session_destroy();
    
    Controler::addMessage(t('Session détruite !'), 'report');
  }
  
  public function isMember() {
    
  }
  
  public function getGroups() {
    
    return $this->aGroups;
  }
  
  protected function setGroups(array $aGroups) {
    
    if (is_array($aGroups)) $this->aGroups = $aGroups;
  }
  
  public function authenticate($sUser, $sPassword) {
    
    if (!$sUser || !$sPassword) {
      
      $this->dspm('Données d\'authentification incomplètes, nom d\'utilisateur ou mot de passe manquants !', 'warning');
    }
    else {
      
      $dUsers = $this->getDocument($this->readSettings('users/@path'), MODE_EXECUTION);
      
      if (!$dUsers || $dUsers->isEmpty()) {
        
        $this->dspm(t('Aucun utilisateur actif sur ce site'), 'action/error');
      }
      else {
        
        list($sUser, $sPassword) = $this->escape($sUser, sha1($sPassword));
        
        if (!$eUser = $dUsers->get("//user[@name = $sUser and @password = $sPassword]")) {
          
          $this->dspm('Nom d\'utilisateur ou mot de passe incorrect !', 'warning');
        }
        else {
          
          // Authentification success !
          
          $this->setName($sUser);
        }
      }
    }
  }
}

