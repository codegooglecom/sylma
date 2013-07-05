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

  public function getLogger($bDebug = true) {

    return parent::getLogger($bDebug);
  }

  protected function buildView(dom\handler $doc, fs\file $target) {

    $this->loadLogger();
    $sMode = $this->loadDocument($doc);

    $window = $this->prepareWindow($doc, $sMode);
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

    $result = $this->createFile($target, $return);

    $this->loadLog($doc);
    $this->clearLogger();

    return $result;
  }

  /**
   *
   * @param type $sMode
   * @return common\_window
   */
  protected function prepareWindow(dom\handler $doc, $sMode) {

    $window = $this->createWindow();

    switch ($sMode) {

      case 'insert' :
      case 'update' :

        $this->setArguments(self::DO_ARGUMENTS);

        break;

      case 'hollow' :
      case 'view' :

        $this->setArguments(self::VIEW_ARGUMENTS);

        break;

      default :

        $this->launchException(sprintf('Unexpected mode : ""%s', $sMode), get_defined_vars());
    }

    $this->prepareFormed($window);
    $external = $window->createVariable('bSylmaExternal', 'php-boolean');

    if ($doc->readx('@internal', array(), false)) {

      $window->add($window->createCondition($external, $window->getSylma()->call('throwException', array('Public request for internal view'))));
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

  public function loadDocument(dom\handler $doc) {

    if ($sMode = $this->loadMode($doc)) {

      $this->setMode($sMode);
    }
    else {

      $this->setMode(self::MODE_DEFAULT);
    }

    if ($sPath = $doc->readx('@extends', array(), false)) {

      $file = $this->getSourceFile($sPath);
      $new = $this->importDocument($file->getDocument(), $file);
      $doc->add($new->getRoot()->getChildren());
    }

    return $this->getMode();
  }

  protected function loadMode(dom\handler $doc) {

    return $doc->readx('@mode', array(), false);
  }

  public function callScript(fs\file $file, common\_window $window, $return = null, $bReturn = true) {

    $arguments = $window->getVariable('aSylmaArguments');

    //$closure = $window->createClosure(array($arguments));
    //$closure->addContent($window->callFunction('include', $return, array($file->getName())));

    $call = $window->createCall($window->getSylma(), 'includeFile', $return, array($file->getRealPath(), $arguments, $window->getVariable('bSylmaExternal')));

    if ($bReturn) {

      $result = $window->createAssign($window->getVariable('result'), $call);
    }
    else {

      $result = $call;
    }

    return $result;
  }

  public function asPath() {

    return $this->getSourceFile()->asPath();
  }
}

