<?php

namespace sylma\parser\reflector\basic;
use \sylma\core, sylma\dom, sylma\storage\fs, symla\parser\reflector;

abstract class Componented extends Namespaced {

  protected $allowComponent = false;

  protected function allowComponent($mValue = null) {

    if (!is_null($mValue)) $this->allowComponent = $mValue;
    return $this->allowComponent;
  }

  abstract protected function parseComponent(dom\element $el);

  protected function createComponent(dom\element $el, $parser) {

    $class = $this->getFactory()->findClass($el->getName());
    $sName = 'component/' . $el->getName();

    return $this->create($sName, array($parser, $el, $class, false, $this->allowForeign(), $this->allowUnknown()));
  }

}

/*
  protected function createComponent(dom\element $el) {

    $aClass = explode('\\', get_class($this));

    \Sylma::load('/core/functions/Text.php');
    $sName = text\toggleCamel($el->getName());

    if ($dir = $this->getComponentsDirectory($el->getNamespace())) {

      $sClass = str_replace('/', '\\', (string) $dir) . $sName;
    }
    else {

      $sClass = '\\' . implode('\\', array_slice($aClass, 0, -1)) . '\\' . $sName;
    }

    return new $sClass($this, $el);
  }

  protected function getComponentsDirectory($sNamespace) {

    return $this->componentsDirs[$sNamespace];
  }

  protected function setComponentsDirectory(fs\directory $dir, $sNamespace = '') {

    if (!$sNamespace) $sNamespace = $this->getNamespace();

    $this->componentsDirs[$sNamespace] = $dir;
  }
*/