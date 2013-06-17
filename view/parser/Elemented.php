<?php

namespace sylma\view\parser;
use sylma\core, sylma\dom, sylma\template, sylma\parser\reflector, sylma\schema, sylma\parser\languages\common;

class Elemented extends template\parser\handler\Domed {

  const NS = 'http://2013.sylma.org/view';
  const PREFIX = 'view';

  const SCHEMA_NS = 'http://2013.sylma.org/schema/template';
  const SCHEMA_PREFIX = 'stp';

  const BINDER_NS = 'http://2013.sylma.org/template/binder';

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
    $this->setNamespace(self::SCHEMA_NS, self::SCHEMA_PREFIX);
    $this->setNamespace(parent::NS, parent::PREFIX);
    $this->setNamespace(self::NS, self::PREFIX);

    parent::__construct($root, $parent, $arg);
  }

  public function parseRoot(dom\element $el, $sMode = '') {

    $this->allowUnknown(true);
    $this->allowForeign(true);

    $el = $this->setNode($el);

    if ($el->getNamespace() !== self::NS) {

      $this->launchException('Bad namespace');
    }

    $this->setMode($sMode);

    $resource = $this->loadResource($sMode); // parseRoot(), onAdd()

    $schema = $resource->setSchema($this->loadSchema());
    $this->setSchema($schema);

    $aContent = $this->parseChildren($el->getChildren());

    $schema->loadTemplates($this->getTemplates());

    $window = $this->getWindow();
    $window->add($window->parseArrayables($aContent));

    $result = $this->build($aContent, $resource->getTree(), $sMode); // asArray()

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

    switch ($el->getNamespace()) {

      case self::BINDER_NS;

        if ($this->getMode() !== 'view') {

          $result = null;
          break;
        }

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

    if (!$sPrefix) {

      $sResult = $this->getSchema()->lookupNamespace($sPrefix);
    }
    else if (!$sResult = parent::lookupNamespace($sPrefix)) {

      $sResult = $this->getNode()->lookupNamespace($sPrefix);
    }

    return $sResult;
  }

  protected function build(array $aContent, template\parser\tree $tree, $sMode) {

    $window = $this->getWindow();

    $content = $tree->reflectApply();
    //$window->loadContent($content);

    switch ($sMode) {

      case 'update' :
      case 'insert' :

        $this->addToResult($content, false);
        $window->add($tree->asArgument());
        $result = $this->getResult();

        break;

      // hollow, view, ...
      default :

        $this->addToResult($content);
        $result = $this->getResult();
    }

    return $result;
  }

  protected function loadResource($sMode) {

    $el = $this->getNode();

    if (!$el->getName() == 'view') {

      $this->throwException('Bad root');
    }

    $this->loadResult();

    $resource = $this->parseElement($this->getx('*[local-name() = "resource"]', true)->remove());
    $resource->setMode($sMode);

    return $resource;
  }

  protected function loadSchema() {

    $component = $this->loadSimpleComponent('component/schema', $this);
    $result = $component->parseRoot($this->getx('view:schema', true)->remove());

    return $result;
  }

  protected function setSchema(schema\parser\schema $schema) {

    $this->schema = $schema;
  }

  public function getSchema() {

    return $this->schema;
  }
}

