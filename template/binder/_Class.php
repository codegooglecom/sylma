<?php

namespace sylma\template\binder;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template, sylma\parser\languages\js;

class _Class extends Basic implements common\arrayable {

  const CONTEXT_ALIAS = 'js/binder/context';
  const JS_OBJECTS_PATH = 'sylma.ui.tmp';
  const TEMPLATE_MODE = 'script';

  protected $object;
  protected $element;
  protected $bRoot = false;
  protected $template = null;

  protected $sExtend = '';
  protected $sID;
  protected $bAdded = false;
  protected $bExtended = false;
  protected $bTemplate = false;

  protected $aAliases = array();

  /**
   * Script var
   */
  protected $source;

  public function parseRoot(dom\element $el) {

    $this->setNode($el, true);
    $this->allowComponent(true);
  }

  public function setElement(template\element $element) {

    $this->element = $element;

    $this->build();
  }

  public function getElement() {

    return $this->element;
  }

  protected function loadName() {

    $this->sName = uniqid('sylma');

    return $this->sName;
  }

  protected function loadParent() {

    return $this->readx('@js:parent');
  }

  protected function loadParentName() {

    return $this->readx('@js:parent-name');
  }

  protected function build() {

    $this->init();

    $this->getParser()->startObject($this);
    $this->startLog("Class [{$this->getExtend()}]");

    $this->getElement()->parseRoot($this->cleanAttributes($this->getNode()));

    $this->getParser()->stopObject(false);
    $this->stopLog();

    //$this->addToWindow();
  }

  protected function setObject(common\_object $obj) {

    $this->object = $obj;
  }

  protected function getObject() {

    if (!$this->object) {

      $this->launchException('No object defined');
    }

    return $this->object;
  }

  public function isRoot($bVal = null) {

    if (is_bool($bVal)) $this->bRoot = $bVal;

    return $this->bRoot;
  }

  protected function init() {

    $obj = $this->getWindow()->createObject();

    $this->setObject($obj);
    //$bName = (bool) $this->readx('@js:name');

    $this->loadID();

    $sParent = $this->loadParent();

    if ($this->isRoot()) {

      if (!$sParent) $sParent = self::JS_OBJECTS_PATH;
    }
    else if ($sParent) {

      $this->throwException('@attribute parent must only appears on root element');
    }

    $this->setExtend($this->readx('@js:class'));

    $obj->setProperty('Extends', $this->getWindow()->createVariable($this->getExtend()));

    if ($sParentName = $this->loadParentName()) {

      $obj->setProperty('sylma.parentName', $sParentName);
    }
    //$this->setExtend($this->readx('@js:class'));

    //$obj->setProperty('name', $bName);
  }

  protected function setExtend($sExtend) {

    $this->sExtend = $sExtend;
  }

  public function getExtend() {

    return $this->sExtend;
  }

  public function setProperty($sName, $val) {

    $this->getObject()->setProperty($sName, $val);
  }

  protected function loadID() {

    $sID = uniqid('sylma');
    $this->setID($sID);

    return $this->getID();
  }

  protected function setID($sID) {

    $this->sID = $sID;
  }

  public function getID() {

    return $this->sID;
  }

  public function setEvent($sName, js\basic\instance\_Object  $val, template\element $el) {

    if ($el !== $this->getElement()) {

      $el->addToken('class', $sName);
      $val->setProperty('node', $sName);
    }

    $this->getObject()->setProperty('events.' . $sName, $val);
  }

  public function setMethod($sName, js\basic\instance\_Object $val) {

    $this->getObject()->setProperty($sName, $val);
  }

  protected function getAlias() {

    return $this->readx('@js:alias');
  }

  /**
   * Called from child classes
   */
  public function addAlias($sAlias, $sName) {

    $sPath = "classes.$sAlias";
    $obj = $this->getObject();

    if (!$obj->getProperty($sPath, false)) {

      $sID = uniqid('sylma');

      $obj->setProperty($sPath, $this->getWindow()->createObject(array(
        'name' => $sName,
        'node' => $sID,
      )));
    }
    else {

      $sID = $obj->getProperty("$sPath.node");
    }

    return $sID;
  }

  public function useTemplate($bValue = null) {

    if (is_bool($bValue)) $this->bTemplate = $bValue;

    return $this->bTemplate;
  }

  protected function loadExtend() {

    if (!$this->bExtended) {

      $this->setExtend('sylma.binder.classes.' . $this->getID());
      $this->bExtended = true;
    }
  }

  public function addTo(common\_object $container) {

    $js = $this->getObject();

    if ($this->useTemplate()) {

      $js->setProperty('buildTemplate', $this->template);
    }

    if (count($js->getProperties()) > 1) {

      $this->loadExtend();

      $class = $this->getWindow()->createObject(array(), 'Class');
      $new = $this->getWindow()->createInstanciate($class, array($js));

      $container->setProperty($this->getID(), $new);
    }
  }

  protected function prepareParent(self $class) {

    return $class->addAlias($this->getAlias(), $this->getExtend());
  }

  protected function setSource(common\_var $source) {

    $this->source = $source;
  }

  protected function getSource() {

    return $this->source;
  }

  public function asArray() {

    $aResult = array();
    $bTemplate = $this->useTemplate();
    $bTemplateView = $this->getRoot()->getMode() === self::TEMPLATE_MODE;

    if ($bTemplate || $bTemplateView) {

      if (!$this->bAdded) {

        $this->loadExtend();
        $obj = $this->getParser()->getObject();
        $class = $bTemplateView ? $obj : $obj->getClass();

        $sID = $this->prepareParent($class);

        $window = $this->getWindow();

        $source = $window->createVariable('item');
        $self = $window->createVariable('this');

        $this->setSource($source);
        $this->getParser()->startSource($source);
        $this->getParser()->startObject($this);


        if (!$bTemplateView) {

          $root = $this->getRoot();
          $sMode = $root->getMode();

          $root->setMode(self::TEMPLATE_MODE);
        }
        else {

          $sAlias = $this->getAlias();
          $aResult[] = $self->call('buildObjects', array($window->toString($sAlias), $source->getProperty($sAlias)));
        }

        $parser = $this->getParser()->getParent();
        $aResult[] = $parser->parseFromChild($this->createElement('span', null, array('class' => $sID), \Sylma::read('namespaces/html'), false));

        $this->getElement()->setAttribute('id', $self->getProperty('id'));
        $content = $window->toString($this->getElement());

        $this->template = $window->createFunction(array($source->getName()));
        $this->template->addContent($window->createReturn($content));

        $this->getParser()->stopSource();
        $this->getParser()->stopObject();

        if (!$bTemplateView) {

          $root->setMode($sMode);
        }

        $this->bAdded = true;
      }
    }
    else {

      $obj = $this->loadSimpleComponent('object');
      $obj->setClass($this);

      $obj->parseRoot($this->getNode());
      $aResult[] = $obj->asArray();
    }

    return $aResult;
  }
}

