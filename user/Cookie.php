<?php

class Cookie extends ModuleBase {
  
  protected $sUser = '';
  
  public function __construct() {
    
    $this->setName('cookie');
    $this->setArguments(Sylma::get('users/cookies'));
    
    $this->validate();
  }
  
  public function getUser() {
    
    return $this->sUser;
  }
  
  public function save($sUser, $bRemember = false) {
    
    if ($bRemember) $iExpiration = time() + $this->getArgument('lifetime/normal'); // 14 days
    else $iExpiration = time() + $this->getArgument('lifetime/short'); // 8 hours
    
    $sCookie = $this->generate($sUser, $iExpiration);
    
    if (!setcookie($this->getArgument('name'), $sCookie, $iExpiration, '/') ) {
      
      dspm(t('Impossible de créer le cookie, les paramètres de votre navigateur ne l\'autorise peut-être pas.'), 'error');
    }
    else {
      
      dspm(t('Cookie enregistré.'), 'success');
    }
  }
  
  private function generate($sID, $iExpiration) {
    
    $sKey = hash_hmac( 'md5', $sID . $iExpiration, $this->getArgument('secret-key') );
    $sHash = hash_hmac( 'md5', $sID . $iExpiration, $sKey );
    
    $sCookie = $sID . '|' . $iExpiration . '|' . $sHash;
    
    return $sCookie;
  }
  
  public function kill() {
    
    $_COOKIE = array();
    setcookie($this->getArgument('name'), '', 0); // , '/', '/sylma/modules/users/', time() - 42000
    
    $this->dspm(xt('Cookie %s détruit', new HTML_Strong($this->getArgument('name'))));
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