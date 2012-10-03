<?php

namespace sylma\parser\js\binder;
use sylma\core, sylma\dom, sylma\parser;

\Sylma::load('/core/module/Domed.php');
\Sylma::load('/parser/cached/documented.php');

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

  public function setParent($parent) {

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

    $sJSON = json_encode($objects->asArray(true), JSON_FORCE_OBJECT);
    $this->getParent()->getContext('js/load')->add("sylma.ui.load($sParent, $sJSON);");

    $result = $doc->getx('self:render', $this->getNS())->getChildren();

    return $this->createDocument($result);
  }

}
