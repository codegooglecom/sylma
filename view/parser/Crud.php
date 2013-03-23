<?php

namespace sylma\view\parser;
use sylma\core, sylma\parser\reflector, sylma\dom;

class Crud extends reflector\handler\Elemented implements reflector\elemented {

  const VIEW_NS = 'http://2013.sylma.org/view';
  const VIEW_PREFIX = 'view';

  protected $global;
  protected $aRoutes = array();

  public function parseRoot(dom\element $el) {

    $el = $this->setNode($el);
    $this->setNamespace(self::VIEW_NS, self::VIEW_PREFIX);

    $this->parseChildren($el->getChildren());
  }

  protected function parseElementSelf(dom\element $el) {

    switch ($el->getName()) {

      case 'global' : $this->global = $this->parseComponent($el); break;
      default : $this->aRoutes[] = $this->parseComponent($el);
    }
  }

  public function getRoutes() {

    return $this->aRoutes;
  }

  public function getGlobal() {

    return $this->global;
  }

}

