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

  public function reflectRead(array $aArguments = array()) {

    //return $this->getParser()->trimString($this->getOptions()->read());
  }

  public function reflectApplyDefault($sPath, array $aPath = array(), $sMode = '', $bRead = false) {

    return $this->getDummy()->call('read', array($sPath, false));
  }
}
