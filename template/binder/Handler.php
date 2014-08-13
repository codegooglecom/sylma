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

  const TEMPLATE_FILE = '/#sylma/ui/Template.js';

  protected $window;
  protected $bInitTemplate = false;

  protected $aFiles = array(
    '/#sylma/ui/Main.js',
    '/#sylma/ui/Base.js',
    '/#sylma/ui/Container.js',
  );

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

  /**
   * JS context
   * common\_var
   */
  protected $context;

  /**
   * Classes context
   * common\_var
   */
  protected $classes;

  /**
   * root object for rendering
   * @var common\_var (> Cached)
   */
  protected $objects;
  protected $bInit = false;
  protected $bTemplate = false;
  protected $bRoot;

  protected function init() {

    $aResult = array();

    if (!$this->bInit) {

      $this->bInit = true;

      $this->initWindow();
      $this->getPHPWindow()->add($this->prepareParent());

      $this->getContainer()->setPHPWindow($this->getPHPWindow());
      $this->getContainer()->setContext($this->getClasses());
    }

    return $aResult;
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

    $aResult = array();

    $window = $this->getPHPWindow();
    $contexts = $window->getVariable('contexts');

    $isset = $window->callFunction('isset', $window->tokenToInstance('php-boolean'), array($contexts));
    $content = $window->createCall($window->getSylma(), 'throwException', 'php-boolean', array('No context sent'));
    $aResult[] = $window->createCondition($window->createNot($isset), $content);

    $this->setDirectory(__FILE__);

    $context = $contexts->call('get', array(self::CONTEXT_JS), '\sylma\core\window\context')->getVar(false);
    $this->setContext($context);

    $aResult[] = $context->getInsert();
    $aResult[] = $this->addScript($context, $this->read('mootools'));

    foreach ($this->aFiles as $sFile) {

      $aResult[] = $this->addScript($context, $sFile);
    }

    list($content, $classes) = $this->checkContext($contexts, self::CLASSES_PATH, self::CLASSES_CONTEXT, $window);
    $this->setClasses($classes);
    $aResult[] = $content;

    list($content, $objects) = $this->checkContext($contexts, self::OBJECTS_PATH, self::OBJECTS_CONTEXT, $window);
    $this->setObjects($objects);
    $aResult[] = $content;

    return $aResult;
  }

  public function addScript(common\_var $js, $sPath) {

    $window = $this->getPHPWindow();
    $fs = $window->addControler('fs');

    return $js->call('add', array($fs->call('getFile', array($sPath))))->getInsert();
  }

  protected function checkContext(common\_var $contexts, $sPath, $sAlias, $window) {

    $aResult = array();

    $context = $contexts->call('get', array($sPath, false), '\sylma\core\window\context')->getVar(false);
    $aResult[] = $context->getInsert();

    $if = $window->createCondition($window->createNot($context));

    $new = $this->createObject($sAlias, array(), $window, false);
    $call = $contexts->call('set', array($sPath, $new, true));
    $if->addContent($context->getInsert($call));

    $aResult[] = $if;

    return array($aResult, $context);
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

  /**
   * @return common\_var
   */
  protected function getClasses() {

    return $this->classes;
  }

  protected function setClasses(common\_var $val) {

    $this->classes = $val;
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

  public function parseAttributes(dom\element $el, $resultElement, $current) {

    $aResult = null;
    $bRoot = false;

    //$el = $this->setNode($el);
    $aResult[] = $this->init();

    $el->getHandler()->registerNamespaces($this->getNS());

    if ($this->elementIsTemplate($el)) {

      $aResult[] = $this->reflectTemplate($el, $resultElement);
    }
    else if ($this->elementIsObject($el)) {

      if (!$this->bRoot) {

        $this->bRoot = true;
        $aResult[] = $this->getPHPWindow()->createInstruction($this->getContainer());
      }

      $aResult[] = $this->reflectObject($el, $resultElement);

    }
    else if ($this->elementIsNode($el)) {

      $aResult[] = $this->reflectNode($el, $resultElement);
    }

    return $aResult;
  }

  protected function addClass(_Class $class) {

    $this->aClasses[] = $class;

  }

  /**
   * @uses _class\Builder::addTo()
   */
  protected function buildClasses() {

    foreach ($this->aClasses as $class) {

      $class->addTo($this->getContainer());
    }
  }

  public function isRoot() {

    return !$this->aObjects;
  }

  public function parseFromChild(dom\element $el) {

    return array(
      $this->init(),
      parent::parseFromChild($el),
    );
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

  public function startObject($object) {

    if (!$object instanceof _class && !$object instanceof _Object) {

      $this->launchException('Bad object', get_defined_vars());
    }

    $this->aObjects[] = $object;
  }

  public function getObject($bDebug = true) {

    if (!$this->aObjects && $bDebug) {

      $this->launchException('No object in stack');
    }

    $result = end($this->aObjects);

    return $result === false ? null : $result;
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

    $aResult = array();

    if (!$this->bInitTemplate) {

      $aResult[] = $this->addScript($this->getContext(), self::TEMPLATE_FILE);
      $this->bInitTemplate = true;
    }

    $obj = $this->reflectObject($el, $resultElement);
    $obj->useTemplate(true);

    $aResult[] = $obj;

    return $aResult;
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

