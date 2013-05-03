<?php

namespace sylma\view\parser;
use sylma\core, sylma\parser\reflector, sylma\dom;

class Crud extends reflector\handler\Elemented implements reflector\elemented {

  const VIEW_NS = 'http://2013.sylma.org/view';
  const VIEW_PREFIX = 'view';

  protected $global;
  protected $default;

  protected $aRoutes = array();
  protected $aGroups = array();

  public function parseRoot(dom\element $el) {

    $el = $this->setNode($el);
    $this->setNamespace(self::VIEW_NS, self::VIEW_PREFIX);

    $this->parseChildren($el->getChildren());
  }

  protected function parseElementSelf(dom\element $el) {

    switch ($el->getName()) {

      case 'global' : $this->global = $this->parseComponent($el); break;
      case 'view' :
      case 'route' : $this->aRoutes[] = $this->parseAliased($el); break;
      case 'group' : $this->parseGroup($el); break;
      default :

        $this->launchException('Unknown route', get_defined_vars());
    }
  }

  protected function parseAliased(dom\element $el) {

    $result = $this->parseComponent($el);

    if (!$result->getAlias(true)) {

      $this->setDefault($result);
    }

    return $result;
  }

  protected function parseGroup(dom\element $el) {

    $group = $this->parseComponent($el);
    $this->aGroups[$group->getName()] = $group;
  }

  public function getGroup($sName) {

    return $this->aGroups[$sName];
  }
  
  protected function setDefault(crud\Basic $route) {

    if ($this->default) {

      $this->launchException('Cannot have more than one default route', get_defined_vars());
    }

    $this->default = $route;
  }

  public function getDefault() {

    return $this->default;
  }

  public function getRoutes() {

    return $this->aRoutes;
  }

  public function getGlobal() {

    return $this->global;
  }

}

