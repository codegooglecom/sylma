<?php

namespace sylma\view\parser;
use sylma\core, sylma\parser\reflector, sylma\dom;

class Crud extends reflector\handler\Elemented implements reflector\elemented {

  const VIEW_NS = 'http://2013.sylma.org/view';
  const VIEW_PREFIX = 'view';

  protected $global;
  protected $default;

  protected $aPaths = array();
  protected $aGroups = array();

  public function parseRoot(dom\element $el) {

    $el = $this->setNode($el);
    $this->setNamespace(self::VIEW_NS, self::VIEW_PREFIX);

    $this->parseChildren($el->getChildren());
    $this->loadExtends();
  }

  protected function parseElementSelf(dom\element $el) {

    switch ($el->getName()) {

      case 'global' : $this->global = $this->parseComponent($el); break;
      case 'view' :
      case 'route' : $this->parsePath($el); break;
      case 'group' : $this->parseGroup($el); break;
      default :

        $this->launchException('Unknown route', get_defined_vars());
    }
  }

  protected function loadExtends() {

    if ($sPath = $this->readx('@extends')) {

      $file = $this->getSourceFile($sPath);

      $reflector = clone $this;
      $reflector->parseRoot($file->getDocument()->getRoot());

      foreach ($reflector->getPaths() as $path) {

        if ($parent = $this->getPath($path->getName())) {

          $parent->merge($path);
        }
        else {

          $this->addPath($path);
        }
      }
    }
  }

  protected function parsePath(dom\element $el) {

    $result = $this->parseComponent($el);
    $this->addPath($result);

    return $result;
  }

  protected function addPath(crud\Path $path) {

    if (!$path->getName()) {

      $this->setDefault($path);
    }

    $this->aPaths[$path->getName()] = $path;
  }

  protected function parseGroup(dom\element $el) {

    $group = $this->parseComponent($el);
    $this->aGroups[$group->getName()] = $group;
  }

  public function getGroup($sName) {

    return $this->aGroups[$sName];
  }

  protected function setDefault(crud\Path $route) {

    if ($this->default) {

      $this->launchException('Cannot have more than one default route', get_defined_vars());
    }

    $this->default = $route;
  }

  public function getDefault() {

    return $this->default;
  }

  public function getPaths() {

    return $this->aPaths;
  }

  protected function getPath($sName) {

    return isset($this->aPaths[$sName]) ? $this->aPaths[$sName] : null;
  }

  public function getGlobal() {

    return $this->global;
  }

  public function __clone() {

    $this->default = null;
    $this->aPaths = array();
    $this->aGroups = array();
  }
}

