<?php

namespace sylma\parser\js\binder;
use sylma\core, sylma\parser, sylma\dom;

class _Object extends core\module\Argumented implements core\argumentable {

  protected $aProperties = array();
  protected $aCollections = array();
  protected $aObjects = array();

  protected $sName;
  protected $sClass;
  protected $sID;
  protected $sTemplate;

  public function __construct(parser\cached\documented $reflector, dom\element $el) {

    $this->setControler($reflector);

    $this->setID();
    $this->setName($el->readAttribute('name', null, false));
    $this->setClass($el->readAttribute('class'));
    $this->setTemplate($el->readAttribute('template'));

    $this->parseChildren($el->getChildren());
  }

  protected function getID() {

    return $this->sID;
  }

  protected function setID($sID = '') {

    if (!$sID) $sID = uniqid('sylma-');

    $this->sID = $sID;
  }

  public function getName() {

    return $this->sName;
  }

  protected function setName($sName = '') {

    if (!$sName) $sName = $this->getID();

    $this->sName = $sName;
  }

  protected function getClass() {

    return $this->sClass;
  }

  protected function setClass($sName) {

    $this->sClass = $sName;
  }

  public function getTemplate() {

    return $this->sTemplate;
  }

  public function setTemplate($sTemplate) {

    $this->sTemplate = $sTemplate;
  }

  protected function parseChildren(dom\collection $children) {

    foreach ($children as $child) {

     switch ($child->getName()) {

        case 'object' : $this->reflectObject($child); break;
        case 'property' : $this->reflectProperty($child); break;
        case 'collection' : $this->reflectCollection($child); break;

        default : $this->throwException(sprintf('Unknown %s', $child->asToken()));
      }

    }
  }

  protected function reflectProperty(dom\element $el) {

    $this->aProperties[$el->readAttribute('name')] = $el->read();
  }

  protected function reflectObject(dom\element $el) {

    $obj = new self($el);
    $this->aObjects[$obj->getName()] = $obj;
  }

  protected function reflectCollection(dom\element $el) {

    $aCollection = array();

    foreach ($el->getChildren() as $child) {

      //$aCollection[]
    }

    $this->aCollections[$el->readAttribute('name')] = $aCollection;
  }

  protected function getProperties() {

    return $this->aProperties;
  }

  protected function getObjects() {

    return $this->aObjects;
  }

  protected function getCollections() {

    return $this->aCollections;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'id' => $this->getID(),
      'class' => $this->getClass(),
      'template' => $this->getTemplate(),
      'properties' => $this->getProperties(),
      'objects' => $this->getObjects(),
      'collections' => $this->getCollections(),
    ));
  }
}

?>
