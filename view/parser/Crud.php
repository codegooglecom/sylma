<?php

namespace sylma\view\parser;
use sylma\core, sylma\parser\reflector, sylma\dom, sylma\storage\fs;

class Crud extends reflector\handler\Elemented implements reflector\elemented {

  const CRUD_NS = 'http://2013.sylma.org/view/crud';
  const CRUD_PREFIX = 'crud';

  const VIEW_NS = 'http://2013.sylma.org/view';
  const VIEW_PREFIX = 'view';

  protected $global;
  protected $default;

  protected $aPaths = array();
  protected $aGroups = array();

  public function parseRoot(dom\element $el, fs\directory $base = null) {

    $el = $this->setNode($el, true);

    $this->setNamespace(self::CRUD_NS, self::CRUD_PREFIX);
    $this->setNamespace(self::VIEW_NS, self::VIEW_PREFIX);

    if ($base) {

      $this->setDirectory($base);
      $this->resolveImports($base);
    }

    if ($el->isComplex()) {

      $this->parseChildren($el->getChildren());
    }

    $this->loadExtends();
  }

  protected function resolveImports(fs\directory $dir) {

    foreach ($this->queryx('//self:import') as $el) {

      $el->set((string) $this->getFile($el->readx()));
    }
  }

  protected function parseElementSelf(dom\element $el) {

    switch ($el->getName()) {

      case 'global' : $this->setGlobal($this->parseComponent($el)); break;
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
      $reflector->parseRoot($this->getRoot()->importDocument($file->getDocument(), $file)->getRoot(), $file->getParent());

      foreach ($reflector->getPaths() as $path) {

        if ($parent = $this->getPath($path->getName())) {

          $parent->merge($path);
        }
        else {

          $this->addPath($path);
        }
      }

      if ($this->getGlobal()) $this->getGlobal()->merge($reflector->getGlobal());
      else if ($reflector->getGlobal()) $this->setGlobal($reflector->getGlobal());

      foreach ($reflector->getGroups() as $group) {

        if ($parent = $this->getGroup($group->getName(), false)) {

          $parent->merge($group);
        }
        else {

          $this->addGroup($group);
        }
      }
    }
  }

  protected function setGlobal(crud\Share $global) {

    $this->importComponent($global);
    $this->global = $global;
  }

  public function getGlobal() {

    return $this->global;
  }

  protected function parsePath(dom\element $el) {

    $result = null;

    if (!$el->readx('@disabled', array(), false)) {

      $result = $this->parseComponent($el);
      $this->addPath($result);
    }

    return $result;
  }

  protected function addPath(crud\Pathed $path) {

    if (!$path->getName()) {

      $this->setDefault($path);
    }

    $this->importComponent($path);
    $this->aPaths[$path->getName()] = $path;
  }

  protected function parseGroup(dom\element $el) {

    $group = $this->parseComponent($el);
    $this->aGroups[$group->getName()] = $group;
  }

  protected function addGroup(crud\Share $group) {

    $this->importComponent($group);
    $this->aGroups[$group->getName()] = $group;
  }

  public function getGroups() {

    return $this->aGroups;
  }

  public function getGroup($sName, $bDebug = true) {

    if (!isset($this->aGroups[$sName])) {

      if ($bDebug) $this->launchException('No group with this name');
      $result = null;
    }
    else {

      $result = $this->aGroups[$sName];
    }

    return $result;
  }

  protected function setDefault(crud\Pathed $route) {

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

  public function __clone() {

    $this->default = null;
    $this->global = null;
    $this->aPaths = array();
    $this->aGroups = array();

    $this->sourceFile = null;
  }
}

