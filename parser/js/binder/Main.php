<?php

namespace sylma\parser\js\binder;
use sylma\core, sylma\parser\reflector, sylma\dom, sylma\parser\languages\common, sylma\parser\languages\js;

class Main extends reflector\handler\Elemented implements reflector\elemented, reflector\attributed {

  protected static $sFactoryClass = '\sylma\core\factory\Reflector';
  protected static $sArgumentClass = 'sylma\core\argument\Filed';

  const NS = 'http://www.sylma.org/parser/js/binder';

  const PARENT_METHOD = 'getParentParser';
  const PARENT_RETURN = '\sylma\parser\action\cached';
  const PARSER_METHOD = 'loadParser';

  const CACHED_NS = Cached::NS;

  const JS_TEMPLATES_PATH = 'sylma.binder.classes';
  const JS_OBJECTS_PATH = 'sylma.ui.tmp';

  const CONTEXT_ALIAS = Cached::CONTEXT_ALIAS;

  protected $window;
  protected $sPath = '';
  protected $aObjects = array();
  protected $rootObject;
  //protected $bRootElement = true;
  protected $context;

  protected $iDepth = 0;

  public function __construct(reflector\documented $root, reflector\elemented $parent = null, core\argument $arg = null) {

    $this->setRoot($root);

    if ($parent) $this->setParent($parent);

    $this->setDirectory(__file__);
    $this->setArguments('settings.yml');

    $this->setNamespace(self::NS, 'self');
    $this->setNamespace(self::CACHED_NS, 'cached', false);
    $this->setNamespace(\Sylma::read('namespaces/html'), 'html', false);

    $this->addToParent($root->getWindow());

    $this->initWindow();
    $this->prepareParent();
  }

  public function init() {

  }

  /**
   * Add this parser to parent PHP window
   * @param common\_window $window Window to add the parsers to
   */
  protected function addToParent(common\_window $window) {

    $parent = $window->createCall($window->getSelf(), self::PARENT_METHOD, self::PARENT_RETURN, array(true));
    $call = $window->createCall($parent, self::PARSER_METHOD, 'php-boolean', array($this->getNamespace('cached')));

    $window->add($call);
  }

  /**
   * @return common\_var
   */
  public function getContext() {

    return $this->context;
  }

  public function setContext(common\_var $context) {

    $this->context = $context;
  }

  protected function prepareParent() {

    $window = $this->getRoot()->getWindow();
    $manager = $window->addControler('parser');

    $call = $window->createCall($manager, 'getContext', '\\sylma\\parser\\context', array(self::CONTEXT_ALIAS));
    $this->setContext($call->getVar());
  }

  protected function initWindow() {

    $window = $this->create('window', array($this, $this->getArgument('classes/js')));
    $this->setWindow($window);

    $root = $window->createObject();
    //$window->assignProperty(self::JS_TEMPLATES_PATH, $root);
    //$this->startObject($root);
    $this->setRootObject($root);

    return $root;

    //echo $this->show($window->getContexts(), false);
/*

    $window->insert("sylma.binder.classes = {
      test1 : {
        properties : {
          value : 'hello'
        },
        events : {
          click : {
            callback : function() {
              $(this).retrieve('sylma-object').test();
            }
            //target : '.test1-1234'
          }
        }
      }
    }");

    $window->stopContext();
 */
  }

  public function getObject() {

    return end($this->aObjects);
  }

  public function setRootObject(common\_object $root) {

    $this->rootObject = $root;
  }

  /**
   *
   * @return common\_object
   */
  public function getRootObject() {

    return $this->rootObject;
  }

  protected function startObject(common\_object $object) {

    $this->aObjects[] = $object;
  }

  protected function stopObject() {

    return array_pop($this->aObjects);
  }

  protected function setWindow(common\_window $window) {

    $this->window = $window;
  }

  /**
   *
   * @return js\window
   */
  public function getWindow() {

    return $this->window;
  }

  protected function parseElementSelf(dom\element $el) {

    $result = null;

    switch ($el->getName()) {

      case 'event' : $result = $this->reflectEvent($el); break;
      case 'property' : break;
      case 'static' : $result = $this->reflectStatic($el); break;

      default : $this->throwException(sprintf('Unknown element %s', $el->asToken()));
    }

    return $result;
  }

  protected function parseElementUnknown(dom\element $el) {

    return $el;
  }

  protected function isRoot() {

    return !$this->aObjects;
  }

  protected function elementIsObject(dom\element $el) {

    return $el->readx('@self:class', $this->getNS(), false);
  }

  protected function elementIsNode(dom\element $el) {

    return $el->readx('@self:node', $this->getNS(), false) && $el->getNamespace() === $this->getNamespace('html');
  }

  public function parseAttributes(dom\element $el, $resultElement, $result) {

    $result = null;

    if ($this->elementIsObject($el)) {

      $result = $this->reflectObject($el, $resultElement);
    }
    else if ($this->elementIsNode($el)) {

      $result = $this->reflectNode($el, $resultElement);
    }

    return $result;
  }

  public function onClose(dom\element $el, $newElement) {

    if ($this->elementIsObject($el)) {

      $this->stopObject();

      if ($this->isRoot()) {

        $this->addToWindow($this->getRootObject());
      }
    }
  }

  protected function addToWindow(common\_object $obj) {

    $contents = $this->getWindow()->objAsDOM($obj);

    if ($this->readArgument('debug/show')) {

      $this->loadDefaultArguments();
      $tmp = $this->createDocument($contents);

      //echo '<pre>' . $file->asToken() . '</pre>';
      echo '<pre>' . str_replace(array('<', '>'), array('&lt;', '&gt'), $tmp->asString(true)) . '</pre>';
      //exit;
    }

    $window = $this->getRoot()->getWindow(); // PHP window

    foreach($contents->getChildren() as $child) {

      if ($child->getType() == $child::TEXT) $aResult[] = $child->getValue();
      else $aResult[] = $child;
    }

    $window->add($this->getContext()->call('add', array($window->createString($aResult)), '\sylma\parser\context', false));
  }

  protected function reflectEvent(dom\element $el) {

    $window = $this->getWindow();

    $function = $window->createFunction(array('e'), $this->parseEventContent($el->read()));
    $sName = $el->readAttribute('name');
    $sID = uniqid('sylma');

    $event = $this->getObject()->setProperty("events.$sID", $window->createObject());

    $event->setProperty('name', $sName);
    $event->setProperty('callback', $function);

    if (!$this->elementIsObject($el->getParent())) {

      $sClass = uniqid('sylma');

      $this->getParent()->getLastElement()->addToken('class', $sClass);
      $event->setProperty('target', $sClass);
    }
  }

  protected function parseEventContent($sContent) {

    $aReplaces = array(
      '/%([\w-_]+)%/' => '\$(this).retrieve(\'sylma-$1\')',
      '/%([\w-_]+)\s*,\s*([^%]+)%/' => '\$(this).store(\'sylma-$1\', $2)');

    $sResult = preg_replace(array_keys($aReplaces), $aReplaces, $sContent);

    return $sResult;
  }

  protected function reflectObject(dom\element $el, $resultElement) {

    $bName = (bool) $el->readx('@self:name', $this->getNS(), false);

    $result = $this->buildElement($el, $resultElement);
    $obj = $this->getWindow()->createObject();

    if ($bName) $obj->setProperty('name', true);

    $this->getRootObject()->setProperty($result->readAttribute('binder'), $obj);
    $this->startObject($obj);

    return $result;
  }

  protected function buildElement(dom\element $el, $resultElement) {

    $sClass = $el->readx('@self:class', $this->getNS());
    $sName = $el->readx('@self:name', $this->getNS(), false);
    $sParent = $el->readx('@self:parent', $this->getNS(), false);

    if ($this->isRoot() && !$sParent) {

      $sParent = self::JS_OBJECTS_PATH;
    }

    if (!$this->isRoot() && $sParent) {

      $this->throwException(sprintf('@attribute parent must only appears on root element %s', $el->asToken()));
    }

    $aAttributes = array(
      'class' => $sClass,
      'name' => $sName,
      'id' => $el->readAttribute('id', null, false),//$resultElement->readAttribute('id', null, false),
      'binder' => uniqid('sylma'),
      'parent' => $sParent,
    );

    //$resultElement->setAttributes($aAttributes);
    $result = $resultElement->createElement('object', $resultElement, $aAttributes, $this->getNamespace('cached'));

    return $result;
  }

  protected function reflectStatic(dom\element $el) {

    $content = $this->getParent()->parseFromChild($el->getFirst());
    $this->getObject()->setProperty($el->readAttribute('name'), $content);
  }

  protected function reflectNode(dom\element $el, dom\element $resultElement) {

    $sName = $el->readx('@self:node', $this->getNS());
    $sClass = uniqid('sylma-');

    $this->getObject()->setProperty("nodes.$sName", $sClass);
    $resultElement->addToken('class', $sClass);

    return $resultElement;
  }
}