<?php

namespace sylma\view\parser;
use sylma\core, sylma\dom, sylma\template, sylma\parser\reflector, sylma\schema;

class Elemented extends template\parser\Elemented {

  const NS = 'http://2013.sylma.org/view';
  const NS_SCHEMA = 'http://2013.sylma.org/schema/template';

  const TMP_ARGUMENTS = 'view.xml';

  protected $allowForeign = true;

  protected $schema;
  protected $resource;
  protected $aEntries = array();

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

    $this->build($el, $sMode); // parseRoot(), onAdd()

    $this->addToResult(array($this->getTemplate())); // asArray()


    switch ($sMode) {

      case 'insert' :

        $var = $this->getTemplate()->getTree()->getSource();
        $var->insert();

        $result = $var;

        break;

      default :

        $result = $this->getResult();
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

  protected function build(dom\element $el, $sMode) {

    $this->setNode($el);

    if (!$el->getName() == 'view') {

      $this->throwException('Bad root');
    }

    $this->loadResult();

    $resource = $this->parseElement($this->getx('*[local-name() = "resource"]'));
    $resource->setMode($sMode);

    $schema = $resource->setSchema($this->loadSchema());
    $this->setSchema($schema);

    $this->loadTemplates();
    $schema->loadTemplates($this->getTemplates());

    $tpl = $this->getTemplate();
    $tpl->setTree($resource->getTree());
  }

  protected function loadSchema() {

    $component = $this->loadSimpleComponent('component/schema', $this);
    $result = $component->parseRoot($this->getx('self:schema', true));

    return $result;
  }

  protected function setSchema(schema\parser\schema $schema) {

    $this->schema = $schema;
  }

  protected function getSchema() {

    return $this->schema;
  }
}

