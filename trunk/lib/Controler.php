<?php

/**
 * Contrôleur général du framwork
 */
class Controler {
  
  private static $oMessages = null;
  private static $bReady = false;
  private static $oUser = null;
  private static $aArguments = array();
  private static $aRights = array();
  
  private static $aAllowedWindowType = array();
  private static $sWindowType = 'html';
  
  private static $oWindow;
  
  private static $sClassName = '';
  private static $sOperationName = '';
  
  private static $sPath = '/';      // Chemin complet du fichier. Ex: /utilisateur/edit/1
  private static $aPaths = array(); // Liste des précédents chemins redirigés, ajoutés dans oRedirect
  private static $sAction = '';     // Chemin de l'action. Ex: /utilisateur/edit
  
  public static function trickMe($sDefaultModule, $sDefaultAction) {
    
    // Formatage de l'adresse
    
    self::loadSettings();
    self::loadContext($sDefaultModule, $sDefaultAction);
    
    // Chargement des droits
    
    self::loadRights();
    
    // Récupération du cookie Redirect qui indique qu'une redirection a été effectuée
    
    $oRedirect = self::loadRedirect();
    
    // Récupération du cookie User
    
    self::setUser(self::loadUser());
    
    // Création du type de fenêtre
    
    // $sWindow = ucfirst($oRedirect->getWindowType());
    $sWindow = ucfirst(self::getWindowType());
    self::setWindow(new $sWindow);
    
    // Include de la classe d'action
    
    $oAction = self::includeClass();
    
    // Authentification
    
    if (!self::checkAuthentication()) self::accessRedirect();
    
    // DEBUG
    
    if (self::isAdmin()) error_reporting(E_ALL);
    else error_reporting(0);
    
    /*** Lancement de l'action, récupuration du contenu / redirect ***/
    
    $oResult = self::getContent($oAction, $oRedirect);
    
    // Ajout des infos système
    
    $oMessage = new HTML_Strong(t('Authentification').' : ');
    
    if (self::getUser()->isReal()) {
      
      self::addMessage(array(
        $oMessage,
        self::getUser()->getBloc('full_name')), 'system');
      
    } else {
      
      self::addMessage(array(
        $oMessage, 
        new HTML_Tag('em', t('- aucun -'))), 'system');
    }
    
    if (self::getUser()->isReal()) {
      
      $oMessage = new HTML_Strong(t('Rôle(s)').' : ');
      
      if (self::getUser()->getRoles()) {
        
        self::addMessage(array(
          $oMessage,
          implode(', ', self::getUser()->getRoles())), 'system');
          
      } else {
        
        self::addMessage(array(
          $oMessage,
          new HTML_Tag('em', t('- aucun -'))), 'system');
      }
    }
    
    self::addMessage(array(
      new HTML_Strong(t('Adresse').' : '),
      self::getAction()), 'system');
    
    $oMessage = new HTML_Strong(t('Redirection').' : ');
    
    if ($oRedirect->isReal()) {
      
      self::addMessage(array(
        $oMessage, 
        $oRedirect->getSource()), 'system');
      
    } else {
      
      self::addMessage(array(
        $oMessage,
        new HTML_Tag('em', t('- aucun -'))), 'system');
    }
    
    self::addMessage(array(
      new HTML_Strong(t('Fenêtre').' : '), 
      self::getWindowType()), 'system');
    
    self::addMessage(array(
      new HTML_Strong(t('Action').' : '),
      self::getClassName(),
      '::',
      self::getOperationName().'()'), 'system');
    
    self::addMessage(array(
      new HTML_Strong(t('Date & heure').' : '),
      date('j M Y').' - '.date('H:i')), 'system');
    
    self::addMessage(array(
      new HTML_Strong(t('Messages').' : '),
      implode(', ', self::getMessages()->getAllowedMessages())), 'system');
    
    // Redirection ou ajout du contenu
    
    if (is_object($oResult) && get_class($oResult) == 'Redirect') {
      
      // Redirection
      
      if (self::isWindowType('html') || self::isWindowType('redirection')) self::doHTTPRedirect($oResult);
      else self::doAJAXRedirect($oResult);
      
    } else {
      
      // Affichage
      
      $oContent = self::getWindow()->setContent($oResult);
    }
    
    if (self::isAdmin()) self::getMessages()->addMessages(db::getQueries());
    return self::getWindow();
  }
  
  public static function loadSettings() {
    
    $oSettings = new XML_Document('/xml/root.xml', 'file');
    
    self::$oMessages = new Messages(explode(',', $oSettings->read('//messages/allowed')));
    self::setReady();
    
    self::$aAllowedWindowType = $oSettings->query('//window/*')->toArray('name');
  }
  
  public static function loadContext($sDefaultModule, $sDefaultAction) {
    
    $aArguments = array();
    
    // Lecture des arguments de l'adresse
    
    if (isset($_GET['q']) && $_GET['q']) {
      
      self::setPath($_GET['q']);
      $aArguments = explode('/', $_GET['q']);
      
      // Le premier argument (si il est correct) indique le type de fenêtre
      
      if (in_array($aArguments[0], self::$aAllowedWindowType)) self::setWindowType(array_shift($aArguments));
    }
    
    // Les 2 suivants indiquent respectivement la classe et la méthode à appeller dans self::getContent()
    
    if (count($aArguments) >= 2) {
      
      list($sModule, $sAction) = $aArguments;
      self::addArguments(array_splice($aArguments, 2)); // Tous les argument suivants sont stockés
      
    } else {
      
      if (count($aArguments) && Controler::isAdmin()) self::addMessage(t('Pas assez d\'arguments !'), 'warning');
      
      $sModule = $sDefaultModule;
      $sAction = $sDefaultAction;
    }
    
    // Définit les noms des classes et fonctions correspondantes à l'adresse
    
    self::$sAction = "/$sModule/$sAction";
    
    self::$sClassName = ucfirst($sModule);
    self::$sOperationName = 'action'.ucfirst($sAction);
  }
  
  public static function loadRights() {
    
    self::$aRights = self::getYAML('rights.yml');
  }
  
  public static function getBacktrace($sStatut = 'system') {
    
    $aResult = array(); $aLines = array(); $i = 0;
    $aBackTrace = debug_backtrace();
    array_shift($aBackTrace);
    
    foreach ($aBackTrace as $aLine) {
      
      if (isset($aLine['line'])) $aLines[] = $aLine['line'];
      else $aLines[] = 'k';
    }
    
    $aLines[] = 'x';
    
    foreach ($aBackTrace as $aTrace) {
      
      if (isset($aTrace['file'])) $sFile = new HTML_Tag('u', strrchr($aTrace['file'], DIRECTORY_SEPARATOR));
      else $sFile = 'xxx';
      
      if (isset($aTrace['class'])) $sClass = "::{$aTrace['class']}";
      else $sClass = '';
      
      $aResult[] = new HTML_Div(array("[{$aLines[$i]}] ", $sFile, $sClass, "::{$aTrace['function']}()"));
      $i++;
    }
    // self::addMessage(new HTML_Strong(t('Backtrace').' : ').implode('<br/>', $aResult), $sStatut);
    return new XML_NodeList($aResult);
  }
  
  public static function loadRedirect() {
    
    $oRedirect = new Redirect();
    
    // Une redirection a été effectuée
    
    if (array_key_exists('redirect', $_SESSION)) {
      
      $oRedirect = unserialize($_SESSION['redirect']);
      unset($_SESSION['redirect']);
      
      // Récupération des messages du Redirect et suppression
      
      if (get_class($oRedirect) == 'Redirect') {
        
        $oRedirect->setReal();
        
        $oMessages = new XML_Document;
        $oMessages->loadText($oRedirect->getArgument('messages'));
        $aMessages = $oMessages->query('//message');
        
        $oRedirect->resetMessages($aMessages);
        
        if ($aMessages->length) self::getMessages()->addMessages($aMessages);
        
      } else {
        
        $oRedirect = new Redirect();
        self::addMessage(t('Cookie Redirect perdu !'), 'warning');
      }
    }
    
    return $oRedirect;
  }
  
  public static function includeClass() {
    
    $sClassName = self::getClassName();
    
    $sPath = "action/$sClassName.php";
    
    if (file_exists(self::getDirectory().$sPath)) include_once($sPath);
    else if (self::isAdmin()) self::addMessage(sprintf(t('Fichier "%s" introuvable !'), $sPath), 'warning');
    
    // Contrôle de l'existence de la classe et de l'opération
    
    if (self::isAdmin()) $sClassError = sprintf(t('Action impossible (la classe "%s" n\'existe pas) !'), new HTML_Strong($sClassName));
    else $sClassError = t('Page introuvable, veuillez corriger l\'adresse !');
    
    if (!class_exists($sClassName)) self::errorRedirect($sClassError);
    
    // Création de la classe
    
    $oAction = new $sClassName;
    
    return $oAction;
  }
  
  public static function checkAuthentication($sPath = null) {
    
    if (!$sPath) $sPath = self::getAction();
    
    $aAskRights = self::getActionRights($sPath);
    
    $aExceptions = array(PATH_LOGIN, PATH_LOGIN_DO, PATH_ERROR, PATH_ACCESS);
    
    if (in_array($sPath, $aExceptions)) return true;
    
    if (self::getUser()->isRole('administrateur')) return true;
    if (self::getUser()->isRole('developpeur')) return true;
    
    if (in_array('*', $aAskRights)) return true;
    if (self::getUser()->isReal() && in_array('#', $aAskRights)) return true;
    
    foreach ($aAskRights as $sRight) if (self::getUser()->isRole($sRight)) return true;
    
    return false;
  }
  
  public static function getRights() {
    
    return self::$aRights;
  }
  
  public static function getActionRights($sPath = null) {
    
    if (!$sPath) $sPath = self::getAction();
    
    $aActionRights = isset(self::$aRights[$sPath]['rights']) ? self::$aRights[$sPath]['rights'] : array();
    
    if (!is_array($aActionRights)) $aActionRights = array($aActionRights);
    
    return $aActionRights;
  }
  
  public static function getContent($oAction, $oRedirect) {
    
    $sOperationName = self::getOperationName();
    
    // Création de la méthode
    
    if (self::isAdmin()) $sOperationError = sprintf(t('Action impossible (la méthode "%s" n\'existe pas) !'), new HTML_Strong(self::getClassName().'::'.$sOperationName.'()'));
    else $sOperationError = t('Page introuvable, veuillez corriger l\'adresse !');
    
    if (!method_exists($oAction, $sOperationName)) self::errorRedirect($sOperationError);
    else $oResult = $oAction->$sOperationName($oRedirect);
    
    return $oResult;
  }
  
  public static function doAJAXRedirect($oRedirect) {
    
    self::doRedirect($oRedirect);
    self::getWindow()->setRedirect($oRedirect);
  }
  
  public static function doHTTPRedirect($oRedirect) {
    
    self::doRedirect($oRedirect);
    
    // Redirection
    header("Location: $oRedirect");
    
    exit;
  }
  
  public static function doRedirect($oRedirect) {
    
    // Récupération et ajout dans le Redirect des messages en attente
    
    $oRedirect->getMessages()->addMessages(self::getMessages()->query('//message'));
    
    // Suppression des infos système
    
    if ($oSystem = $oRedirect->getMessages()->get('system')) $oSystem->remove();
    
    // Ajout des messages requêtes si admin
    
    if (self::isAdmin()) $oRedirect->getMessages()->addMessages(db::getQueries('old'));
    
    $oRedirect->setArgument('messages', $oRedirect->getMessages()->saveXML());
    
    // $oRedirect->setSource(Controler::getPath());
    
    // Redirection
    
    $_SESSION['redirect'] = serialize($oRedirect);
  }
  
  /* *** */
  
  public static function accessRedirect($mMessages = '', $sPath = PATH_LOGIN, $sStatut = 'warning') {
    
    if (!$mMessages) $mMessages = sprintf(t('Vous n\'avez pas les droits pour accéder à cette page "%s" !'), self::getPath());
    
    if (Controler::getUser()->isReal()) $sPath = PATH_ACCESS;
    
    if (is_string($mMessages)) $mMessages = new Message($mMessages, $sStatut);
    
    self::doHTTPRedirect(new Redirect($sPath, $mMessages));
  }
  
  public static function errorRedirect($mMessages = null, $sStatut = 'error') {
    
    if (is_string($mMessages)) $mMessages = new Message($mMessages, $sStatut);
    
    self::doHTTPRedirect(new Redirect(PATH_ERROR, $mMessages));
  }
  
  public static function setUser($oUser = null) {
    
    if (is_object($oUser) && get_class($oUser) == 'User') self::$oUser = $oUser;
  }
  
  public static function loadUser() {
    
    // Une redirection a été effectuée
    
    if (array_key_exists('user', $_SESSION)) {
      
      self::addMessage(t('Session existante'), 'report');
      
      $oUser = unserialize($_SESSION['user']);
      
      // Récupération des messages du Redirect et suppression
      
      if (get_class($oUser) != 'User') {
        
        $oUser = new User();
        
        unset($_SESSION['user']);
        self::addMessage(t('Cookie User perdu !'), 'warning');
      }
      
    } else $oUser = new User();
    
    return $oUser;
  }
  
  public static function getUser() {
    
    return self::$oUser;
  }
  
  public static function getMessages() {
    
    return self::$oMessages;
  }
  
  public static function addMessage($sMessage = '- message vide -', $sStatut = 'notice', $aArgs = array()) {
    
    self::getMessages()->addStringMessage($sMessage, $sStatut, $aArgs);
  }
  
  public static function setWindow($oWindow) {
    
    self::$oWindow = $oWindow;
  }
  
  public static function getWindow() {
    
    return self::$oWindow;
  }
  
  public static function getWindowType() {
    
    return self::$sWindowType;
  }
  
  public static function isWindowType($sWindowType) {
    
    return (self::$sWindowType == $sWindowType);
  }
  
  public static function setWindowType($sWindowType) {
    
    self::$sWindowType = $sWindowType;
  }
  
  public static function getAction() {
    
    return self::$sAction;
  }
  
  public static function getArgument($sKey = 0) {
    
    return isset(self::$aArguments[$sKey]) ? self::$aArguments[$sKey] : null;
  }
  
  public static function getArguments() {
    
    return self::$aArguments;
  }
  
  public static function setArgument($sKey, $oValue) {
    
    self::$aArguments[$sKey] = $oValue;
  }
  
  public static function addArguments($aArguments) {
    
    self::$aArguments += $aArguments;
  }
  
  public static function setArguments($aArguments) {
    
    self::$aArguments = $aArguments;
  }
  
  public static function getPath() {
    
    return self::$sPath;
  }
  
  public static function setPath($sPath) {
    
    self::$sPath = '/'.$sPath;
  }
  
  public static function getDirectory() {
    
    return PATH_PHP.'/';
  }
  
  public static function isAdmin() {
    
    if (DEBUG) return true;
    else if (self::getUser()) return self::getUser()->isRole('developpeur');
    else return false;
  }
  
  public static function useCache() {
    
    return self::$useCache;
  }
  
  public static function getClassName() {
    
    return self::$sClassName;
  }
  
  public static function getOperationName() {
    
    return self::$sOperationName;
  }
  
  public static function setReady($bValue = true) {
    
    self::$bReady = $bValue;
  }
  
  public static function isReady() {
    
    return self::$bReady;
  }
  
  public static function getYAML($sFilePath) {
    
    return Spyc::YAMLLoad(self::getDirectory().$sFilePath);
  }
  
  // public static function getBloc($sKey) {
    
    // return self::getMessages()->getBloc($sKey);
  // }
  
  // public static function setBloc($sKey, $sValue) {
    
    // return self::getMessages()->setBloc($sKey, $sValue);
  // }
}

class Redirect {
  
  private $sPath = '/'; // URL cible
  private $oSource = ''; // URL de provenance
  private $sWindowType = 'window';
  private $bIsReal = false; // Défini si le cookie a été redirigé ou non
  
  private $aArguments = array();
  private $oMessages;
  
  public function __construct($sPath = '/', $mMessage = array(), $aArguments = array()) {
    
    $this->resetMessages($mMessage);
    
    $this->setPath($sPath);
    $this->aArguments = $aArguments;
    $this->setArgument('post', $_POST);
    $this->setWindowType(Controler::getWindowType());
    $this->updateSource();
  }
  
  public function getArgument($sKey) {
    
    return (array_key_exists($sKey, $this->aArguments)) ? $this->aArguments[$sKey] : false;
  }
  
  public function setArgument($sKey, $mValue) {
    
    $this->aArguments[$sKey] = $mValue;
  }
  
  public function getArguments() {
    
    return $this->aArguments;
  }
  
  public function resetMessages($aMessages = array()) {
    
    $this->oMessages = new Messages(Controler::getMessages()->getAllowedMessages());
    $this->getMessages()->addMessage($aMessages);
  }
  
  public function getMessages($sStatut = null) {
    
    if ($sStatut) return $this->oMessages->getMessages($sStatut);
    else return $this->oMessages;
  }
  
  public function addMessage($sMessage = '- message vide -', $sStatut = 'notice', $aArguments = array()) {
    
    $this->oMessages->addStringMessage($sMessage, $sStatut, $aArguments);
  }
  
  public function getPath() {
    
    return $this->sPath;
  }
  
  public function setPath($sPath) {
    
    if ($sPath == '/' || $sPath != Controler::getPath()) $this->sPath = $sPath;
    else Controler::errorRedirect(t('Un problème de redirection à été détecté !'));
  }
  
  public function getSource() {
    
    return $this->oSource;
  }
  
  public function setSource($sSource, $aArguments) {
    
    $this->oSource = new URL($sSource, $aArguments);
  }
  
  public function updateSource() {
    
    $this->setSource(Controler::getAction(), Controler::getArguments());
  }
  
  public function isSourceAction($sSource) {
    
    return ($this->oSource->getAction() == $sSource);
  }
  
  public function isSourcePath($sSource) {
    
    return ((string) $this->oSource == $sSource);
  }
  
  public function getWindowType() {
    
    return $this->sWindowType;
  }
  
  public function setWindowType($sWindowType) {
    
    $this->sWindowType = $sWindowType;
  }
  
  public function setReal($bValue = 'true') {
    
    $this->bIsReal = (bool) $bValue;
  }
  
  public function isReal() {
    
    return $this->bIsReal;
  }
  
  public function __toString() {
    
    return $this->getPath();
  }
}

class URL {
  
  private $sAction;
  private $aArguments;
  
  public function __construct($sAction, $aArguments) {
    
    $this->sAction = $sAction;
    $this->aArguments = $aArguments;
  }
  
  public function getAction() {
    
    return $this->sAction;
  }
  
  public function __toString() {
    
    if ($this->aArguments) $sArguments = '/'.implode('/', $this->aArguments);
    else $sArguments = '';
    
    return $this->sAction.$sArguments;
  }
}

class Messages extends XML_Action {
  
  private $aAllowedMessages = array();
  
  public function __construct($aAllowedMessages = array()) {
    
    parent::__construct('messages');
    $this->setBloc('allowed', new XML_Document('messages'));
    
    $this->setAllowedMessages($aAllowedMessages);
  }
  
  public function addMessage() {
    
    $aArguments = func_get_args();
    
    if (!$aArguments) return null;
    
    if (count($aArguments) > 1) return $this->addMessages($aArguments);
    else $mMessage = $aArguments[0];
    
    if (is_array($mMessage) || ($mMessage instanceof XML_NodeList)) return $this->addMessages($mMessage);
    else if (is_string($mMessage)) return $this->addStringMessage($mMessage);
    else if ($mMessage instanceof XML_Element) {
      
      $oMessage = $mMessage;
      
      // TODO: foreach ($oMessage->aArguments as $oArgument) $this->setArgument('fields'][ += $oMessage[]
      // $this->aMessages[$oMessage->getStatut()][] = $oMessage;
      
      $sStatut = $oMessage->read('statut');
      
      // Add the stat if not exists
      
      if (!$oAllStatut = $this->get($sStatut))
        $oAllStatut = $this->addNode($sStatut);
      
      // Add in the main doc
      
      $oAllStatut->add($oMessage);
      
      // Add in the allowed doc
      
      if (in_array($sStatut, $this->getAllowedMessages()))
        $oMessage = $this->getBloc('allowed')->get($sStatut)->add($oMessage);
      
      return $oMessage;
      
    } else return null;
  }
  
  /*
   * Add a message from a String
   * 
   * @param $sMessage
   *   The message format String
   * @param $sStatut
   *   The stat of the message format String
   * @param $aArguments
   *   The arguments of the message format Array
   * @return
   *   A pointer to the node added
   **/
  public function addStringMessage($sMessage, $sStatut = 'notice', $aArguments = array()) {
    
    return $this->addMessage(new Message($sMessage, $sStatut, $aArguments));
    if (is_array($aArguments) && isset($aArguments['show_array'])) $this->addStringMessage(implosion(' => ', '<br />', $aArguments['show_array']), $sStatut);
  }
  
  /*
   * Ajoute une liste de messages dans la pile
   * 
   * @param $aMessages
   *   Un tableau contenant les messages à ajouter
   **/
  public function addMessages($aMessages) {
    
    $aResult = array();
    foreach ($aMessages as $oMessage) $aResult[] = $this->addMessage($oMessage);
    
    return $aResult;
  }
  
  /*
   * Récupère les messages sous forme de liste
   * 
   * @param $sStatut
   *   Si donné, seul les messages du statut correspondant seront récupérés
   **/
  public function getMessages($sStatut = null) {
    
    if ($sStatut) {
      
      $oResult = $this->query("$sStatut/*");
      
    } else {
      
      $oResult = $this->query("//message");
    }
    
    if (!$oResult->length) $oResult = array();
    
    return $oResult;
  }
  
  public function hasMessages($sStatut = null) {
    
    return $this->getMessages($sStatut);
  }
  
  public function getAllowedMessages() {
    
    return $this->aAllowedMessages;
  }
  
  public function setAllowedMessages($aStatuts = array()) {
    
    $this->aAllowedMessages = $aStatuts;
    
    // Add allowed statuts in docs
    $this->addArray($aStatuts);
    $this->getBloc('allowed')->add($this->getChildren());
  }
  
  public function parse() {
    
    // return $this->parseXSL(new XML_Document('/messages.xsl'));
    return $this->getBloc('allowed')->parseXSL(new XML_Document('/xml/messages.xsl', 'file'));
  }
}

class Message extends HTML_Tag {
  
  public function __construct($mMessage, $sStatut = 'notice', $aArgs = array()) {
    
    parent::__construct('message');
    
    $this->addNode('content', $mMessage);
    $this->addNode('statut', $sStatut);
    $this->addNode('arguments')->addArray($aArgs);
  }
}
