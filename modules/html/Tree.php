<?php

namespace sylma\modules\html;
use sylma\core, sylma\dom, sylma\template, sylma\parser\languages\common;

class Tree extends template\parser\ArgumentTree implements template\parser\tree {

  protected $var;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);

    $this->setDirectory(__FILE__);

    if (!$this->getSettings(false)) {

      $this->setSettings('reflector.xml');
    }

    $window = $this->getWindow();

    $args = $window->getVariable('arguments');
    $contexts = $window->getVariable('contexts');

    $var = $this->createObject('cached', array($args, $contexts));
    $this->setVar($var);

    $this->getRoot()->setReturn('result');

    $view = $this->getParser();
    $view->setReturn($this->getVar()->call('prepare', array($view->getResult())));

    $var->insert();
  }

  protected function setVar(common\_callable $var) {

    $this->var = $var;
  }

  /**
   * @return common\_callable
   */
  protected function getVar() {

    return $this->var;
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'content' : $result = $this->reflectContent(); break;
      default :

        $result = parent::reflectApplyFunction($sName, $aPath, $sMode, $bRead, $sArguments, $aArguments);
    }

    return $result;
  }

  protected function reflectContent() {

    return $this->getVar()->call('getContent');
  }
}