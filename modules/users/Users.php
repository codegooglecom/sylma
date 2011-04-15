<?php

class Users extends DBX_Module {
  
  public function __construct(XML_Directory $oDirectory, XML_Document $oSchema, XML_Document $oOptions) {
    
    $this->setName('users');
    parent::__construct($oDirectory, $oSchema, $oOptions);
  }
  
  public function connection(Redirect $oRedirect) {
    
    $oTemplate = $this->getTemplate('form/index.xsl');
    if (isset($_SERVER['HTTPS'])) $oTemplate->setParameter('https', $_SERVER['HTTPS']);
    
    return $this->add(
      $oRedirect,
      $this->setFormID(),
      $oTemplate,
      $this->readOption('add-do-path', false),
      $this->getTemplateExtension());
  }
  
  public function login(Redirect $oRedirect) {
    
    $oValues = $oRedirect;
    $bError = false;
    
    if (!$oPost = $oRedirect->getDocument('post')) {
      
      $this->dspm('Aucune données d\'authentification !', 'warning');
    }
    else {
      
      $sUser = $oPost->readByName('name');
      $sPassword = $oPost->readByName('password');
      
      $bRemember = (bool) $oPost->readByName('remember');
      
      if (!$sUser || !$sPassword) {
        
        $this->dspm('Données d\'authentification incomplètes, nom d\'utilisateur ou mot de passe manquants !', 'warning');
      }
      else {
        
        $oUsers = $this->getDocument($this->readSettings('users/@path'), MODE_EXECUTION);
        
        if (!$oUsers || $oUsers->isEmpty()) {
          
          $this->dspm(t('Aucun utilisateur actif sur ce site'), 'action/error');
        }
        else {
          
          $sUser = addQuote($sUser);
          $sPassword = addQuote(sha1($sPassword));
          
          if (!$eUser = $oUsers->get("//user[@name = $sUser and @password = $sPassword]")) {
            
            $this->dspm('Nom d\'utilisateur ou mot de passe incorrect !', 'warning');
          }
          else {
            
            // Authentification réussie !
            $this->setUser($oRedirect, $eUser, $bRemember);
          }
        }
      }
    }
    
    return $oRedirect;
  }
  
  private function setUser(Redirect $oRedirect, XML_Element $eUser, $bRemember = false) {
    
    $sUser = $eUser->getAttribute('name');
    $sRedirect = $this->readOption('redirect', '/', false);
    
    // Ajout des rôles
    
    $aGroups = Sylma::get('users/authenticated/groups');
    
    $oAllGroups = $this->getDocument($this->readSettings('groups/@path'), MODE_EXECUTION);
    
    $oGroups = $oAllGroups->query("group[@owner = $sUser]/@name | group[member = $sUser]/@name");
    foreach ($oGroups as $oAttribute) $aGroups[] = $oAttribute->getValue();
    
    // Création de l'utilisateur
    
    $oUser = new User($sUser, $aGroups);
    Controler::setUser($oUser);
    
    // Ajout du profil
    
    $oProfil = $this->getDocument($eUser->read('@path'));
    
    if (!$oProfil->isEmpty()) {
      
      $sFirstName = $oProfil->read('first-name');
      $oUser->setArguments(array('full-name' => $sFirstName.' '.$oProfil->read('last-name')));
      
    } else $sFirstName = '... visiteur';
    
    $oUser->login($bRemember);
    
    $oRedirect->setPath($sRedirect);
    $oRedirect->addMessage(t('Bienvenue '.$sFirstName), 'success');
  }
}


