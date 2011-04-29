<?php

class Cookie extends XDB_Module {
  
  protected $sUser = '';
  
  public function __construct() {
    
    $this->setName('cookie');
    $this->setArguments(Sylma::get('users/cookies'));
    
    $this->validate();
  }
  
  public function getUser() {
    
    return $this->sUser;
  }
  
  public function save($bRemember = false) {
    
    if ($bRemember) $iExpiration = time() + $this->getArgument('lifetime/normal'); // 14 days
    else $iExpiration = time() + $this->getArgument('lifetime/short'); // 8 hours
    
    $sCookie = $this->generate($user->getName(), $iExpiration);
    
    if (!setcookie($this->getArgument('name'), $sCookie, $iExpiration) ) {
      
      dspm(t('Impossible de créer le cookie, les paramètres de votre navigateur ne l\'autorise peut-être pas.'), 'error');
    }
  }
  
  private function generate($sID, $iExpiration) {
    
    $sKey = hash_hmac( 'md5', $sID . $iExpiration, $this->getArgument('secret-key') );
    $sHash = hash_hmac( 'md5', $sID . $iExpiration, $sKey );
    
    $sCookie = $sID . '|' . $iExpiration . '|' . $sHash;
    
    return $sCookie;
  }
  
  public function validate() {
    
    if ($sCookie = array_val($this->getArgument('name'), $_COOKIE)) {
      
      list($sID, $iExpiration, $sHmac) = explode('|', $sCookie);
      
      if ($iExpiration > time()) {
        
        $sKey = hash_hmac('md5', $sID . $iExpiration, $this->getArgument('secret-key'));
        $sHash = hash_hmac('md5', $sID . $iExpiration, $sKey);
        
        if ($sHmac == $sHash) $this->sUser = $sID;
      }
    }
  }
}