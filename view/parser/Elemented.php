<?php

namespace sylma\view\parser;
use sylma\core, sylma\dom, sylma\template, sylma\parser\reflector, sylma\schema, sylma\parser\languages\common;

class Elemented extends template\parser\handler\Domed {

  const NS = 'http://2013.sylma.org/view';
  const NS_SCHEMA = 'http://2013.sylma.org/schema/template';

  const TMP_ARGUMENTS = 'view.xml';

  protected $allowForeign = true;

  protected $schema;
  protected $resource;
  protected $aEntries = array();
  protected $sMode;

  public function __construct(reflector\documented $root, reflector\elemented $parent = null, core\argument $arg = null) {

    //$this->setDirectory(__FILE__);
    //$this->setArguments(self::TMP_ARGUMENTS);
    //$arg = $this->setArguments($this->getFactory()->findClass('classes/elemented'));

    //$this->setNamespace(self::NS, self::PREFIX);
    $this->setNamespace(self::NS_SCHEMA);
    $this->setNamespace(parent::NS, parent::PREFIX);

    parent::__construct($root, $parent, $arg);
  }

  public function parseRoot(dom\element $el, $sMode = '') {

    $this->allowUnknown(true);
    $this->allowForeign(true);

    $this->setMode($sMode);

    $tree = $this->loadTree($el, $sMode); // parseRoot(), onAdd()
    $result = $this->build($tree, $sMode); // asArray()

    return $result;
  }

  protected function setMode($sMode) {

    $this->sMode = $sMode;
  }

  protected function getMode() {

    return $this->sMode;
  }

  public function setNamespace($sNamespace, $sPrefix = null, $bDefault = true) {

    return parent::setNamespace($sNamespace, $sPrefix, $bDefault);
  }

  public function loadElementForeignKnown(dom\element $el, reflector\elemented $parser) {

    switch ($this->getMode()) {

      case 'update' :
      case 'insert' :

        $result = null;
        break;

      default :

        $result = parent::loadElementForeignKnown($el, $parser);
    }

    return $result;
  }

  /**
   * Namespace load from view except for empty prefix where schema target (default) namespace is loaded
   * @see \sylma\storage\sql\template\Handler::lookupNamespace()
   * @param type $sPrefix
   * @return type
   */
  public function lookupNamespace($sPrefix = '') {

    if (!$sPrefix) $sResult = $this->getSchema()->lookupNamespace($sPrefix);
    else $sResult = parent::lookupNamespace($sPrefix);

    return $sResult;
  }

  protected function parseElementSelf(dom\element $el) {

    switch ($el->getName()) {

      case 'template' :

        $this->loadTemplate($el);
        $result = null;
        break;

      default :

        $result = parent::parseElementSelf($el);
    }

    return $result;
  }

  protected function build(template\parser\tree $tree, $sMode) {

    switch ($sMode) {

      case 'update' :
      case 'insert' :

        $tpl = $tree->reflectApply();

        if ($tpl instanceof common\arrayable) {

          $this->addToResult($tpl->asArray());
        }
        else {

          $this->getWindow()->add($tpl);
        }
        //$tpl->asArray();
        //dsp($tpl);

        $var = $tree->getSource();
        $var->insert();

        $result = $var;

        break;

      // hollow, view, ...
      default :

        $content = $tree->reflectApply();
        $this->getWindow()->loadContent($content);

        if ($content instanceof common\arrayable) {

          $this->addToResult($content);
        }
        else {

          $this->getWindow()->add($content);
        }

        $result = $this->getResult();
    }

    return $result;
  }

  protected function loadTree(dom\element $el, $sMode) {

    $el = $this->setNode($el);

    if (!$el->getName() == 'view') {

      $this->throwException('Bad root');
    }

    $this->loadResult();

    $resource = $this->parseElement($this->getx('*[local-name() = "resource"]', true)->remove());
    $resource->setMode($sMode);

    $schema = $resource->setSchema($this->loadSchema());
    $this->setSchema($schema);

    $aResult = $this->parseChildren($el->getChildren()); // unused result

    //$this->getWindow()->add($aResult);

    //$this->loadTemplates();
    $schema->loadTemplates($this->getTemplates());

    return $resource->getTree();

    //$tpl = $this->getTemplate();
    //$tpl->setTree($tree);
  }

  protected function loadSchema() {

    $component = $this->loadSimpleComponent('component/schema', $this);
    $result = $component->parseRoot($this->getx('self:schema', true)->remove());

    return $result;
  }

  protected function setSchema(schema\parser\schema $schema) {

    $this->schema = $schema;
  }

  protected function getSchema() {

    return $this->schema;
  }
}

