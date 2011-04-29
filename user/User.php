<?php

class User extends Module {
  
  private $sName = '';
  private $sSID = ''; // session ID
  private $bIsReal = false;
  private $aGroups = array();
  private $aArguments = array();
  
  public function __construct($sName = '', array $aGroups = array(), array $aOptions = array()) {
    
    $this->setName($sName);
    $this->setArguments(Sylma::get('users'));
    
    if ($aOptions) {
      
      $options = new Arguments($aOptions);
      $this->setOptions($options->getOptions($this->createNode('user')));
    }
    
    $this->aGroups = $aGroups;
  }
  
  public function load($bRemember = false) {
    
    $cookie = $this->loadCookie();
    
    if ($this->getName()) { // just authenticated via @method authenticate()
      
      $this->loadProfile();
      if ($cookie) $cookie->save($bRemember);
    }
    else if (!$this->loadSession()) { // no session
      
      if ($cookie && ($sUser = $cookie->getUser())) { // has cookie
        
        $this->setName($sUser);
        $this->loadProfile();
      }
      else { // no cookie, neither session
        
        $aServer = $this->getArgument('server');
        
        if ($_SERVER['REMOTE_ADDR'] == $aServer['ip']) $aOptions = $aServer; // is server ?
        else $aOptions = $this->getArgument('anonymouse'); // is anonymouse
        
        $this->setName($aOptions['name']);
        $this->aGroups = $aOptions['groups'];
        $this->setArguments($aOptions['arguments']);
      }
    }
  }
  
  protected function loadProfile() {
    
    $this->setDirectory(Controler::getDirectory($this->getArgument('path') . '/' . $this->getName()));
    
    $profil = new Options($this->getDocument($this->getArgument('profil')));
    $profil->set('full-name', $profil->get('first-name') . ' ' . $profil->get('last-name'));
    
    $this->setOptions($profil);
    $this->saveSession();
  }
  
  protected function loadCookie() {
    
    return $this->create('cookie');
  }
  
  protected function saveCookie() {
    
    $_SESSION[$this->getArgument('session/name')] = array($this->getName(), $this->getArguments());
  }
  
  protected function loadSession() {
    
    if ($sSession = array_val($this->getArgument('session/name'), $_SESSION)) {
      
      $aSession = unserialize($sSession);
      
      $this->setName($aSession[0]);
      $this->setOptions($aSession[1]);
    }
    
    return $this->getName();
  }
  
  protected function saveSession() {
    
    $_SESSION[$this->getArgument('session/name')] = serialize(array($this->getName(), $this->getOptions()));
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
        
        if ($sOwner == $this->getName()) $iMode |= $iOwner;
        if ($this->isMember($sGroup)) $iMode |= $iGroup;
        return $iMode;
      }
    }
    
    return null;
  }
  
  public function parse() {
    
    $sName = $this->getOption('full-name').' ['.$this->getName().']';
    return new HTML_A($this->getArgument('edit') . $this->getName(), $sName);
  }
}
