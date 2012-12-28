<?php

namespace sylma\parser\js\binder;
use sylma\core, sylma\dom, sylma\parser;

/**
 * On run, this class will work with the _Object instances to build final js tree of datas
 */

class Cached extends core\module\Domed implements parser\cached\documented {

  protected $parent;

  const NS = 'http://www.sylma.org/parser/js/binder/cached';
  const CONTEXT_ALIAS = 'js/binder/context';

  public function __construct() {

    $this->setNamespace(self::NS, 'self');
    $this->setDirectory(__FILE__);
    $this->loadDefaultArguments();
  }

  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }

  public function getParent() {

    return $this->parent;
  }

  public function setParent(parser\action\cached $parent) {

    $this->parent = $parent;
  }

  public function parseDocument(dom\handler $doc) {

    $js = $this->getParent()->getContext('js');

    $js->shift($this->getFile('../sylma.js'));
    $js->shift($this->getFile('../mootools.js'));
    $js->add($this->getControler('parser')->getContext(self::CONTEXT_ALIAS));

    $doc = $this->getTemplate('ids.xsl')->parseDocument($doc);//$this->dsp($doc);
    $doc = $this->getTemplate('cached.xsl')->parseDocument($doc);//$this->dsp($doc);

    $parser = $this->getControler('parser');
    $aResult = array();

    $sParent = '';

    foreach ($doc->getx('self:objects', $this->getNS())->getChildren() as $el) {

      $sParent = $el->readAttribute('parent', null, false);

      $object = $parser->create('js/binder/object', array($this, $el));
      $aResult[$object->getName()] = $object;
    }

    $objects = $this->createArgument($aResult);

    $sObjects = $objects->asJSON();
    $this->getParent()->getContext('js/load')->shift("sylma.ui.load($sParent, $sObjects);");

    $result = $doc->getx('self:render', $this->getNS())->getChildren();

    return $this->createDocument($result);
  }

}
