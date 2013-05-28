<?php

namespace sylma\core;
use sylma\parser\action, sylma\core, sylma\storage\fs;

class Initializer extends module\Filed {

  const NS = 'http://www.sylma.org/core/initializer';
  const EXTENSION_DEFAULT = 'html';

  protected $iStartTime = 0;
  protected static $aStats = array();

  //protected static $sArgumentClass = 'sylma\core\argument\Iterator';
  //protected static $sArgumentFile = 'core/argument/Iterator.php';

  /**
   * 2. Load global settings
   *
   * @param type $sServer
   * @param type $sSylma
   * @return XArguments
   */
  public function loadSettings($sServer, $sSylma) {

    //$settings = $this->createArgument($sSylma, \Sylma::NS);
    //require_once(self::$sArgumentFile);
    $settings = new self::$sArgumentClass($sSylma, array(\Sylma::NS));

    if ($sServer) $settings->mergeFile($sServer);

    return $settings;
  }

  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }

  protected function getStartTime() {

    return $this->iStartTime;
  }

  protected function setStartTime($iTime) {

    $this->iStartTime = $iTime;
  }

  public function getElapsedTime() {

    return microtime(true)- $this->getStartTime();
  }

  public function run($settings) {

    if (\Sylma::read('debug/enable')) {

      require_once('debug/Functions.php');
    }

    $this->setArguments($settings);
    $this->setErrorReporting();

    //set_exception_handler("self::sendException");
    ini_set('session.gc_maxlifetime', $this->readArgument('session/lifetime'));

    session_start();

    // if (\Sylma::read('db/enable')) $this->loadXDB();

    $this->setStartTime(microtime(true));

    require_once('storage/fs/Controler.php');

    // load directory without security
    $fs = new fs\Controler(\Sylma::ROOT, false, false, false);
    $fs->loadDirectory();
    \Sylma::setControler('fs', $fs);

    // load user
    $user = \Sylma::getControler('user');
    $user->load();

    // load directory with security
    $fs = new fs\Controler(\Sylma::ROOT, false, true, true);
    $fs->loadDirectory();
    \Sylma::setControler('fs', $fs);

    // Check for maintenance mode
    if ($sMaintenance = $this->loadMaintenance()) return $sMaintenance;

    $this->setDirectory($fs->getDirectory());
    //$this->getDirectory()->getSettings()->loadDocument();

    $aGET = $this->loadGET();

    $path = $this->create('path', array($aGET['path'], null, $aGET['arguments'], false));

    // The extension specify the window type

    // Parse of the request_uri, creation of the window - $_GET


    // Reload last alternatives mime-type results - $_SESSION['results']
    //self::loadResults();

    $sResult = '';

    if ($file = $path->asFile()) {

      // A file
      $sResult = $this->createWindowFile($file);
    }
    else {

      $sExtension = $path->parseExtension(true);

      if ($path->getExtension() == $this->readArgument('redirect/extension')) {

        // Redirect
        $action = $this->loadAction($path);
        $redirect = $action->asObject();

        if (!$redirect instanceof core\redirect) {

          $this->throwException('Cannot redirect at that adress');
        }

        $this->runRedirect($redirect);
      }
      else if (in_array($path->getExtension(), $this->getArgument('executables')->asArray())) {

        // Normal action
        $window = $this->create($sExtension, array($this));
        \Sylma::setManager('window', $window);

        $sResult = $this->loadObject($path, $window);
      }
      else if (!$path->getExtension()) {

        // HTML action
        $window = $this->createWindowAction($sExtension);
        \Sylma::setManager('window', $window);

        $sResult = $this->loadWindowContent($path, $window);
      }
      else {

        $this->throwException('No valid window defined');
      }
    }

    return $sResult;
  }

  protected function loadObject(core\request $path, $window) {

    $path->parse();
    $file = $path->getFile();

    switch ($file->getExtension()) {

      case 'eml' : $result = $this->loadObjectAction($path, $window); break;
      case 'vml' : $result = $this->loadObjectScript($path, $window); break;

      default :

        $this->launchException(sprintf('Unknown exectuable extension : %s', $file->getExtension()));
    }

    return $result;
  }

  public function getExtensions() {

    return $this->getArgument('extensions')->query();
  }

  protected function loadAction(core\request $path) {

    $path->parse();

    return $this->create('action', array($path->getFile(), $path->getArguments()->asArray()));
  }

  /**
   * Window action is a action that load an action as argument
   * @param string $sExtension
   * @return parser\action
   */
  protected function createWindowAction($sExtension) {

    $sExtension = strtolower($sExtension);
    if (!$sExtension) $sExtension = self::EXTENSION_DEFAULT;

    $settings = $this->getArgument('window/' . $sExtension);

    $sAlias = $sExtension;
    $sPath = $settings->read('action');

    $window = $this->create($sAlias, array($this->getFile($sPath)));

    return $window;
  }

  protected function loadWindowContent(core\request $path, action\handler $window) {

    $path->parse();
    $file = $path->asFile();

    try {

      switch ($file->getExtension()) {

        case 'eml' : $content = $this->prepareAction($path, $window); break;
        case 'vml' : $content = $this->prepareScript($path, $window); break;
        default :

          $this->launchException('Unknown extension for window content');
      }

      if (!$content) {

        $this->launchException('No content for main window');
      }

      $window->setArgument('content', $content);
      $window->setArgument('current', $path);

      $sResult = $window->asString();

    }
    catch (core\exception $e) {

      if (\Sylma::read('debug/enable')) {

        $e->save(false);
        //throw $e;
      }
      else {

        header('HTTP/1.0 404 Not Found');
      }

      //$window = $this->createWindowAction('');
      $action = $this->create('action', array($this->getFile($this->readArgument('error/action'))));

      $window->setArgument('content', $action);
      $window->setArgument('current', $path);

      $sResult = $window->asString();
    }

    //if ($action->doRedirect()) self::doHTTPRedirect($oResult);

    return $sResult;
  }

  protected function prepareScript(core\request $path, action\handler $window) {

    $builder = $this->getManager(self::PARSER_MANAGER);

    $result = $builder->load($path->asFile(), array(
      'arguments' => $path->getArguments(),
      'contexts' => $window->getContexts(),
      //'post' => $post,
    ), $this->readArgument('debug/update', false), $this->readArgument('debug/run'));

    return $result;
  }

  protected function prepareAction(core\request $path, action\handler $window) {

    $result = $this->loadAction($path);
    $result->setContexts($window->getContexts());
    $result->setParentParser($window);

    return $result;
  }

  protected function createWindowFile(fs\file $file) {

    $sResult = '';

    switch ($file->getExtension()) {

      case 'php' :

        $this->throwException('Cannot read php files');

      case 'jpg' :
      case 'jpeg' :
      case 'png' :
      case 'gif' :
      default :

        $window = $this->create('window', array($this));
        $sResult = $this->loadWindowFile($file, $window);

      break;
    }

    return $sResult;
  }

  protected function loadWindowFile(fs\file $file, core\window\file $window) {

    $window->setFile($file);
    return $window->asString();
  }

  protected function loadObjectAction(core\request $path, core\window\action $window) {

    $action = $this->loadAction($path);

    $window->setAction($action, $path->getExtension());

    return $window->asString();
  }

  protected function loadObjectScript(core\request $path, core\window\scripted $window) {

    $window->setScript($path, $this->createArgument($this->loadPOST()));

    return $window->asString();
  }

  public function getError() {

    return $this->getFile($this->readArgument('error/html'))->execute();
  }

  public function getMime($sExtension) {

    if (!$sResult = $this->readArgument("mime/$sExtension", false)) {

      $sResult = $this->readArgument("mime/default");
    }

    return $sResult;
  }

  public function setHeaderCache($iTime) {

    header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + $iTime));
    header('Cache-Control: public');
    header_remove('Pragma');
  }

  public function setHeaderContent($sMime, $sCharset = 'utf-8') {

    $sType = "Content-Type: $sMime;";
    if ($sCharset) $sType .= "charset=$sCharset";

    header($sType);
    //header("Vary: Accept");
  }

  protected function loadMaintenance() {

    $sResult = '';

    if ($this->readArgument('maintenance/enable')) $sResult = 'site en maintenance';

    return $sResult;
  }

  protected function setErrorReporting() {

    if (\Sylma::read('debug/enable')) {

      error_reporting(E_ALL);

	    if (!ini_get('display_errors')) {

	      \Sylma::log(self::NS, sprintf('php.ini : display_errors is Off. Fatal error will not be shown.'));
	    }
    }
    else {

      error_reporting(0);
    }

    libxml_use_internal_errors(false);
  }

  protected function loadGET() {

    $aResult = array();

    if (array_key_exists('q', $_GET) && $_GET['q']) {

      $aResult['path'] = '/' . $_GET['q'];
      unset($_GET['q']);
    }
    else {

      $aResult['path'] = '/';
    }

    $aResult['arguments'] = $_GET;

    return $aResult;
  }

  protected function loadPOST() {

    return $_POST;
  }

  /**
   * Load Redirect session var, if present means it has been redirected - $_SESSION['redirect'], $_POST in 'post'
   * @return core\redirect
   */
  public function loadRedirect() {

    $redirect = $this->create('redirect');

    // Une redirection a été effectuée

    if (array_key_exists('redirect', $_SESSION)) {

      $redirect = unserialize($_SESSION['redirect']);
      unset($_SESSION['redirect']);

      // Récupération des messages du Redirect et suppression

      if (!$redirect instanceof core\redirect) {

        $this->throwException('Cannot get back the redirect');
      }

    } else {

      if ($aPost = $this->loadPost()) $redirect->setArgument('post', $aPost);
    }

    return $redirect;
  }

  protected function runRedirect(core\redirect $redirect) {

    $_SESSION['redirect'] = serialize($redirect);

    if (!$sPath = (string) $redirect) {

      $this->throwException('Bad redirection');
    }

    header("Location: $sPath");
  }

  protected function loadResults() {

    if (!array_key_exists('results', $_SESSION)) $_SESSION['results'] = array();
    self::$aResults = $_SESSION['results'];
  }

  public function addStat($sPath) {

    if (!isset(self::$aStats[$sPath])) self::$aStats[$sPath] = 0;
    self::$aStats[$sPath]++;
  }

  public function getStats() {

    $arg = new core\argument\advanced\Treed(self::$aStats);

    $aTree = $arg->parseTree();
    $aResult = $arg->renderTree($aTree);

    return $aResult[1];
  }
}