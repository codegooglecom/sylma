<?php

namespace sylma\crud\_list;
use sylma\core, sylma\storage\xml, sylma\parser\reflector, sylma\parser\languages\common;

class Tree extends xml\tree\_Callable {

   public function __construct(reflector\domed $parser, core\argument $arg = null, array $aNamespaces = array()) {

     parent::__construct($parser, $arg, $aNamespaces);
   }

  public function loadDummy() {

    $window = $this->getWindow();
    $dummy = $this->createDummy('dummy', array($window->getVariable('arguments'), $window->getVariable('post')), null, true);

    $this->setDummy($dummy);

    return $dummy->getInsert();
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'default' : $result = $this->reflectFunctionDefault($aPath, $sMode, $bRead, $aArguments); break;

      default :

        $result = parent::reflectApplyFunction($sName, $aPath, $sMode, $bRead, $sArguments, $aArguments);
    }

    return $result;
  }

  public function reflectRead(array $aArguments = array()) {

    //return $this->getParser()->trimString($this->getOptions()->read());
  }

  protected function reflectFunctionDefault(array $aPath, $sMode, $bRead = false, array $aArguments = array()) {

    $sPath = $this->getWindow()->parse(array_shift($aArguments));

    return $this->reflectApplyDefault($sPath, $aPath, $sMode, $bRead, $aArguments);
  }

  public function reflectApplyDefault($sPath, array $aPath = array(), $sMode = '', $bRead = false, array $aArguments = array()) {

    return $this->getDummy()->call($bRead ? 'read' : 'query', array($sPath, false));
  }
}
