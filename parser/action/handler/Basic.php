<?php

namespace sylma\parser\action\handler;
use sylma\core, sylma\parser, sylma\storage\fs, sylma\dom;

require_once('core/module/Argumented.php');

require_once('parser/action.php');
require_once('core/stringable.php');

/**
 * "Controller free" class.
 */
class Basic extends core\module\Argumented implements parser\action, core\stringable {

  const CONTROLER_ALIAS = 'action';

  protected static $sArgumentClass = 'sylma\core\argument\Filed';
  protected static $sArgumentFile = 'core/argument/Filed.php';

  const FS_CONTROLER = 'fs/editable';

  protected $file;
  protected $controler;

  protected $baseDirectory = null;

  protected $aArguments = array();
  protected $aContexts = array();

  protected $action = null;
  protected $bRunned = false;

  public function __construct(fs\file $file, array $aArguments = array(), fs\directory $base = null) {

    $this->setArguments($aArguments);

    $this->setControler(\Sylma::getControler(self::CONTROLER_ALIAS));
    //$this->setDirectory(__file__);

    $this->setNamespace($this->getControler()->getNamespace());

    $this->setFile($file);

    if ($base) $this->setBaseDirectory($base);
    else $this->setBaseDirectory($file->getParent());
  }

  protected function getBaseDirectory() {

    return $this->baseDirectory;
  }

  protected function setBaseDirectory(fs\directory $baseDirectory) {

    $this->baseDirectory = $baseDirectory;
  }

  protected function setFile(fs\file $file) {

    $this->file = $file;
  }

  protected function getFile() {

    return $this->file;
  }

  protected function getAction() {

    return $this->action;
  }

  protected function setAction(parser\action\cached $action) {

    $this->action = $action;
  }

  public function getContexts() {

    return $this->aContexts;
  }

  public function setContexts(array $aContexts) {

    $this->aContexts = $aContexts;
  }

  public function setArgument($sPath, $mValue) {

    return parent::setArgument($sPath, $mValue);
  }

  /**
   * Allow management of multiple calls on same action
   * @return parser\action\cached
   */
  protected function runAction() {

    if (!$this->isRunned()) {

      $this->setAction($this->loadAction($this->getFile()));
      $this->getAction()->loadAction();
      $this->isRunned(true);
    }

    return $this->getAction();
  }

  protected function isRunned($mValue = null) {

    if (!is_null($mValue)) $this->bRunned = $mValue;

    return $this->bRunned;
  }

  protected function loadAction(fs\file $file) {

    $result = null;
    $sName = $file->getName() . '.php';

    //$sDirectory = (string) $file->getParent();
    //$sDirectory = $sDirectory ? $sDirectory : '/';

    $fs = $this->getControler('fs/cache');
    $tmpDir = $fs->getDirectory()->addDirectory((string) $file->getParent());

    if ($tmpDir) {

      $tmpFile = $tmpDir->getFile($sName, 0);
    }

    if (!$tmpDir || !$tmpFile || $tmpFile->getLastChange() < $file->getLastChange() || \Sylma::read('action/update')) {

      $compiler = $this->getControler()->create('compiler', array($this->getControler()));
      $tmpFile = $compiler->build($file, $this->getBaseDirectory());
    }

    if ($this->getControler()->readArgument('debug/run')) {

      $result = $this->createCached($tmpFile, $this->getBaseDirectory(), $this, $this->getContexts(), $this->getArguments()->query());
    }
    else {

      $this->throwException('No result, DEBUG_RUN set to TRUE');
    }

    return $result;
  }

  protected function createCached(fs\file $file, fs\directory $dir, $controler, array $aContexts, array $aArguments) {

    $result = $this->getControler()->create('cached', array($file, $dir, $controler, $aContexts, $aArguments));

    foreach ($this->getControlers() as $sName => $controler) {

      $result->setControler($controler, $sName);
    }
    
    return $result;
  }

  protected function parseString(core\stringable $mVal) {

    return $mVal->asString();
  }

  protected function parseObject(parser\action\cached $mVal) {

    return $mVal->asObject();
  }

  public function getContext($sContext) {

    $action = $this->runAction();
    return $action->getContext($sContext);
  }

  public function asObject() {

    $action = $this->runAction();
    return $this->parseObject($action);
  }

  public function asArray() {

    $action = $this->runAction();
    return $action->asArray();
  }

  public function asString($iMode = 0) {

    $action = $this->runAction();
    return $this->parseString($action);
  }

  protected function parseDOM(dom\domable $val) {

    return $val->asDOM();
  }

  public function asDOM() {

    $action = $this->runAction();
    return $this->parseDOM($action);
  }
}