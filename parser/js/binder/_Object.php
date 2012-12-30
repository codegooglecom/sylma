<?php

namespace sylma\parser\js\binder;
use sylma\core, sylma\parser, sylma\dom;

class _Object extends core\module\Controled implements core\argumentable {

  protected $aProperties = array();
  protected $aCollections = array();
  protected $aObjects = array();

  protected $sName;
  protected $sClass;
  protected $sID;
  protected $sBinder;

  public function __construct(parser\cached\documented $reflector, dom\element $el) {

    $this->setControler($reflector);

    $this->setID($el->readAttribute('id'));
    $this->setName($el->readAttribute('name', null, false));
    $this->setClass($el->readAttribute('class'));
    $this->setBinder($el->readAttribute('binder'));

    $this->parseChildren($el->getChildren());
  }

  protected function getID() {

    return $this->sID;
  }

  protected function setID($sID) {

    //if (!$sID) $sID = uniqid('sylma-');

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

  public function getBinder() {

    return $this->sBinder;
  }

  public function setBinder($sBinder) {

    $this->sBinder = $sBinder;
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

    $obj = new self($this->getControler(), $el);
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

    $result = $this->getControler()->createArgument(array(
      'id' => $this->getID(),
      'extend' => $this->getClass(),
      'binder' => $this->getBinder(),
      'properties' => $this->getProperties(),
      'objects' => $this->getObjects(),
      'collections' => $this->getCollections(),
    ));

    return $result;
  }
}

?>
