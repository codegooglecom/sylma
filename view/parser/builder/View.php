<?php

namespace sylma\view\parser\builder;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\storage\fs, sylma\parser\languages\common, sylma\view\parser;

class View extends Variabled {

  const MODE_DEFAULT = 'view';
  const ARGUMENT_METHOD = 'getFirst';

  const DO_ARGUMENTS = '../do.xml';
  const VIEW_ARGUMENTS = '../view.xml';

  protected $sMode = self::MODE_DEFAULT;
  protected $reflector;

  public function build() {

    $this->setDirectory(__FILE__);

    $doc = $this->getDocument();

    $result = $this->buildView($doc, $this->loadSelfTarget($this->getFile()));

    return $result;
  }

  protected function buildView(dom\document $doc, fs\file $target) {

    $sMode = $this->loadDocument($doc);

    $window = $this->prepareWindow($sMode);
    $content = $this->reflectMain($this->getFile(), $doc, $window);

    switch ($sMode) {

      case 'insert' :
      case 'update' :

        $return = $this->buildSimple($content, $window);

        break;

      case 'hollow' :
      case 'view' :

        $return = $this->buildInstanciation($content, $window);

        break;

      default :

        $this->launchException(sprintf('Unexpected mode : ""%s', $sMode), get_defined_vars());
    }

    return $this->createFile($target, $return);
  }

  /**
   *
   * @param type $sMode
   * @return common\_window
   */
  protected function prepareWindow($sMode) {

    $window = $this->createWindow();

    switch ($sMode) {

      case 'insert' :
      case 'update' :

        $this->setArguments(self::DO_ARGUMENTS);
        $this->prepareFormed($window);

        break;

      case 'hollow' :
      case 'view' :

        $this->setArguments(self::VIEW_ARGUMENTS);
        $this->prepareArgumented($window);

        break;

      default :

        $this->launchException(sprintf('Unexpected mode : ""%s', $sMode), get_defined_vars());
    }

    return $window;
  }

  protected function getMode() {

    return $this->sMode;
  }

  protected function setMode($sMode) {

    $this->sMode = $sMode;
  }

  protected function getReflector() {

    return $this->reflector;
  }

  protected function setReflector(parser\Elemented $reflector) {

    $this->reflector = $reflector;
  }

  protected function parseReflector(reflector\domed $reflector, dom\document $doc) {

    return $reflector->parseRoot($doc->getRoot(), $this->getMode());
  }

  protected function loadDocument(dom\handler $doc) {

    if ($sMode = $this->loadMode($doc)) {

      $this->setMode($sMode);
    }
    else {

      $this->setMode(self::MODE_DEFAULT);
    }

    return $this->getMode();
  }

  protected function loadMode(dom\handler $doc) {

    return $doc->readx('@mode', array(), false);
  }
}

