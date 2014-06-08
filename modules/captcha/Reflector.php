<?php

namespace sylma\modules\captcha;
use sylma\core, sylma\dom, sylma\storage\xml, sylma\storage\sql\schema;

class Reflector extends xml\tree\Argument {

  const NS = 'http://2013.sylma.org/modules/captcha';
  const NAME = 'root';

  public function parseRoot(dom\element $el = null) {

    $this->setDirectory(__FILE__);

    $this->setNamespace(self::NS);
    $this->setName(self::NAME);

    if (!$el) {

      $this->loadElement();
    }
  }

  protected function loadElement() {

    $tree = $this->getParser()->getCurrentTemplate()->getTree();
    $schema = $tree->getParser();

    $file = $this->getFile('schema.xql');

    $doc = $this->getRoot()->importDocument($file->getDocument(), $file);

    $element = $schema->parseComponent($doc->getRoot()->getFirst());
    $element->loadNamespace($tree->getNamespace());
    $element->setParent($tree);

    $particle = current($tree->getType()->getParticles());
    $particle->addElement($element);

    $this->setElement($element);
  }

  protected function setElement(schema\field $element) {

    $this->element = $element;
  }

  protected function getElement() {

    return $this->element;
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'element' : $result = $this->getElement()->reflectApply($sMode);
    }

    return $result;
  }

  public function reflectApplyDefault($sPath, array $aPath, $sMode, $bRead = false, array $aArguments = array()) {

    return parent::reflectApplyDefault($sPath, $aPath, $sMode, $bRead);
  }

  public function reflectRegister($content = null, $sReflector = '') {

    $this->getElement()->reflectRegister($content, $sReflector);
  }
}

