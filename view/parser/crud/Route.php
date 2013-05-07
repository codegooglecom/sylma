<?php

namespace sylma\view\parser\crud;
use sylma\core, sylma\dom;

class Route extends Pathed implements dom\domable {

  protected $local;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);

    $this->main = $this->loadView($this->getx('view:view[not(@name)]'));
    $this->sub = $this->loadView($this->getx('view:view[@name]'));

    if ($local = $this->getx('self:local')) {

      $this->local = $this->parseComponent($local);
    }

    $this->loadName();
  }

  protected function loadView(dom\element $el = null) {

    $result = null;

    if ($el) {

      $result = $this->loadComponent('component/' . $el->getName(), $el);
      $result->parseRoot($el, $this);
    }

    return $result;
  }

  public function getMain($bDebug = true) {

    if ($bDebug && !$this->main) {

      $this->launchException('No main route defined');
    }

    return $this->main;
  }

  public function getSub($bDebug = true) {

    if ($bDebug && !$this->sub) {

      $this->launchException('No sub route defined');
    }

    return $this->sub;
  }

  public function merge($path) {

    if (!$path instanceof self) {

      $this->launchException('Cannot merge view into route');
    }

    $this->getMain(false) ? $this->getMain()->merge($path->getMain()) : $this->main = $path->getMain();
    $this->getSub(false) ? $this->getSub()->merge($path->getSub()) : $this->sub = $path->getSub();
  }

  public function getPath(array $aPath) {

    if ($aPath) {

      $sName = array_shift($aPath);
    }
    else {

      $sName = '';
    }

    $sMain = $this->getMain()->getName();
    $sSub = $this->getSub()->getName();

    if ($sMain == $sName) {

      $result = $this->getMain()->getPath($aPath);
    }
    else if ($sSub == $sName) {

      $result = $this->getSub()->getPath($aPath);
    }
    else {

      $this->launchException('No view available with this path', get_defined_vars());
    }

    return $result;
  }

  public function asDOM() {

    $aResult = array();

    if ($aGroups = $this->loadGroups()) $aResult[] = $aGroups;
    if ($this->local) $aResult[] = $this->local->asDOM();

    return $aResult ? $aResult : null;
  }
}

