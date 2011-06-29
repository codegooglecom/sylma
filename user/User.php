<?php

class User extends Module {
  
  private $sUser = '';
  private $bValid = false;
  private $sSID = ''; // session ID
  
  private $aGroups = array();
  private $cookie;
  
  public function __construct($sName = '', array $aGroups = array(), array $aOptions = array()) {
    
    $this->setName($sName);
    $this->setArguments(Sylma::get('modules/users'));
    $this->getArguments()->merge(Sylma::get('users'));
    
    /*if ($aOptions) {
    
    $options = new Arguments($aOptions);
    $this->setOptions($options->getOptions($this->createNode('user')));
    }*/
    
    $this->setGroups($aGroups);
  }
  
  protected function setName($sName) {
    
    return $this->sName = $sName;
  }
  
  public function getName() {
    
    return $this->sName;
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
          $this->isValid(true);
          
          dspm(xt('Authentification %s réussie !', new HTML_Strong($sUser)), 'success');
        }
      }
    }
    
    return $sResult;
  }
  
  protected function isValid($bValid = null) {
    
    if ($bValid !== null) $this->bValid = $bValid;
    return $this->bValid;
  }
  
  public function logout() {
    
    if ($this->getCookie()) $this->getCookie()->kill();
    
    $_SESSION = array();
    // setcookie(session_name(), '', time()-42000, '/');
    
    return new Redirect('/');
  }
  
  /**
   * Load the user, from either cookie, session or profil if authentication has been done
   */
  public function load($bRemember = false) {
    
    $this->loadCookie();
    
    if ($this->isValid() && $this->getName()) {
      
      // just authenticated via @method authenticate()
      
      $this->loadProfile();
      if ($this->getCookie()) $this->getCookie()->save($this->getName(), $bRemember);
    }
    else if (!$this->loadSession()) {
      
      // no session
      
      if ($cookie && ($sUser = $cookie->getUser())) {
        
        // has cookie
        
        $this->setName($sUser);
        $this->loadProfile();
      }
      else {
        
        // no cookie, select the default user
        
        $server = $this->getArgument('server');
        
        if ($_SERVER['REMOTE_ADDR'] == $server->get('ip')) $options = $server;
        else $options = $this->getArgument('anonymouse');
        
        $this->setName($options->get('name'));
        $this->aGroups = $options->query('groups');
        $this->setArguments($options->get('arguments'));
      }
    }
  }
  
  public function getDirectory() {
    
    if ($this->getName()) return Controler::getDirectory($this->getArgument('path').'/'.$this->getName());
    else return null;
  }
  
  protected function loadProfile() {
    
    $this->setDirectory(Controler::getDirectory($this->getArgument('path') . '/' . $this->getName()));
    
    $sProfil = $this->getArgument('profil');
    $dProfil = $this->getDocument($sProfil);
    
    if (!$dProfil || $dProfil->isEmpty()) {
      
      $this->dspm(xt('Cannot load profile in %s', $sProfil), 'error');
    }
    else {
      
      $dProfil->addNode('full-name', $dProfil->readByName('first-name') . ' ' . $dProfil->readByName('last-name'));
      $this->setOptions($dProfil);
    }
    
    $this->loadGroups();
    $this->saveSession();
  }
  
  protected function loadGroups() {
    
    $aGroups = $this->getArgument('authenticated/groups')->query();
    $sUser = $this->getName();
    
    $oAllGroups = $this->getDocument($this->readSettings('groups/@path'), MODE_EXECUTION);
    
    $oGroups = $oAllGroups->query("group[@owner = '$sUser']/@name | group[member = '$sUser']/@name");
    foreach ($oGroups as $oAttribute) $aGroups[] = $oAttribute->getValue();
    
    if (Controler::isAdmin()) $aGroups = array_merge($aGroups, $this->getArgument('root/groups')->query());
    
    $this->setGroups(array_unique($aGroups));
  }
  
  public function getCookie() {
    
    return $this->cookie;
  }
  
  protected function loadCookie() {
    
    $this->cookie = $this->create('cookie');
  }

  protected function loadSession() {

    if ($sSession = array_val($this->getArgument('session/name'), $_SESSION)) {
      
      $aSession = unserialize($sSession);
      
      $this->setName($aSession[0]);
      $this->setGroups($aSession[1]);
      $this->setOptions($aSession[2]);
    }
    
    return $this->getName();
  }
  
  protected function saveSession() {
    
    $_SESSION[$this->getArgument('session/name')] = serialize(array($this->getName(), $this->getGroups(), $this->getOptions()->getDocument()));
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
    }
    else if (strlen($sMode) < 3 || !is_numeric($sMode)) {
      
      Controler::addMessage(xt('Sécurité : "mode" invalide ! - %s', $oOrigin), 'xml/warning');
    }
    else if (!strlen($sGroup)) {
      
      Controler::addMessage(xt('Sécurité : "group" inexistant ! %s', $oOrigin), 'xml/warning');
    }
    else {
      
      // everything is ok
      
      $iOwner = intval($sMode{0});
      $iGroup = intval($sMode{1});
      $iPublic = intval($sMode{2});
      
      if ($iOwner > 7 || $iGroup > 7 || $iPublic > 7) {
        
        // check validity of mode
        Controler::addMessage(xt('Sécurité : Attribut "mode" invalide !', $oOrigin), 'xml/warning');
      }
      else {
        
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
    
    $sName = $this->getName() . ' [' . implode(', ', $this->getGroups()) . ']';
    return new HTML_A($this->getArgument('edit') . '/' . $this->getName(), $sName);
  }
}
