<?php

namespace sylma\storage\xml\tree;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template as tpl, sylma\parser\reflector;

class Callable extends reflector\component\Foreigner implements tpl\parser\tree {

  protected $var;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->setDirectory(__FILE__);
    $this->loadDefaultArguments();
  }

  protected function setSettings($args = null, $bMerge = true) {

    parent::setSettings($args, $bMerge);

    if ($this->getFactory()->findClass('cached', '', false)) {

      $this->reflectInit();
    }
  }

  protected function reflectInit() {

    $window = $this->getWindow();

    $args = $window->getVariable('arguments');
    $post = $window->getVariable('post');
    $contexts = $window->getVariable('contexts');

    $var = $this->createObject('cached', array($args, $post, $contexts));
    $this->setVar($var);

    $var->insert();
  }

  protected function reflectReturn($sMethod) {

    $this->getRoot()->setReturn('result');

    $view = $this->getParser();
    $view->setReturn($this->getVar()->call($sMethod, array($view->getResult())));
  }

  protected function setVar(common\_callable $var) {

    $this->var = $var;
  }

  /**
   * @return common\_callable
   */
  protected function getVar($bDebug = true) {

    if (!$this->var && $bDebug) {

      $this->launchException('No object associated');
    }

    return $this->var;
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    $aFunctionArguments = $this->getParser()->getPather()->parseArguments($sArguments);

    switch ($sName) {

      //case 'init' : $result = $this->reflectInit(); break;
      case 'return' : $result = $this->reflectReturn($aFunctionArguments[0]); break;
      default :

        $result = $this->reflectCall($sName, $aArguments);
    }

    return $result;
  }

  protected function reflectCall($sName, array $aArguments) {

    if (!$this->getVar(false)) {

      $this->launchException("Function '$sName()' unknown or object undefined");
    }

    return $this->getVar()->call($sName, $aArguments);
  }

  public function reflectApply($sMode) {

    $this->launchException('Cannot apply, no tree defined');
  }

  public function asToken() {

    return $this->show($this->getVar());
  }
}