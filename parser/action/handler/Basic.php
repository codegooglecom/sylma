<?php

namespace sylma\parser\action\handler;
use sylma\core, sylma\parser\action, sylma\storage\fs, sylma\dom;

/**
 * "Controller free" class.
 */
class Basic extends core\module\Filed implements action\handler, core\stringable, core\tokenable {

  const CONTROLER_ALIAS = 'action';

  protected static $sArgumentClass = 'sylma\core\argument\Readable';
  //protected static $sArgumentClass = 'sylma\core\argument\Filed';
  //protected static $sArgumentFile = 'core/argument/Filed.php';

  //const FILE_MANAGER = 'fs/editable';

  protected $controler;

  protected $aArguments = array();
  protected $contexts;

  protected $action = null;
  protected $parentParser;
  protected $bRunned = false;
  protected $bExceptions = true;

  public function __construct(fs\file $file, array $aArguments = array(), fs\directory $dir = null) {

    $this->setArguments($aArguments);
    $this->setManager(\Sylma::getManager(self::CONTROLER_ALIAS));

    $this->setNamespace($this->getControler()->getNamespace());

    $this->setFile($file);

    if ($dir) $this->setDirectory($dir);
    else $this->setDirectory($file->getParent());
  }

  protected function getAction() {

    return $this->action;
  }

  protected function setAction(action\cached $action) {

    $this->action = $action;
  }

  public function getContexts() {

    return $this->contexts;
  }

  public function setContexts(core\argument $contexts) {

    $this->contexts = $contexts;
  }

  public function setParentParser(action\handler $parent) {

    $this->parentParser = $parent;
  }

  public function getParentParser($bRoot = false) {

    if ($bRoot) {

      $result = $this->parentParser ? $this->parentParser->getParentParser($bRoot) : $this;
    }
    else {

      $result = $this->parentParser;
    }

    return $result;
  }

  public function loadParser($sNamespace) {

    if (!$this->getAction()) {

      $this->throwException('Cannot load parser, action not yet loaded');
    }

    return $this->getAction()->loadParser($sNamespace);
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

      $this->isRunned(true);

      // temporary change current parser, then restore it
      $sContext = 'action/current';

      $manager = $this->getControler('parser');
      //$parent = $manager->getContext($sContext, false);
      $manager->setContext($sContext, $this);

      try {

        $aArguments = array($this->getDirectory(), $this, $this->getContexts(), $this->getArguments()->query(), $this->getControlers());

        if ($action = $this->getControler()->load($this->getFile(), $aArguments)) {

          $this->setAction($action);
          $this->getAction()->loadAction();
        }
      }
      catch (core\exception $e) {

        $e->addPath('Compiler error');
        $e->addPath($this->getFile()->asToken());

        if ($this->useExceptions()) throw $e;
        else $e->save(false);
      }

      //$manager->setContext($sContext, $parent);
    }

    return $this->getAction();
  }

  protected function isRunned($mValue = null) {

    if (!is_null($mValue)) $this->bRunned = $mValue;

    return $this->bRunned;
  }

  protected function parseString(core\stringable $mVal) {

    return $mVal->asString();
  }

  protected function parseObject(action\cached $mVal) {

    return $mVal->asObject();
  }

  public function getContext($sContext) {

    $action = $this->runAction();
    return $action ? $action->getContext($sContext) : null;
  }

  public function asObject() {

    $action = $this->runAction();
    return $action ? $this->parseObject($action) : null;
  }

  public function asArray() {

    $action = $this->runAction();
    return $action ? $action->asArray() : null;
  }

  public function asString($iMode = 0) {

    $action = $this->runAction();
    return $action ? $this->parseString($action) : null;
  }

  protected function parseDOM(dom\domable $val) {

    return $val->asDOM();
  }

  public function useExceptions($mValue = null) {

    if (!is_null($mValue)) $this->bExceptions = $mValue;
    return $this->bExceptions || \Sylma::read('debug/action');
  }

  public function asToken() {

    return 'Action : ' . $this->getFile();
  }

  public function asDOM() {

    $action = $this->runAction();
    return $action ? $this->parseDOM($action) : NULL;
  }
}