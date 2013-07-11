<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common;

class Reference extends sql\template\component\Reference {

  protected function reflectID() {

    return $this->getParent()->getResult();
  }

  protected function reflectFunctionRef(array $aPath, $sMode, array $aArguments = array()) {

    $el = $this->getElementRef();
    $window = $this->getWindow();

    $item = $window->createVariable('', '\sylma\core\argument');
    $loop = $window->createLoop($this->getParent()->getElementArgument($this->getName(), 'get'), $item);

    $el->addElement($this->getForeign(), '', $this->reflectID());
    $el->setSource($item);

    $aContent[] = $window->toString($this->getParser()->parsePathToken($el, $aPath, $sMode, false, $aArguments));
    $aContent[] = $el->asArgument();

    $loop->setContent($aContent);

    $this->getParent()->addTrigger(array($loop));
  }
}

