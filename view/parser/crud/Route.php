<?php

namespace sylma\view\parser\crud;
use sylma\core, sylma\dom, sylma\parser\reflector;

class Route extends Pathed implements dom\domable {

  protected $local;
  protected $main;
  protected $sub;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);

    $this->main = $this->parseView($this->getx('view:view[not(@name)]'));
    $this->sub = $this->parseView($this->getx('view:view[@name]'));
    if ($this->sub) $this->sub->isMain(false);

    if ($local = $this->getx('self:local')) {

      $this->local = $this->parseComponent($local);
    }

    $this->loadName();
  }

  protected function parseView(dom\element $el = null) {

    if ($el) {

      $result = $this->importView($this->parseComponent($el));
    }
    else {

      $result = null;
    }

    return $result;
  }

  public function setParser(reflector\domed $parent) {

    parent::setParser($parent);

    if ($this->main) $this->main->setParser($parent);
    if ($this->sub) $this->sub->setParser($parent);
    if ($this->local) $this->local->setParser($parent);
  }

  public function getMain($bDebug = true) {

    if ($bDebug && !$this->main) {

      $this->launchException('No main route defined');
    }

    return $this->main;
  }

  protected function importView(View $view) {

    $view->setRoute($this);
    return $this->importComponent($view);
  }

  public function getSub($bDebug = true) {

    if ($bDebug && !$this->sub) {

      $this->launchException('No sub route defined');
    }

    return $this->sub;
  }

  protected function getLocal() {

    return $this->local;
  }

  public function merge($path) {

    if (!$path instanceof self) {

      $this->launchException('Cannot merge view into route');
    }

    $this->getNode()->shift($path->queryx('@*'));

    $this->main = $this->mergeView($this->getMain(false), $path->getMain(false));
    $this->sub = $this->mergeView($this->getSub(false), $path->getSub(false));

    $this->getLocal() ? $this->getLocal()->merge($path->getLocal()) : $this->local = $path->getLocal() ? $this->importComponent($path->getLocal()) : null;
  }

  protected function mergeView($source = null, $target = null) {

    if ($source) {

      $result = $source;
      if ($target) $source->merge($target);
    }
    else if ($target) {

      $result = $this->importView($target);
    }

    return $result;
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
    if ($this->getLocal()) $aResult[] = $this->getLocal()->asDOM();

    return $aResult ? $aResult : null;
  }
}

