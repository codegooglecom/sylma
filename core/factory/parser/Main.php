<?php

namespace sylma\core\factory\parser;
use sylma\core, sylma\dom, sylma\parser\reflector;

class Main extends reflector\handler\Elemented implements reflector\elemented {

  const NS = 'http://2013.sylma.org/core/factory';

  protected $sClassBase = '';
  protected $allowForeign = true;

  public function parseRoot(dom\element $el) {

    if ($el->getName() !== 'classes') {

      $this->throwException(sprintf('Bad root %s', $el->asToken()));
    }

    $aResult = $this->reflectClasses($el);
    $doc = $this->createArgument($aResult, $this->getParent()->getNamespace())->asDOM();
    //$this->dsp($doc);
    $result = $this->getParent()->parseFromChild($doc->getRoot());
    //$this->dsp($result);
    return $result;
  }

  public function parseFromParent(dom\element $el) {

    return $this->parseRoot($el);
  }

  public function parseFromChild(dom\element $el) {

    $this->throwException('Cannot parse from child');
  }

  protected function parseElementSelf(dom\element $el) {

    $result = null;

    switch ($el->getName()) {

      case 'classes' : $result = $this->reflectClasses($el); break;
      //case 'class' : $result = $this->reflectClass($el); break;
      case 'base' : $this->reflectBase($el); break;

      default : $this->throwException(sprintf('Unknown element %s', $el->asToken()));
    }

    return $result;
  }

  protected function reflectClasses(dom\element $el) {

    //$result = $this->getWindow()->create('array', array($this->getWindow()));
    //$result->setContent(array('classes' => $this->parseChildren($el->getChildren())));

    $aResult = array('classes' => $this->parseClasses($el->getChildren()));
    //$this->dsp($aResult);

    return $aResult;
  }

  protected function parseClasses(dom\collection $children) {

    $aResult = array();

    while ($child = $children->current()) {

      if ($this->useNamespace($child->getNamespace())) {

        if ($child->getName() == 'class') {

          $aResult[] = $this->reflectClass($child);
        }
        else {

          $this->parseChildrenElementSelf($child, $aResult);
        }

      }
      else {

        $this->parseChildrenElementForeign($child, $aResult);
      }

      $children->next();
    }

    return $aResult;
  }

  protected function parseChildrenElementSelf(dom\element $el, array &$aResult) {

    $mResult = $this->parseElementSelf($el);
    if (!is_null($mResult)) $aResult[] = $mResult;
  }

  protected function reflectBase(dom\element $el) {

    $this->sClassBase = $el->read();
  }

  protected function getClassBase() {

    return $this->sClassBase;
  }

  protected function reflectClass(dom\element $el) {

    $content = null;
    if ($el->hasChildren()) $content = $this->parseChildren($el->getChildren());

    $sClass = $this->getClassName($el);

    return array($el->readAttribute('alias') => array(
      'file' => $this->getFileName($el, $sClass),
      'name' => $sClass,
      $content));
  }

  protected function getFileName(dom\element $el, $sClass) {

    $sFile = $el->readAttribute('file', null, false);

    if ($sFile) {

      $sResult = $sFile;
    }
    else {

      $sResult = str_replace('/', '\\', $sClass) . '.php';
    }

    return $sResult;
  }

  protected function getClassName(dom\element $el) {

    $sName = $el->readAttribute('name');

    if ($sName{0} == '\\') $sResult = $sName;
    else $sResult = $this->sClassBase . '\\' . $sName;

    return $sResult;
  }
}


