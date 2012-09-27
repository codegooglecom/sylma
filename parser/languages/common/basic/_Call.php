<?php

namespace sylma\parser\languages\common\basic;
use \sylma\core, \sylma\parser\languages\common, \sylma\parser\languages\php;

\Sylma::load('Controled.php', __DIR__);

\Sylma::load('../linable.php', __DIR__);
\Sylma::load('/core/argumentable.php');

abstract class _Call extends Controled implements common\linable, core\argumentable  {

  protected $sName;

  protected $return;

  protected $aArguments;

  protected $var;

  protected $called;

  public function __construct(common\_window $controler, $called, $sName, common\_instance $return, array $aArguments = array()) {

    $this->setCalled($called);
    $this->setName($sName);
    $this->setControler($controler);
    $this->setReturn($return);
//dspf($aArguments, 'error');
    $this->setArguments($this->parseArguments($aArguments));
  }

  protected function setCalled($called) {

    if ($called instanceof self || $called instanceof common\_object) {

      $this->called = $called;
    }
    else {

      $this->throwException(sprintf('Cannot call non object %s', $this->show($called)));
    }
  }

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

  protected function setReturn(common\_instance $return) {

    $this->return = $return;
  }

  protected function parseArguments($aArguments) {

    $window = $this->getControler();
    $aResult = array();

    foreach ($aArguments as $mVar) {

      $arg = $window->argToInstance($mVar);

      if ($arg instanceof common\basic\_Object) {

        $this->getControler()->throwException('Cannot add object instance here');
      }

      $aResult[] = $arg;
    }

    return $aResult;
  }

  /**
   * Build an (optionnaly temporary) variable assigned with this call
   * @param boolean $bInsert
   * @return common\_var
   */
  public function getVar($bInsert = true) {

    if (!$this->var) {

      $this->var = $this->getControler()->createVar($this);
    }

    if ($bInsert) $this->var->insert();

    return $this->var;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'call' => array(
          '@name' => $this->getName(),
          'called' => $this->called,
          '#argument' => $this->getArguments(),
      ),
    ));
  }

}
