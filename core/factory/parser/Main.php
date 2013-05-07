<?php

namespace sylma\core\factory\parser;
use sylma\core, sylma\dom, sylma\parser\reflector;

class Main extends core\argument\parser\compiler\Elemented implements reflector\elemented {

  const NS = 'http://2013.sylma.org/core/factory';

  protected $aBases = array();
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

    $this->startClasses($el);
    $aResult = array('classes' => $this->parseClasses($el->getChildren()));
    $this->stopClasses($el);

    //$this->dsp($aResult);

    return $aResult;
  }

  protected function parseClasses(dom\collection $children) {

    $aResult = array();

    while ($child = $children->current()) {

      if ($child->getType() == $child::ELEMENT) {

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
      }

      $children->next();
    }

    return $aResult;
  }

  protected function parseChildrenElementSelf(dom\element $el, array &$aResult) {

    $mResult = $this->parseElementSelf($el);
    if (!is_null($mResult)) $aResult[] = $mResult;
  }

  protected function parseChildrenElementForeign(dom\element $el, array &$aResult) {

    $aResult[] = $this->parseElementForeign($el);
  }

  protected function startClasses($test) {

    $this->aBases[] = '';
  }

  protected function stopClasses($test) {

    array_pop($this->aBases);
  }

  protected function reflectBase(dom\element $el) {

    array_pop($this->aBases);
    $this->aBases[] = $el->read();
  }

  protected function getClassBase() {

    $sResult = '';

    foreach ($this->aBases as $sBase) {

      if ($sBase) $sResult = $sBase;
    }

    return $sResult;
  }

  protected function reflectClass(dom\element $el) {

    $content = null;
    $sClass = $this->getClassName($el);

    if ($el->hasChildren()) $content = $this->parseChildren($el->getChildren());

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
    else $sResult = $this->getClassBase() . '\\' . $sName;

    return $sResult;
  }
}


