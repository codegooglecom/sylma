<?php

namespace sylma\storage\xml\tree;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template as tpl, sylma\parser\reflector;

class _Callable extends reflector\component\Foreigner implements tpl\parser\tree {

  protected $var;
  protected $dummy;

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
    $this->setDummy($var);

    $var->insert();
  }

  protected function reflectReturn($sMethod) {

    $this->getRoot()->setReturn('result');

    $view = $this->getParser();
    $view->setReturn($this->getDummy()->call($sMethod, array($view->getResult())));
  }

  protected function setDummy(common\_var $var) {

    $this->dummy = $var;
  }

  /**
   * @usedby sql\template\component\Table::startStatic()
   * @return common\_var
   */
  public function getDummy() {

    return $this->dummy;
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    $aFunctionArguments = $this->getParser()->getPather()->parseArguments($sArguments);

    switch ($sName) {

      //case 'init' : $result = $this->reflectInit(); break;
      case 'return' : $result = $this->reflectReturn($aFunctionArguments[0]); break;
      default :

        if ($aFunctionArguments) {

          $aArguments = $aFunctionArguments;
        }

        $result = $this->reflectCall($sName, $aArguments);
    }

    return $result;
  }

  protected function reflectCall($sName, array $aArguments) {

    if (!$this->getDummy(false)) {

      $this->launchException("Function '$sName()' unknown or object undefined");
    }

    return $this->getDummy()->call($sName, $aArguments);
  }

  public function reflectApply($sMode) {

    $this->launchException('Cannot apply, no tree defined');
  }

  public function reflectApplyDefault($sPath, array $aPath = array(), $sMode = '', $bRead = false, array $aArguments = array()) {

    $this->launchException('Cannot apply, no tree defined');
  }

  public function reflectRead() {

    $this->launchException('Not implemented');
  }

  public function asToken() {

    return get_class($this);
  }
}
