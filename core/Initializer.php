<?php

namespace sylma\core;
use sylma\parser\action, sylma\core, sylma\storage\fs;

class Initializer extends module\Domed {

  const NS = 'http://www.sylma.org/core/initializer';
  const EXTENSION_DEFAULT = 'html';

  protected $iStartTime = 0;
  protected static $aStats = array();

  protected static $sArgumentClass = 'sylma\core\argument\Readable';

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
    $settings = new \sylma\core\argument\Filed($sSylma, array(\Sylma::NS));

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

    $this->setArguments($this->createArgument($settings->query()));
    $this->setSettings($this->getArguments());

    //$this->setArguments($settings);
    $this->setErrorReporting();

    //set_exception_handler("self::sendException");
    ini_set('session.gc_maxlifetime', $this->readArgument('session/lifetime'));

    $this->startSession();

    // if (\Sylma::read('db/enable')) $this->loadXDB();

    $this->setStartTime(microtime(true));

    require_once('storage/fs/Controler.php');

    // load directory without security
    $fs = new fs\Controler(\Sylma::ROOT, false, false, false);
    $fs->loadDirectory();
    \Sylma::setManager('fs', $fs);

    // load user
    $user = \Sylma::getManager('user');
    $user->load();

    if (\Sylma::isAdmin()) {

      require_once('debug/Functions.php');
    }

    // load directory with security
    $fs = new fs\Controler(\Sylma::ROOT, false, true, true);
    $fs->loadDirectory();
    \Sylma::setManager('fs', $fs);

    $this->setDirectory($fs->getDirectory());

    // Check for maintenance mode
    if ($sMaintenance = $this->loadMaintenance()) {

      return $sMaintenance;
    }
    //$this->getDirectory()->getSettings()->loadDocument();

    $path = $this->loadPath();
    \Sylma::setManager('path', $path);

    // The extension specify the window type

    // Parse of the request_uri, creation of the window - $_GET


    // Reload last alternatives mime-type results - $_SESSION['results']
    //self::loadResults();

    if ($file = $path->asFile()) {

      if (in_array($file->getExtension(), $this->query('images/extensions'))) {

        $window = $this->create('images', array($this, $this->get('images')));
        $sResult = $this->loadWindowFile($file, $window);
      }
      else {

        $sResult = $this->createWindowFile($file);
      }
    }
    else {

      $sResult = $this->runScript($path);
    }

    return $sResult;
  }

  protected function startSession() {

    session_start();
  }

  protected function loadPath() {

    $aGET = $this->loadGET();
    return $this->create('path', array($aGET['path'], null, $aGET['arguments'], false));
  }

  protected function runScript(core\request $path) {

    $sResult = '';
    $bProfile = $this->readArgument('debug/profile');

    if ($bProfile) {

      xhprof_enable(\XHPROF_FLAGS_CPU + \XHPROF_FLAGS_MEMORY);
    }

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
    else if (in_array($path->getExtension(), $this->getArgument('executables')->query())) {

      $sResult = $this->runExecutable($path);
    }
    else if (!$path->getExtension()) {

      $sResult = $this->buildWindow($path);
    }
    else {

      $this->throwException('No valid window defined');
    }

    if ($bProfile) {

      $xhprof_data = xhprof_disable();

      $XHPROF_ROOT = "xhprof/";
      include_once $XHPROF_ROOT . "/utils/xhprof_lib.php";
      include_once $XHPROF_ROOT . "/utils/xhprof_runs.php";

      $xhprof_runs = new \XHProfRuns_Default();
      $run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_testing");
    }

    return $sResult;
  }

  protected function runExecutable(core\request $path) {

    $sExtension = $path->getExtension();

    if ($this->getFactory()->findClass($sExtension, '', false)) {

      // with window

      $window = $this->create($sExtension, array($this));
      \Sylma::setManager('window', $window);

      $sResult = $this->loadObject($path, $window);
    }
    else {

     // no window

     $this->setHeaderContent($this->getMime($sExtension));

     $path->parse();
     $file = $path->asFile();

     if ($file->getExtension() !== 'vml') {

       $this->launchException('Can execute only view');
     }

     $sResult = (string) $this->prepareScript($file, $this->loadPOST(true), $path->getArguments());
    }

    return $sResult;
  }

  protected function loadAction(core\request $path) {

    $path->parse();

    return $this->createAction($path->getFile(), $path->getArguments()->asArray());
  }

  protected function createAction(fs\file $file, array $aArguments = array()) {

    return $this->create('action', array($file, $aArguments));
  }

  protected function prepareScript(fs\file $file, core\argument $args, core\argument $post, core\argument $contexts = null) {

    $builder = $this->getManager(self::PARSER_MANAGER);

    $result = $builder->load($file, array(
      'arguments' => $args,
      //'post' => $post,
      'contexts' => $contexts,
    ), $this->readArgument('debug/update', false), $this->readArgument('debug/run'), true);

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

  public function getExtensions() {

    return $this->getArgument('extensions')->query();
  }

  protected function buildWindow(core\request $path) {

    $sExtension = strtolower($path->getExtension());
    if (!$sExtension) $sExtension = self::EXTENSION_DEFAULT;

    $settings = $this->getArgument('window/' . $sExtension);
    $sCurrent = (string) $path;

    $path->parse();

    $aPaths = $this->buildWindowStack($settings, $sCurrent);
    $aPaths[] = (string) $path->asFile();

    $aPaths = array_reverse($aPaths);
    $sMain = array_pop($aPaths);

    $file = $this->getFile($sMain);

    if (!$file->checkRights(\Sylma::MODE_EXECUTE)) {

      $file = $this->getFile($this->read('window/error/window'));
      $aPaths = array($this->read('window/error/action'));
    }

    $args = $path->getArguments();
    $args->set('sylma-paths', $aPaths);

    $builder = $this->getManager(self::PARSER_MANAGER);

    return $builder->load($file, array(
      'arguments' => $args,
      //'post' => $this->loadPost(true),
    ), $this->readArgument('debug/update', false), $this->readArgument('debug/run'));
  }

  protected function buildWindowStack(core\argument $arg, $sPath) {

    $aResult = array();

    do {

      $content = $this->lookupRoute($arg, $sPath);
      $aResult[] = $content->read('action');

      $arg = $content->get('sub', false);

    } while ($arg);

    return $aResult;
  }

  /**
   * @return \sylma\core\argument
   */
  protected function lookupRoute(core\argument $args, $sCurrent) {

    $result = null;

    foreach ($args as $alt) {

      $sPattern = $alt->read('pattern', false);

      if (!$sPattern || preg_match($sPattern, $sCurrent)) {

        $result = $alt;
      }
    }

    if (!$result) {

      $this->launchException('No route found', get_defined_vars());
    }

    return $result;
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

  protected function loadObjectAction(core\request $path, core\window\action $window) {

    $action = $this->loadAction($path);

    $window->setAction($action, $path->getExtension());

    return $window->asString();
  }

  protected function loadObjectScript(core\request $path, core\window\scripted $window) {

    $window->setScript($path, $this->loadPOST(true));

    return $window->asString();
  }

  public function getMime($sExtension) {

    if (!$sResult = $this->readArgument("mime/$sExtension", false)) {

      $this->launchException('Unknown content type');
      //$sResult = $this->readArgument("mime/default");
    }

    return $sResult;
  }

  public function setHeaderCache($iTime, $bPublic = true) {

    header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + $iTime));
    //if ($bPublic) header('Cache-Control: public');

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

    if ($this->readArgument('maintenance/enable') && !\Sylma::isAdmin()) {

      $sResult = $this->getFile($this->readArgument('maintenance/file'))->execute();
    }

    return $sResult;
  }

  protected function setErrorReporting() {

    if (\Sylma::isAdmin() || $this->readArgument('debug/fatal', false)) {

      error_reporting(E_ALL);
/*
	    if (!ini_get('display_errors')) {

	      \Sylma::log(self::NS, sprintf('php.ini : display_errors is Off. Fatal error will not be shown.'));
	    }
*/
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

  public function loadPOST($bArgument = false) {

    return $bArgument ? $this->createArgument($_POST) : $_POST;
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