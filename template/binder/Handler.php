<?php

namespace sylma\template\binder;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\template, sylma\parser\languages\common;

class Handler extends reflector\handler\Elemented implements reflector\elemented, reflector\attributed {

  const PREFIX = 'js';
  const NS = 'http://2013.sylma.org/template/binder';

  const CLASSES_PATH = 'js/classes';
  const CLASSES_CONTEXT = 'context/classes';

  const OBJECTS_PATH = 'js/load/objects';
  const OBJECTS_CONTEXT = 'context/objects';

  const CONTEXT_JS = 'js';

  const FILE_MOOTOOLS = '../medias/mootools.js';
  const FILE_SYLMA = '../medias/sylma.js';

  protected $window;

  protected $container;

  /**
   * Stack of builded objects
   */
  protected $aObjects = array();

  /**
   * List of all classes
   */
  protected $aClasses = array();

  /**
   * Stack of script var
   */
  protected $aSources = array();

  protected $context;

  /**
   * root object for rendering
   * @var common\_var (> Cached)
   */
  protected $objects;
  protected $bInit = false;
  protected $bTemplate = false;
  protected $rootElement;

  public function init() {

    if (!$this->bInit) {

      $this->initWindow();
      $this->prepareParent();

      $this->getContainer()->setPHPWindow($this->getPHPWindow());
      $this->getContainer()->setContext($this->getContext());

      $this->bInit = true;
    }
  }

  protected function initWindow() {

    $window = $this->loadSimpleComponent('window');
    $this->setWindow($window);

    $root = $window->createObject();
    $this->setContainer($root);
  }

  protected function setWindow(common\_window $window) {

    $this->window = $window;
  }

  public function getWindow() {

    return $this->window;
  }

  public function getPHPWindow() {

    return parent::getWindow();
  }

  protected function prepareParent() {

    $window = $this->getPHPWindow();
    $contexts = $window->getVariable('contexts');

    $isset = $window->callFunction('isset', $window->tokenToInstance('php-boolean'), array($contexts));
    $content = $window->createCall($window->getSylma(), 'throwException', 'php-boolean', array('No context sent'));
    $window->add($window->createCondition($window->createNot($isset), $content));

    $js = $contexts->call('get', array(self::CONTEXT_JS), '\sylma\core\window\context', true);

    $this->setDirectory(__FILE__);

    $fs = $window->addControler('fs');
    $window->add($js->call('add', array($fs->call('getFile', array((string) $this->getFile(self::FILE_MOOTOOLS))))));
    $window->add($js->call('add', array($fs->call('getFile', array((string) $this->getFile(self::FILE_SYLMA))))));

    $this->setContext($this->checkContext($contexts, self::CLASSES_PATH, self::CLASSES_CONTEXT, $window));
    $this->setObjects($this->checkContext($contexts, self::OBJECTS_PATH, self::OBJECTS_CONTEXT, $window));
  }

  protected function checkContext(common\_var $contexts, $sPath, $sAlias, $window) {

    $result = $contexts->call('get', array($sPath, false), '\sylma\core\window\context')->getVar();
    $if = $window->createCondition($window->createNot($result));

    $new = $this->createObject($sAlias, array(), $window, false);
    $call = $contexts->call('set', array($sPath, $new, true));
    $if->addContent($window->createAssign($result, $call));

    $window->add($if);

    return $result;
  }

  protected function setObjects(common\_var $arg) {

    $this->objects = $arg;
  }

  public function getObjects() {

    return $this->objects;
  }

  public function getParent($bDebug = true) {

    return parent::getParent($bDebug);
  }
  /**
   * @return common\_var
   */
  protected function getContext() {

    return $this->context;
  }

  protected function setContext(common\_var $context) {

    $this->context = $context;
  }

  protected function elementIsObject(dom\element $el) {

    return $el->readx('@js:class', $this->getNS(), false);
  }

  protected function elementIsNode(dom\element $el) {

    return $el->readx('@js:node', $this->getNS(), false);
  }

  protected function elementIsTemplate(dom\element $el) {

    return $el->readx('@js:alias', $this->getNS(), false);
  }

  public function parseAttributes(dom\element $el, $resultElement, $result) {

    $result = null;

    //$el = $this->setNode($el);

    if (is_null($this->rootElement)) {

      $this->rootElement = $el;
    }

    $el->getHandler()->registerNamespaces($this->getNS());

    if ($this->elementIsTemplate($el)) {

      $result = $this->reflectTemplate($el, $resultElement);
    }
    else if ($this->elementIsObject($el)) {

      $result = $this->reflectObject($el, $resultElement);
    }
    else if ($this->elementIsNode($el)) {

      $result = $this->reflectNode($el, $resultElement);
    }

    return $result;
  }

  public function onClose(dom\element $el, $newElement) {

    if ($newElement instanceof Basic && $this->rootElement === $el) {

      $this->buildClasses();
      $this->getPHPWindow()->add($this->getContainer());
    }
  }

  protected function addClass(_Class $class) {

    $this->aClasses[] = $class;

  }

  protected function buildClasses() {

    foreach ($this->aClasses as $class) {

      $class->addTo($this->getContainer());
    }
  }

  public function isRoot() {

    return !$this->aObjects;
  }

  protected function setContainer(common\_object $container) {

    $this->container = $container;
  }

  /**
   * @return common\_object
   */
  public function getContainer() {

    return $this->container;
  }

  public function startObject(Basic $object) {

    $this->aObjects[] = $object;
  }

  public function getObject($bDebug = true) {

    if (!$this->aObjects && $bDebug) {

      $this->launchException('No object in stack');
    }

    return end($this->aObjects);
  }

  public function stopObject($bBuild = true) {

    $result = array_pop($this->aObjects);
/*
    if ($bBuild && !$this->aObjects) {

      $this->onFinish();
    }
*/
    return $result;
  }

  public function onFinish() {

    $this->buildClasses();
    parent::onFinish();
  }

  /**
   * @return _Class
   */
  protected function reflectObject(dom\element $el, template\element $resultElement) {

    $result = $this->loadComponent('class', $el);

    $result->isRoot(!count($this->aObjects));
    $result->setElement($resultElement);

    $this->addClass($result);

    return $result;
  }

  protected function reflectNode(dom\element $el, template\element $resultElement) {

    $obj = $this->loadComponent('component/node', $el);
    $obj->build($resultElement);

    return $obj;
  }

  protected function reflectTemplate(dom\element $el, template\element $resultElement) {

    $result = $this->reflectObject($el, $resultElement);
    $result->useTemplate(true);

    return $result;
  }

  /**
   * @usedby _Class children
   */
  public function startSource(common\_var $source) {

    $this->aSources[] = $source;
  }

  /**
   * @see startSource()
   */
  public function stopSource() {

    array_pop($this->aSources);
  }

  /**
   * @usedby foreign parser JS
   */
  public function getSource() {

    return end($this->aSources);
  }
}

