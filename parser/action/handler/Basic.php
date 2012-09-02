<?php

namespace sylma\parser\action\handler;
use sylma\core, sylma\parser, sylma\storage\fs, sylma\dom;

\Sylma::load('../../Handler.php', __DIR__);

\Sylma::load('/parser/action.php');
\Sylma::load('/core/stringable.php');

/**
 * "Controller free" class.
 */
class Basic extends parser\Handler implements parser\action, core\stringable {

  const CONTROLER_ALIAS = 'action';

  protected static $sArgumentClass = 'sylma\core\argument\Filed';
  protected static $sArgumentFile = 'core/argument/Filed.php';

  const FS_CONTROLER = 'fs/editable';

  protected $controler;

  protected $aArguments = array();
  protected $aContexts = array();

  protected $action = null;
  protected $bRunned = false;

  public function __construct(fs\file $file, array $aArguments = array(), fs\directory $base = null) {

    $this->setArguments($aArguments);

    $this->setControler(\Sylma::getControler(self::CONTROLER_ALIAS));
    //$this->setDirectory(__file__);

    $this->setNamespace($this->getControler()->getNamespace());

    parent::__construct($file, $base);
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

      $this->setAction($this->load());
      $this->getAction()->loadAction();
      $this->isRunned(true);
    }

    return $this->getAction();
  }

  protected function isRunned($mValue = null) {

    if (!is_null($mValue)) $this->bRunned = $mValue;

    return $this->bRunned;
  }

  protected function createCached(fs\file $file, fs\directory $dir, $controler, array $aContexts, array $aArguments) {
  }

  protected function loadCache(fs\file $file) {

    $result = $this->getControler()->create('cached', array($file, $this->getBaseDirectory(), $this, $this->getContexts(), $this->getArguments()->query()));

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