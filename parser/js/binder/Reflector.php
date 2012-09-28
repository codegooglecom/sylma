<?php

namespace sylma\parser\js\binder;
use sylma\core, sylma\parser, sylma\dom, sylma\parser\languages\common, sylma\storage\fs;

\Sylma::load('/parser/reflector/basic/Domed.php');

\Sylma::load('/parser/reflector/elemented.php');
\Sylma::load('/parser/reflector/attributed.php');

/**
 * Description of Reflector
 *
 * @author Rodolphe Gerber
 */
class Reflector extends parser\reflector\basic\Domed implements parser\reflector\elemented, parser\reflector\attributed {

  const NS = 'http://www.sylma.org/parser/js/binder';

  const PARENT_METHOD = 'getParentParser';
  const PARENT_RETURN = '\sylma\parser\action\cached';
  const PARSER_METHOD = 'loadParser';

  const CACHED_NS = Cached::NS;

  protected $window;
  protected $sPath = '';
  protected $object;

  public function __construct(parser\reflector\domed $parent) {

    $this->setDirectory(__file__);
    $this->setArguments('settings.yml');

    $this->setNamespace(self::NS, 'self');
    $this->setNamespace(self::CACHED_NS, 'cached', false);

    $this->addParser($parent->getWindow());

    $this->setParent($parent);
    $this->initWindow();
  }

  public function getPath() {

    if (!$this->sPath) {

      $this->throwException('No path defined');
    }

    return $this->sPath;
  }

  public function setPath($path) {

    $this->sPath = $path;
  }

  protected function addParser(common\_window $window) {

    $parent = $window->createCall($window->getSelf(), self::PARENT_METHOD, self::PARENT_RETURN, array(true));
    $call = $window->createCall($parent, self::PARSER_METHOD, 'php-boolean', array($this->getNamespace('cached')));

    $window->add($call);
  }

  public function parseRoot(dom\element $el) {

    $result = $this->parseElementSelf($el);

    return $result;
  }

  protected function initWindow() {
/*
    $window = $this->create('window', array($this, $this->getArgument('js')));
    $this->setWindow($window);

    $collection = $window->createObject();
    $property = $window->createProperty(self::DEFAULT_PROPERTY)
 */

    $window = $this->getParent()->getWindow();
    //echo $this->show($window->getContexts(), false);

    $window->startContext('js');

    $window->insert($window->createCall($window->getSelf(), 'getFile', '\sylma\storage\fs\file', array((string) $this->getFile('../mootools.js'))));
    $window->insert($window->createCall($window->getSelf(), 'getFile', '\sylma\storage\fs\file', array((string) $this->getFile('../sylma.js'))));
    //, $this->getFile('../sylma.js'));

    $window->insert("sylma.binder.classes = {
      test1 : {
        properties : {
          value : 'hello'
        },
        events : {
          clic : {
            callback : function() {
              $(this).retrieve('sylma-object').test();
            }
            //target : '.test1-1234'
          }
        }
      }
    }");

    $window->stopContext();
  }

  protected function getWindow() {

    return $this->window;
  }

  protected function setWindow(common\_window $window) {

    $this->window = $window;
  }

  protected function parseElementSelf(dom\element $el) {

    $result = null;

    switch ($el->getName()) {

      //case 'event' : $result = $this->reflectEvent($el); break;
      case 'event' :
      case 'property' :

      break;

      default : $this->throwException(sprintf('Unknown element %s', $el->asToken()));
    }

    return $result;
  }

  public function parseAttributes(dom\node $el, dom\element $resultElement, $result) {

    $result = $this->buildElement($el, $resultElement);

    return $result;
  }

  protected function reflectEvent(dom\element $el) {

    $this->getObject()->set($sName, $function);
  }

  protected function buildElement(dom\element $el, dom\element $resultElement) {

    $sClass = $el->readx('@self:class', $this->getNS());
    $sName = $el->readx('@self:name', $this->getNS(), false);

    $aAttributes = array(
      'class' => $sClass,
      'name' => $sName,
      'template' => 'test1'//uniqid('sylma'),
    );

    $result = $resultElement->createElement('object', $resultElement, $aAttributes, $this->getNamespace('cached'));

    return $result;
  }
}

?>
