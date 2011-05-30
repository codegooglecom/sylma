<?php

/**
 * Contrôleur général du framework
 */
class Controler {
  
  private static $iStartTime = 0;
  private static $iBacktraceLimit = 0;
  
  private static $oMessages = null;
  private static $user = null;
  private static $oDirectory = null;
  
  private static $oWindow;
  private static $oWindowSettings;
  private static $oSettings;
  private static $aSettings = array();
  private static $oRedirect;
  private static $oDatabase;
  
  private static $oPath = null;      // Chemin complet du fichier. Ex: /utilisateur/edit/1.html
  private static $aPaths = array(); // Liste des précédents chemins redirigés, ajoutés dans oRedirect
  private static $sAction = '';     // Chemin de l'action. Ex: /utilisateur/edit
  public static $aResults = array();     // Pile of results of the same action in different mime type (typically html + json)
  public static $iResults = 0;
  private static $bUseMessages = false;
  private static $sSystemPath = '';
  
  private static $aActions = null;      // Array of running actions
  
  /**
   * Reflection classes builded with @method buildClass() or @method ModuleBase::create();
   */
  private static $aClasses = array();
  
  public static function trickMe() {
    
    global $aDefaultInitMessages;
    global $aExecutableExtensions;
    
    self::$iStartTime = microtime(true);
    self::$aActions[] = new XML_Action();
    
    self::$sSystemPath = $_SERVER['DOCUMENT_ROOT'];
    
    // Authentication : load user's session var - $_SESSION['user']
    
    if (!self::$user = self::createObject(Sylma::get('users/classes/user'))) {
      
      Sylma::throwException(txt('Cannot load user'));
    }
    
    self::$user->load();
    
    // Define error_report
    self::setReportLevel();
    
    // Check for maintenance mode
    if ($aMaintenance = self::loadMaintenance()) return $aMaintenance[0];
    
    // Main Messages object
    self::$oMessages = new Messages();
    self::$iBacktraceLimit = Sylma::get('messages/backtrace/count');
    
    // Root directory
    self::$oDirectory = new XML_Directory('', '', Sylma::get('directories/root/rights')->query());
    
    // Load general parameters - root.xml
    self::loadSettings();
    
    // Set Controler ready
    self::useMessages(true);
    
    // init xml database
    if (Sylma::get('db/enable')) self::setDatabase(new XML_Database());
    
    // Load Redirect session var, if present means it has been redirected - $_SESSION['redirect'], $_POST in 'document'
    $oRedirect = self::loadRedirect();
    
    // Parse of the request_uri, creation of the window - $_GET
    self::loadContext();
    
    // Reload last alternatives mime-type results - $_SESSION['results']
    self::loadResults();
    
    if (($sExtension = Controler::getPath()->getExtension()) &&
     (!in_array($sExtension, array('eml', 'iml')) || strtobool(self::getPath()->getAssoc('no-action'))) && 
     ($oFile = self::getFile(Controler::getPath().'.'.$sExtension)) &&
     $oFile->checkRights(MODE_READ)) {
      
      /* A file */
      
      self::getWindow()->loadAction($oFile);
      
    } else if (in_array(self::getPath()->getExtension(), $aExecutableExtensions)) {
      
      /* An action */
      
      self::getPath()->parsePath();
      
      if ($mResult = self::getResult()) {
        
        // Pre-recorded result
        $oResult = self::getWindow()->loadAction($mResult); // TODO : make XML_Action
        
      } else {
        
        /*
        if (!isset($_SESSION['temp-paths'])) $_SESSION['temp-paths'] = array();
        //$_SESSION['temp-paths'] = array();
        $_SESSION['temp-paths'][] = (string) self::getPath();
        dspf($_SESSION['temp-paths']);
        */
        
        // Load file of the interface's paths
        
        ActionControler::loadInterfaces();
        
        // Get then send the action
        
        $oAction = new XML_Action(self::getPath(), $oRedirect, array(), self::getWindow());
        
        if ($oAction->isEmpty() && Sylma::get('actions/redirect/enable')) self::errorRedirect(); // no rights / empty main action
        else {
          
          if (self::getWindowSettings()->hasAttribute('action')) {
            
            $oResult = null;
            
            if (self::getWindow()) {
              
              self::getWindow()->getPath()->setAssoc('window-action', $oAction);
            }
            
          } else $oResult = self::getWindow()->loadAction($oAction); // TODO or not todo : make XML_Action
        }
      }
      
      /* Action redirected */
      
      if (is_object($oResult) && $oResult instanceof Redirect) {
        
        self::doHTTPRedirect($oResult);
        //if (self::isWindowType('html') || self::isWindowType('redirection')) self::doHTTPRedirect($oResult);
        //else self::doAJAXRedirect($oResult);
      }
    }
    
    return self::getWindow();
  }
  
  private static function loadMaintenance() {
    
    $aResult = array();
    
    if (Sylma::get('maintenance/enable') && !self::getUser()->isMember('0')) { // continue when admin
      
      $sPath = isset($_GET['q']) ? $_GET['q'] : '';
      
      if ($sPath != Sylma::get('maintenance/login-do')) { // continue when 'login-do'
        
        if ($sPath == 'login') $aResult[] = file_get_contents(Sylma::get('maintenance/login'));
        else $aResult[] = file_get_contents(Sylma::get('maintenance/file'));
      }
    }
    
    return $aResult;
  }
  
  private static function setReportLevel() {
    
    if (self::isAdmin()) {
      
      // debug or not debug..
      if (Sylma::get('debug/enable')) error_reporting(E_ALL);
      else error_reporting(Sylma::get('users/root/error-level'));
      
    } else error_reporting(0);
  }
  
  private static function loadSettings() {
    
    self::$oSettings = new XML_Document(Sylma::get('general/settings'), Sylma::MODE_EXECUTE);
    
    $oAllowed = new XML_Document(self::getSettings('messages/allowed/@path'));
    
    $aMessages = self::getMessages()->getMessages();
    self::$oMessages = new Messages($oAllowed, $aMessages);
  }
  
  public static function getSettings($sQuery = '') {
    
    if ($sQuery && self::$oSettings) {
      
      if (array_key_exists($sQuery, self::$aSettings)) return self::$aSettings[$sQuery];
      else $sResult = self::$aSettings[$sQuery] = self::$oSettings->read($sQuery);
      
      if (!$sResult) dspm(xt('Aucun paramètre recupéré dans %s avec la requête "%s"',
        self::$oSettings->getFile()->parse(), new HTML_Strong($sQuery)), 'action/error');
      
      return $sResult;
      
    } else return self::$oSettings;
  }
  
  public static function parseGet() {
    
    $sQuery = substr($_SERVER['QUERY_STRING'], 2);
  }
  /*
   * load GET, build action path, show-index, load window settings
   **/
  private static function loadContext() {
    
    //$aGET = self::parseGet();
    $aGET = $_GET;
    
    if (isset($aGET['q']) && $aGET['q']) {
      
      $sPath = $aGET['q'];
      unset($aGET['q']);
      
    } else $sPath = '/';
    
    $oPath = new XML_Path('/'.$sPath, array(), false);
    
    foreach ($aGET as &$mValue) $mValue = $oPath->parseBaseType($mValue);
    $oPath->mergeAssoc($aGET);
    
    // The extension specify the window type
    
    if (!$sExtension = $oPath->parseExtension(true)) $sExtension = 'html';
    $sExtension = strtolower($sExtension);
    
    if (self::isAdmin() && ($oPath->getIndex(0, true) == 'show-report' || isset($_POST['sylma_show_report']))) {
      
      $oPath->getIndex();
      
      if (!$oAction = self::getMessages()->get('action')) $oAction = self::getMessages()->addNode('action');
      $oAction->addNode('report');
    }
    
    self::$oPath = $oPath;
    
    $oWindowSettings = self::getSettings()->get("window/*[extensions[contains(text(), '$sExtension')]]");
    if (!$oWindowSettings) $oWindowSettings = new XML_Element('any');
    
    self::$oWindowSettings = $oWindowSettings;
    
    // Creation of the window
    
    $oWindow = null;
    
    if ($sInterface = $oWindowSettings->getAttribute('interface')) {
      
      ActionControler::loadInterfaces();
      
      if ($oInterface = ActionControler::buildInterface(new XML_Document($sInterface, MODE_EXECUTION))) {
        
        if (!$sAction = $oWindowSettings->getAttribute('action')) {
          
          dspm(t('Impossible de charger la fenêtre, action introuvable'), 'action/error');
          
        } else {
          
          if ($sInterface = $oInterface->readByName('file')) $sInterface = self::getAbsolutePath($sInterface, $oInterface->getFile()->getParent());
          $oWindow = self::buildClass($oInterface->readByName('name'), $sInterface, array($sAction, self::getRedirect()));
        }
      }
      
    } else $oWindow = self::buildClass(ucfirst(self::getWindowType()));
    
    if (!$oWindow) dspm(t('Aucune fenêtre valide, impossible de charger le site'), 'error');;
    
    self::$oWindow = $oWindow;
  }
  
  public static function loadResults() {
    
    if (!array_key_exists('results', $_SESSION)) $_SESSION['results'] = array();
    self::$aResults = $_SESSION['results'];
    
    /*
     * TODO Not necessary until result now use ID, should be run sometimes
    
    foreach (self::$aResults as $sKey => $aAction) {
      
      if (!array_key_exists('result-time', $aAction)) $fTime = 0;
      else $fTime = $aAction['result-time'];
      
      if ((microtime(true) - $fTime) > SYLMA_RESULT_LIFETIME) unset(self::$aResults[$sKey]);
    }
    
    self::updateResults();
    */
  }
  
  public static function updateResults() {
    
    $_SESSION['results'] = self::$aResults;
  }
  
  public static function countResults() {
    
    return self::$iResults;
  }
  
  public static function getResults() {
    
    return self::$aResults;
  }
  
  private static function getResult() {
    
    $mResult = null;
    
    if (($sID = self::getPath()->getAssoc('sylma-result-id')) &&
      array_key_exists($sID, self::$aResults)) {
      
      $mResult = self::$aResults[$sID];
      unset(self::$aResults[$sID]);
    }
    
    return $mResult;
  }
  
  public static function addResult($mResult, $sID) {
    
    self::$aResults[$sID] = $mResult;
    self::updateResults();
    
    self::$iResults++;
    
    return $mResult;
  }
  
  private static function loadRedirect() {
    
    $oRedirect = new Redirect();
    
    // Une redirection a été effectuée
    
    if (array_key_exists('redirect', $_SESSION)) {
      
      $oRedirect = unserialize($_SESSION['redirect']);
      unset($_SESSION['redirect']);
      
      // Récupération des messages du Redirect et suppression
      
      if (get_class($oRedirect) == 'Redirect') {
        
        $oRedirect->setReal();
        
        $oMessages = $oRedirect->getDocument('messages');
        // $oMessages = new XML_Document;
        // $oMessages->loadText($oRedirect->getArgument('messages'));
        $aMessages = $oMessages->query('//lm:message', 'lm', SYLMA_NS_MESSAGES);
        
        $oRedirect->setMessages($aMessages);
        
        if ($aMessages->length) self::getMessages()->addMessages($aMessages);
        
      } else {
        
        $oRedirect = new Redirect();
        self::addMessage(t('Session Redirect perdu !'), 'warning');
      }
      
    } else {
      
      if ($_POST) {
        
        //$oValues = new XML_Document(new XML_Element('post', null, null, SYLMA_NS_XHTML));
        $oValues = new XML_Document('post');
        
        self::loadPost($_POST, $oValues->getRoot());
        
        $oRedirect->setDocument('post', $oValues);
      }
    }
    
    self::$oRedirect = $oRedirect;
    
    return $oRedirect;
  }
  
  /* Build XML_Document from array, used for $_POST.
   * 
   **/
 
  private static function loadPost($mValues, XML_Element $oNode, $sName = '') {
    
    $bFirstPass = true;
    $sIDPrefix = 'sylma-id';
    
    foreach ($mValues as $mKey => $mValue) {
      
      if (is_numeric($mKey)) {
        
        dspm(xt('Numeric field\'s name are not allowed in $_POST %s', view($_POST)), 'warning');
      }
      else if (substr($mKey, 0, strlen($sIDPrefix)) == $sIDPrefix) {
        
        if (!$sName) {
          
          dspm(xt('Impossible d\'importer la clé ID %s dans $_POST (%s), le nom de l\'élément n\'est pas spécifié',
            new HTML_Strong($mKey), view($_POST)), 'action/warning');
        }
        else {
          
          if ($bFirstPass) {
            
            if (!$oNode->getParent()) {
              
              dspm(xt('Impossible d\'importer la clé ID %s dans $_POST (%s), un élément parent doit être spécifié pour %s',
                new HTML_Strong($mKey), view($_POST), view($oNode)), 'action/warning');
            }
            else {
              
              $oParent = $oNode->getParent();
              $oNode->remove();
              $oNode = $oParent;
            }
          }
          
          $oResult = $oNode->addNode($sName);
          
          if (is_array($mValue)) self::loadPost($mValue, $oResult);
          else $oResult->set($mValue);
        }
      }
      else {
        
        if (is_array($mValue)) {
          
          $oResult = $oNode->addNode($mKey);
          self::loadPost($mValue, $oResult, $mKey);
        }
        else {
          
          $oResult = $oNode->addNode($mKey);
          $oResult->set($mValue);
        }
      }
      
      $bFirstPass = false;
    }
  }
  
  private static function getRedirect() {
    
    return self::$oRedirect;
  }
  
  private static function getMime($sExtension) {
    
    switch (strtolower($sExtension)) {
      
      case 'jpg' : $sExtension = 'jpeg';
      case 'jpeg' :
      case 'png' :
      case 'gif' : return 'image/'.$sExtension;
      
      case 'js' : return 'application/javascript';
      case 'css' : return 'text/css';
      case 'xml' :
      case 'xsl' : return 'text/xml';
      
      default : return 'plain/text';
    }
  }
  
  public static function setContentType($sExtension) {
    
    header('Content-type: '.self::getMime($sExtension));
  }

  /*****************/
  /* Infos & Stats */
  /*****************/
  
  public static function getSystemInfos() {
    
    $oView = new HTML_Ul(null, array('class' => 'msg-system'));
    
    $oMessage = new HTML_Strong(t('Authentification').' : ');
    
    if (self::getUser()) $oUser = $oView->addMultiItem($oMessage, self::getUser());
    else $oUser = $oView->addMultiItem($oMessage, new HTML_Tag('em', t('- aucun -')));
    
    $oUser->add(new HTML_Br, new HTML_Tag('em', $_SERVER['REMOTE_ADDR'])); // IP
    /*
    if (self::getUser()->isReal()) {
      
      $oMessage = new HTML_Strong(t('Groupe(s)').' : ');
      
      if (self::getUser()->getGroups()) $oView->addMultiItem($oMessage, implode(', ', self::getUser()->getGroups()));
      else $oView->addMultiItem($oMessage, new HTML_Tag('em', t('- aucun -')));
    }
    */
    $oView->addMultiItem(new HTML_Strong(t('Adresse').' : '), self::getPath());
    
    $oMessage = new HTML_Strong(t('Redirection').' : ');
    
    if (self::getRedirect()) {// && ) {
      
      if (self::getRedirect()->isReal()) $oPath = new HTML_Div(self::getRedirect()->getSource()->getOriginalPath());
      else $oPath = new HTML_Div(new HTML_Tag('em', t('- aucune -')));
      
      $nItem = $oView->addMultiItem($oMessage, $oPath);
      foreach (self::getRedirect()->getDocuments() as $sKey => $oDocument) $nItem->add($sKey, ': ', view($oDocument, false));
    }
    
    
    $sPath = self::getPath()->getSimplePath();
    if (array_key_exists($sPath, self::$aResults)) {
      
      $oResults = new HTML_Div();
      
      foreach (self::$aResults[$sPath] as $sWindow => $mResult) {
        
        if (is_array($mResult))
          foreach ($mResult as $iKey => $sResult) $oResults->add(view(new HTML_Tag($sWindow, new XML_CData($sResult), array('path' => $sPath)), false));
      }
      
    } else $oResults = null;
    //$oResults = view(self::$aResults);
    
    $oView->addMultiItem(new HTML_Strong(t('Fenêtre').' : '), self::getWindowType(), new HTML_Br, $oResults);
    $oView->addMultiItem(new HTML_Strong(t('Date & heure').' : '), date('j M Y').' - '.date('H:i'));
    $oView->addMultiItem(new HTML_Strong(t('Statistiques XML').' : '), XML_Controler::viewStats());
    $oView->addMultiItem(new HTML_Strong(t('Resources').' : '),
      number_format(microtime(true) - self::$iStartTime, 3).' s', new HTML_Br,
      formatMemory(memory_get_peak_usage()));
    
    return $oView;
  }
  
  public static function infosSetFile($oFile, $bFirstTime) {
    
    if ($oLast = array_last(self::$aActions)) $oLast->resumeFile($oFile, $bFirstTime);
  }
  
  public static function infosSetQuery($sQuery) {
    
    if ($oLast = array_last(self::$aActions)) {
      
      $oLast->resumeQuery($sQuery);
      return true;
    }
    
    return false;
  }
  
  public static function infosOpenAction($oCaller) {
    
    self::$aActions[] = $oCaller;
  }
  
  public static function infosCloseAction($oAction) {
    
    if (self::$aActions) {
      
      array_pop(self::$aActions);
      if (($oLast = array_last(self::$aActions)) && ($oLast !== $oAction)) $oLast->resumeAction($oAction);
    }
  }
  
  public static function viewResume() {
    
    $oAction = array_pop(self::$aActions);
    
    $oAction->parse(array('time' => self::$iStartTime), false);
    $oTest = $oAction->viewResume();
    // dspf($oTest->query('//ld:file', array('ld' => SYLMA_NS_DIRECTORY))->length);
    $oResume = new XML_Element('controler', $oTest, array(), XML_Action::MONITOR_NS);
    // dspf($oResume->getNamespace());
    // dspf($oResume->query('//ld:file', array('ld' => SYLMA_NS_DIRECTORY))->length);
    $oResume->getFirst()->setAttribute('path', '<controler>');
    $oTemplate = new XSL_Document(Controler::getSettings('actions/template/@path'), MODE_EXECUTION);
    $oTemplate->setParameter('path-editor', Sylma::get('modules/editor/path'));
    // dspf($oResume->getDocument());
    // dspf($oTemplate);
    return $oResume->getDocument()->parseXSL($oTemplate);
  }
  
  public static function getInfos() {
    
    return new HTML_Tag('div',
        array(self::getSystemInfos(), self::viewResume()),
        array('class' => 'msg-infos clear-block'));
  }
  
  /* Window methods : TODO clean */
  
  public static function getWindowSettings() {
    
    return self::$oWindowSettings;
  }
  
  public static function getWindow() {
    
    return self::$oWindow;
  }
  
  public static function getWindowType() {
    
    if ($sClass = self::getWindowSettings()->getAttribute('class')) return $sClass;
    else return self::getWindowSettings()->getName(true);
  }
  
  public static function isWindowType($sWindowType) {
    
    return (self::getWindowType() == $sWindowType);
  }
  
  
  /*************/
  /* Redirects */
  /*************/
  
  public static function errorRedirect($mMessages = null, $sStatut = 'error') {
    
    if ($mMessages) Controler::addMessage($mMessages, $sStatut);
    
    if ($sExtension = self::getPath()->getExtension()) $sExtension = '.'.$sExtension;
    
    $sPath = SYLMA_PATH_ERROR.$sExtension;
    
    self::doHTTPRedirect(new Redirect($sPath));
  }
  
  private static function doHTTPRedirect($oRedirect) {
    
    self::doRedirect($oRedirect);
    
    // Redirection
    
    $sPath = (string) $oRedirect;
    
    if ($sPath) {
      
      header("Location: $sPath");
      exit;
      
    } else self::errorRedirect('Redirection incorrect !');
  }
  
  private static function doRedirect($oRedirect) {
    
    // Récupération et ajout dans le Redirect des messages en attente
    
    $oRedirect->getMessages()->addMessages(self::getMessages()->getMessages());
    
    // Ajout des messages requêtes si admin
    
    // if (self::isAdmin()) $oRedirect->getMessages()->addMessages(db::getQueries('old')->getMessages());
    
    $oRedirect->setDocument('messages', $oRedirect->getMessages());
    $oRedirect->setSource(Controler::getPath());
    
    // Redirection
    
    $_SESSION['redirect'] = serialize($oRedirect);
  }
  
  public static function error404() {
    
    header('HTTP/1.0 404 Not Found');
    // echo 'Erreur 404 :\'(';
    exit;
  }
  
  /* Backtrace / Messages */
  
  public static function formatResource($mArgument, $bDecode = false, $iMaxLength = 120) {
    
    if (Sylma::get('messages/format/enable')) {
      
      if (is_string($mArgument)) {
        
        //if (!mb_check_encoding($mArgument, 'UTF-8')) $mArgument = 'ERREUR D\'ENCODAGE';
        
        $aValue = array("'".stringResume($mArgument, $iMaxLength, false)."'", '#999');
        
      } else if (is_bool($mArgument))
        $aValue = $mArgument ? array('TRUE', 'green') :  array('FALSE', 'red');
      else if (is_numeric($mArgument))
        $aValue = array($mArgument, 'green');
      else if (is_array($mArgument)) {
        
        // Arrays
        $bLineBreak = Sylma::get('messages/array/line-break');
        
        if (count($mArgument)) {
          
          $iCount = count($mArgument) - 1;
          $oContent = new HTML_Div(null, array('style' => 'display: inline;'));
          if ($bLineBreak) $oContent->add(new HTML_Br);
          
          foreach ($mArgument as $mKey => $mValue) {
            
            $oContent->add(view($mKey), ' => ', self::formatResource($mValue, $bDecode));
            if ($iCount) $oContent->add(', ');
            
            if ($bLineBreak) $oContent->add(new HTML_Br);
            $iCount--;
          }
          
        } else $oContent = '';
        
        $aValue = array(new HTML_Div(xt('array[%s](%s)', new HTML_Strong(count($mArgument)), $oContent), array('class' => 'array' . ($bLineBreak ? ' array-break' : ''))), 'violet');
        
      } else if (is_object($mArgument)) {
        
        // Objects
        
        if ($mArgument instanceof XML_Document && !($mArgument instanceof XML_Action)) {
          
          /* XML_Document */
          
          if (Sylma::get('messages/xml/enable')) {
            
            if ($mArgument->isEmpty()) {
              
              $mContent = get_class($mArgument).' (vide)';
              
            } else {
              
              $oContainer = $mArgument->view(true, true, $bDecode);
              $oContainer->addClass('hidden');
              
              $mContent = array(get_class($mArgument), $oContainer);
            }
            
            $aValue = array(new HTML_Div($mContent, array('class' => 'element')), 'purple');
            
          } else $aValue = array(array(get_class($mArgument), ' => ', $mArgument->viewResume(160, false)), 'purple');
          
        } else if ($mArgument instanceof XML_Action) {
          
          if ($path = $mArgument->getPath()) {
            
            $oContainer = $mArgument->getPath()->parse();
            $oContainer->addClass('hidden');
          }
          else {
            
            $oContainer = null;
          }
          
          $mContent = array(get_class($mArgument), $oContainer);
          
          $aValue = array(new HTML_Div($mContent, array('class' => 'element')), 'magenta');
          
        } else if ($mArgument instanceof XML_Element) {
          
          if ($mArgument->isReal()) { // prevent from dom lost cf. http://bugs.php.net/bug.php?id=39593
            
            if (Sylma::get('messages/xml/enable')) {
              
              $oContainer = $mArgument->view(true, true, $bDecode);
              // $oContainer = new HTML_Span;
              $oContainer->addClass('hidden');
              
              $aValue = array(new HTML_Div(array(
                strtoupper($mArgument->getName()),
                $oContainer), array('class' => 'element')), 'blue');
              
            } else $aValue = array($mArgument->getName(), 'gray');// else $aValue = array(new HTML_Span($mArgument->viewResume(160, false)), 'gray');
          } else $aValue = array(new HTML_Span('-DEAD-', array('class' => 'element')), 'blue');
          
        } else if ($mArgument instanceof XML_NodeList) {
          
          if ($mArgument->length) {
          
            $mArgument->store();
            
            $iCount = $mArgument->length - 1;
            
            $oContent = new HTML_Div(null, array('style' => 'display: inline;'));
            foreach ($mArgument as $mKey => $mValue) {
              
              $oContent->add(view($mKey), ' => ', view($mValue, false));
              if ($iCount) $oContent->add(', ');
              
              $iCount--;
            }
            
            $mArgument->restore();
            
          } else $oContent = '';
          
          $aValue = array(new HTML_Div(xt('XML_NodeList[%s](%s)', new HTML_Strong($mArgument->length), $oContent), array('class' => 'array')), 'green');
          
        } else if ($mArgument instanceof XML_Comment) {
          
          $oContainer = new HTML_Tag('pre', xmlize($mArgument));
          //$oContainer = new HTML_Tag('pre', 'Comment');
          $oContainer->addClass('hidden');
          
          $aValue = array(new HTML_Div(array(
            'XML_Comment',
            $oContainer), array('class' => 'element')), 'blue');
          
        } else if ($mArgument instanceof XML_Text) {
          
          $aValue = array(stringResume($mArgument, $iMaxLength), 'orange');
          
        } else {
          
          $sValue = get_class($mArgument);
          //if (in_array($sValue, array('XML_Directory', 'XML_File'))) $sValue = stringResume($mArgument, 150);
          if (method_exists($mArgument, '__toString')) $sValue .= ' : '.stringResume($mArgument, 150);
          
          $aValue = array($sValue, 'red');
        }
        
      } else if ($mArgument === null) $aValue = array('NULL', 'magenta');
      else $aValue = array('undefined', 'orange');
      
      return new HTML_Div($aValue[0], array('style' => 'display: inline; color: '.$aValue[1].';'));
      
    } else {
      
      if (is_string($mArgument))
        $sValue = "'".stringResume($mArgument, $iMaxLength)."'";
      else if (is_array($mArgument)) {
        $sValue = 'array(';
        foreach ($mArgument as $sKey => $mValue) $sValue .= $sKey.'=>'.self::formatResource($mValue).', ';
        $sValue .= ')';
      } else if (is_object($mArgument)) {

        if ($mArgument instanceof XML_NodeList)
          $sValue = 'XML_NodeList('.$mArgument->length.')';
        else if ($mArgument instanceof XML_Element)
					$sValue = $mArgument;
				else
          $sValue = 'Classe : '.get_class($mArgument);
      } else if ($mArgument === null) $sValue = 'NULL';
      else $sValue = 'undefined';
      
      return $sValue;
    }
  }
  
  public static function getBacktrace() {
    
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
      
      // Arguments
      
      $oArguments = null;
      
      if (isset($aTrace['args']) && $aTrace['args']) {
        
        $aArguments = array();
        $iMaxLength = 120 / count($aTrace['args']);
        
        foreach ($aTrace['args'] as $mArgument) {
          
          $aArguments[] = self::formatResource($mArgument, false, $iMaxLength);
          $aArguments[] = new HTML_Strong(', ');
        }
        
        if ($aArguments) array_pop($aArguments);
        
        $oArguments = new HTML_Span($aArguments);
      }
      
      if (Sylma::get('messages/format/enable')) {
        
        $aResult[] = new HTML_Div(array(
          '[',
          new HTML_Span($aLines[$i], array('style' => 'color: blue; font-weight: bold;')),
          '] ',
          $sFile,
          $sClass,
          '::',
          new HTML_Strong($aTrace['function']),
          '(',  $oArguments, ')'));
          
      } else {
        
        $aResult[] = '['.$aLines[$i].'] '.$sFile.$sClass.'::'.$aTrace['function'].'(no display)'.new HTML_Br;
      }
      
      $i++;
    }
    
    return new HTML_Div(array_reverse($aResult), array('style' => 'margin: 3px; padding: 3px; border: 1px solid white; border-width: 1px 0 1px 0; font-size: 0.9em'));
    // self::addMessage(new HTML_Strong(t('Backtrace').' : ').implode('<br/>', $aResult), $sStatut);
    // return new XML_NodeList($aResult);
  }
  
  /* *** */
  
  /**
   * Create an object from a module array using @method buildClass()
   * 
   * @param array $sKey The key name of the class to use
   * @param array $aModule The module array containing the classes parameters.
   *  Ex : array('path' => 'modules/mymodule', classes' => array('keyname' => array('name' => 'classname', 'file' => 'filename')))
   * @param* array $aArguments The list of arguments to send to __construct method of object created
   * 
   * @return mixed The object created
   */
  public static function createObject(SettingsInterface $class, array $aArguments = array()) {
    
    $result = null;
    
    if (!$sClass = $class->get('name')) { // has name ?
      
      Sylma::throwException(txt('Cannot build object. No "name" defined in class'));
    }
    
    $result = self::buildClass($sClass, $class->get('file', false), $aArguments);
    
    return $result;
  }
  
  /**
   * Build an object from the class name with Reflection
   * @param string $sClass the class name
   * @param string $sFile the file where the class is declared to include
   * @param array $aArgument the arguments to use at the __construct call
   * @return null|object the created object
   */
  public static function buildClass($sClass, $sFile = '', $aArguments = array()) {
    
    $result = null;
    
    if ($sFile) {
      
      // include the file
      
      $sFile = Sylma::get('directories/root/path') . $sFile;
      
      if (file_exists($sFile)) require_once($sFile);
      else {
        
        dspm(xt('Cannot build object %s. File %s not found !',
          new HTML_Strong($sClass), new HTML_Strong($sFile)), 'action/error');
      }
    }
    
    if (!class_exists($sClass)) { // class exists ?
      
      dspm(xt('Cannot build object. The class %s does not seem to exists !',
        new HTML_Strong($sClass)), 'action/error');
      
    } else {
      
      // creation of object
      
      // caching classes improve performances
      if (array_key_exists($sClass, self::$aClasses)) $reflected = self::$aClasses[$sClass];
      else $reflected = self::$aClasses[$sClass] = new ReflectionClass($sClass);
      
      if ($aArguments) $result = $reflected->newInstanceArgs($aArguments);
      else $result = $reflected->newInstance();
      
      // These 2 following functions doesn't work, keep here for futur brainstorming
      //
      // $result = new $sClass(list($aArguments));
      // $result = call_user_func_array(array($sClass, '__construct'), $aArguments);
    }
    
    return $result;

  }
  
  public static function setDatabase($oDatabase) {
    
    self::$oDatabase = $oDatabase;
  }
  
  public static function getDatabase() {
    
    return self::$oDatabase;
  }
  
  public static function getUser() {
    
    return self::$user;
  }
  
  public static function setUser(User $user) {
    
    return self::$user = $user;
  }
  
  public static function getAbsolutePath($sTarget, $mSource = '') {
    
    if (!$sTarget || $sTarget{0} == '/' || $sTarget{0} == '*') return $sTarget;
    else {
      
      //if ($mSource == '/') $mSource = '';
      return $mSource.'/'.$sTarget;
    }
  }
  
  public static function getMessages() {
    
    return self::$oMessages;
  }
  
  public static function useMessages($bValue = null) {
    
    if ($bValue !== null) self::$bUseMessages = $bValue;
    
    return self::$bUseMessages;
  }
  
  public static function addMessage($mMessage = '- message vide -', $sPath = SYLMA_MESSAGES_DEFAULT_STAT, $aArgs = array()) {
    
    if (Controler::isAdmin() &&
      Sylma::get('messages/backtrace/enable') &&
      strstr($sPath, 'error') &&
      self::$iBacktraceLimit !== 0) {
      
      if (self::$iBacktraceLimit) self::$iBacktraceLimit--;
      $mMessage = array($mMessage, Controler::getBacktrace());
    }
    
    if (Sylma::get('debug/enable') && Sylma::get('messages/print/all')) { // || !self::useMessages()
      
      if (is_array($mMessage)) foreach ($mMessage as $mContent) echo $mContent.new HTML_Br;
      else echo $mMessage.new HTML_Br;
    }
    
    if (Sylma::get('debug/enable') && (Sylma::get('messages/log/enable'))) {
      
      if (is_array($mMessage)) foreach ($mMessage as $mContent) dspl($mContent."\n");
      else dspl($mMessage."\n");
    }
    
    if (self::getMessages()) self::getMessages()->addMessage(new Message($mMessage, $sPath, $aArgs));
    else if (Sylma::get('debug/enable') && Sylma::get('messages/print/hidden')) {
      
      echo view($mMessage);
    }
  }
  
  public static function useStatut($sStatut) {
    
    if (!Sylma::get('messages/rights/enable')) return true;
    else return self::getMessages()->useStatut($sStatut);
  }
  
  public static function buildSpecials() {
    
    if (!$oDirectory = self::getDirectory(SYLMA_PATH_INTERFACES)) {
      
      dspm(xt('Le répértoire des interfaces "%s" n\'existe pas !', new HTML_Strong(SYLMA_PATH_INTERFACES)), 'action/warning');
      
    } else {
      
      $oInterfaces = $oDirectory->browse(array('iml'));
      
      if (!$aInterfaces = $oInterfaces->query('//file')) {
        
        dspm(xt('Aucun fichier d\'interface à l\'emplacement "%s" indiqué !', new HTML_Strong(SYLMA_PATH_INTERFACES)), 'action/warning');
        
      } else {
        
        $oIndex = new XML_Document('interfaces');
        
        foreach ($aInterfaces as $oFile) {
          
          $sPath = $oFile->getAttribute('full-path');
          $oInterface = new XML_Document($sPath, MODE_EXECUTION);
          
          if ($oInterface->isEmpty()) {
            
            dspm(xt('Fichier d\'interface "%s" vide', new HTML_Strong($sPath)), 'action/warning');
            
          } else {
            
            if (!$sName = $oInterface->readByName('name')) {
              
              dspm(xt('Fichier d\'interface "%s" invalide, aucune classe n\'est indiquée !', new HTML_Strong($sPath)), 'action/warning');
              
            } else {
              
              $oIndex->addNode('interface', $sPath, array('class' => $sName));
            }
          }
        }
        
        $oIndex->save(SYLMA_PATH_INTERFACES_INDEX);
        dspm(xt('Interface d\'actions %s regénéré !', new HTML_Strong(SYLMA_PATH_INTERFACES_INDEX)), 'success');
      }
    }
  }
  
  public static function getAction() {
    
    return self::$sAction;
  }
  
  public static function getSystemPath() {
    
  	//return Sylma::get('directories/system');
    return self::$sSystemPath;
  }
  
  public static function getPath() {
    
    return self::$oPath;
  }
  
  public static function browseDirectory($aAllowedExt = array(), $aExcludedPath = array(), $iMaxLevel = null, $sOriginPath = '') {
    
    $oDocument = new XML_Document(self::getDirectory()->browse($aAllowedExt, $aExcludedPath, $iMaxLevel));
    
    if ($oDocument && !$oDocument->isEmpty()) $oDocument->getRoot()->setAttribute('path_to', $sOriginPath);
    
    return $oDocument;
  }
  
  public static function getDirectory($sPath = '') {
    
    if (self::$oDirectory && $sPath && $sPath != '/') {
      
      $aPath = explode('/', $sPath);
      array_shift($aPath);
      
      return self::$oDirectory->getDistantDirectory($aPath);
      
    } else return self::$oDirectory;
  }
  
  public static function getFile($sPath, $mSource = null, $bDebug = false) {
    
    if (self::getDirectory()) {
      
      $sPath = self::getAbsolutePath($sPath, $mSource);
      
      $aPath = explode('/', $sPath);
      array_shift($aPath);
      
      return self::getDirectory()->getDistantFile($aPath, $bDebug);
      
    } else return self::$oDirectory;
  }
  
  public static function isAdmin() {
    
    if (Sylma::get('debug/enable')) return true;
    else if (self::getUser()) return self::getUser()->isMember('0');
    else return false;
  }
  
  public static function isReady() {
    
    return self::$bReady;
  }
  
  public function __toString() {
    
    return t('[Controler]');
  }
}

