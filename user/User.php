<?php

class User extends Module {
  
  private $sUser = '';
  private $sSID = ''; // session ID
  
  private $aGroups = array();
  
  public function __construct($sName = '', array $aGroups = array(), array $aOptions = array()) {
    
    $this->setName($sName);
    $this->setArguments(Sylma::get('users'));
    
    if ($aOptions) {
      
      $options = new Arguments($aOptions);
      $this->setOptions($options->getOptions($this->createNode('user')));
    }
    
    
    $this->aGroups = $aGroups;
  }
  
  public function authenticate($sUser, $sPassword) {
    
    $sResult = null;
    
    $this->setSettings(new XML_Document(Controler::getSettings()->get("module[@name='users']")));
    
    if (!$sUser || !$sPassword) {
      
      $this->dspm('Données d\'authentification incomplètes, nom d\'utilisateur ou mot de passe manquants !', 'warning');
    }
    else {
      
      $dUsers = $this->getDocument($this->readSettings('users/@path'), MODE_EXECUTION);
      
      if (!$dUsers || $dUsers->isEmpty()) {
        
        $this->dspm(t('Aucun utilisateur actif sur ce site'), 'action/warning');
      }
      else {
        
        list($spUser, $spPassword) = addQuote(array($sUser, sha1($sPassword)));
        
        if (!$eUser = $dUsers->get("//user[@name = $spUser and @password = $spPassword]")) {
          
          $this->dspm('Nom d\'utilisateur ou mot de passe incorrect !', 'warning');
        }
        else {
          
          // Authentification success !
          
          $sResult = $this->setName($sUser);
        }
      }
    }
    
    return $sResult;
  }
  
  /**
   * Load the user, from either cookie, session or profil if authentication has been done
   */
  public function load($bRemember = false) {
    
    $cookie = $this->loadCookie();
    
    if ($this->getName()) { // just authenticated via @method authenticate()
      
      $this->loadProfile();
      if ($cookie) $cookie->save($bRemember);
    }
    else if (!$this->loadSession()) { // no session
      
      if ($cookie && ($sUser = $cookie->getName())) { // has cookie
        
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
    
    $sProfil = $this->getArgument('profil');
    $dProfil = $this->getDocument($sProfil);
    
    if (!$dProfil || $dProfil->isEmpty()) {
      
      $this->dspm(xt('Cannot load profile in %s', $sProfil), 'error');
    }
    else {
      
      $dProfil->addNode('full-name', $dProfil->getByName('first-name') . ' ' . $dProfil->getByName('last-name'));
      
      $this->setOptions($dProfil);
      
      $this->loadGroups();
      $this->saveSession();
    }
  }
  
  protected function loadGroups() {
    
    $aGroups = Sylma::get('users/authenticated/groups');
    
    $oAllGroups = $this->getDocument($this->readSettings('groups/@path'), MODE_EXECUTION);
    
    $oGroups = $oAllGroups->query("group[@owner = $sUser]/@name | group[member = $sUser]/@name");
    foreach ($oGroups as $oAttribute) $aGroups[] = $oAttribute->getValue();
    
    $this->setGroups($aGroups);
  }
  
  protected function loadCookie() {
    
    return $this->create('cookie');
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
  
  /*** Groups ***/
  
  protected function setGroups(array $aGroups) {
    
    $this->aGroups = $aGroups;
  }
  
  protected function getGroups() {
    
    return $this->aGroups;
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
