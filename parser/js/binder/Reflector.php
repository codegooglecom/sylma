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

  const CACHED_NS = 'http://www.sylma.org/parser/js/binder/cached';

  protected $window;
  protected $sPath = '';

  public function __construct(parser\reflector\domed $parent) {

    $this->setDirectory(__file__);
    $this->setArguments('settings.yml');

    $this->setNamespace(self::NS, 'self');
    $this->setNamespace(self::CACHED_NS, 'cached', false);

    $this->addParser($parent->getWindow());

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
    $window->getContext('js')->add($this->getFile('mootools.js'));
    $window->getContext('js')->add("sylma.binder.classes = {
      test1 : {
        properties : {
          value : 'hello'
        },
        events : {
          clic : {
            callback : function() {
              $(this).retrieve('sylma-object').test();
            },
            target : '.test1-1234'
          }
        }
      }
    }");
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

      case 'event' : $result = $this->reflectEvent($el); break;
      case 'property' :

      break;

      default : $this->throwException(sprintf('Unknown element %s', $el->asToken()));
    }

    return $result;
  }

  public function parseAttributes(dom\node $el, dom\element $resultElement, $result) {

    $this->addParser();
    $result = $this->buildElement($el, $resultElement);

    return $result;
  }

  protected function reflectEvent(dom\element $el) {

    $this->getObject()->set($sName, $function);
  }

  protected function buildElement(dom\element $el, dom\element $resultElement) {

    $sClass = $el->readx('@self:class', $this->getNS());
    $sName = $el->readx('@self:name', $this->getNS(), false);

    if (!$sName) $sName = uniqid('sylma-');

    $aAttributes = array(
      'class' => $sClass,
    );



    $result = $resultElement->createElement('object', $resultElement, $aAttributes, $this->getNamespace('cached'));

    return $result;
  }
}

?>
