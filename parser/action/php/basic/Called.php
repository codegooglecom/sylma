<?php

namespace sylma\parser\action\php\basic;
use \sylma\core, \sylma\parser\action\php;

require_once('Controled.php');
require_once('core/argumentable.php');

abstract class Called extends Controled implements core\argumentable  {

  protected $sName;

  protected $return;

  protected $aArguments;

  public function getName() {

    return $this->sName;
  }

  public function setName($sName) {

    $this->sName = $sName;
  }

  /**
   * @return array
   */
  public function getArguments() {

    return $this->aArguments;
  }

  public function setArguments(array $aArguments) {

    $this->aArguments = $aArguments;
  }

  public function getReturn() {

    return $this->return;
  }
  
  protected function setReturn(php\_instance $return) {

    $this->return = $return;
  }

  protected function parseArguments($aArguments) {

    $window = $this->getControler();
    $aResult = array();

    foreach ($aArguments as $mVar) {

      $aResult[] = $window->argToInstance($mVar);
    }

    return $aResult;
  }

  public function getVar() {

    $window = $this->getControler();

    $var = $this->getReturn();

    if ($var instanceof ObjectInstance) $sAlias = 'object-var';
    else $sAlias = 'simple-var';

    $var = $window->create($sAlias, array($window, $var, $window->getVarName()));
    $assign = $window->create('assign', array($window, $var, $this));

    $window->add($assign);

    return $var;
  }
}