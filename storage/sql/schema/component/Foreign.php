<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\dom, sylma\schema, sylma\storage\sql;

class Foreign extends Element implements sql\schema\foreign {

  const PREFIX = 'sql';
  const JUNCTION_MODE = 'view';

  protected $elementRef;

  public function parseRoot(dom\element $el) {

    $this->setNode($el, false);

    $this->loadName();
    $this->loadType();

    $this->reflectOccurs($el);
    $this->loadOptional();
  }

  protected function loadName() {

    $this->setName($this->readx('@name'));
  }

  protected function loadType() {

    $this->setType($this->getParser()->getType('foreign', $this->getParser()->getNamespace(self::PREFIX)));
  }

  protected function loadElementRef() {

    list($sNamespace, $sName) = $this->parseName($this->readx('@table', true));

    return $this->getParser()->getElement($sName, $sNamespace, false);
  }

  protected function getElementRefFile() {

    if ($sImport = $this->readx('@import')) {

      $result = $this->getSourceFile($sImport);
    }
    else {

      $result = null;
    }

    return $result;
  }

  protected function importElementRef() {

    if (!$result = $this->loadElementRef()) {

      $file = $this->getElementRefFile();
      $this->getParser()->addSchema($file->getDocument());

      $result = $this->loadElementRef($file);
    }

    return $result;
  }

  public function getElementRef() {

    if (!$this->elementRef) {

      if (is_null($this->elementRef)) {

        $this->setElementRef($this->importElementRef());
      }
      else {

        $this->throwException('No ref element defined');
      }
    }

    $result = $this->elementRef;

    if ($result) {

      $result->setParent($this);
    }

    return $result;
  }

  public function setElementRef(Table $element) {

    $this->elementRef = $element;
  }

  protected function reflectOccurs(dom\element $el) {

    if (!$sOccurs = $el->readx('@occurs', array(), false)) {

      $sOccurs = '1..1';
    }

    list($iMin, $iMax) = explode('..', $sOccurs);
    $this->setOccurs($iMin, $iMax);
  }

  protected function loadJunction() {

    $sName = $this->readx('@junction', true);

    $field = $this->getElementRef();
    $parent = $this->getParent();

    $sSource = 'id_' . $parent->getName();
    $sTarget = 'id_' . $field->getName();
    $sConnection = $parent->getConnectionAlias();

    $doc = $this->createArgument(array(
      'schema' => array(
        '@targetNamespace' => $this->getNamespace(),
        'table' => array(
          '@name' => $sName,
          '@connection' => $sConnection,
          '#foreign' => array(
            array(
              '@name' => $sSource,
              '@occurs' => '0..1',
              '@table' => 't1:' . $parent->getName(),
              '@import' => (string) $this->getSourceFile(),
            ),
            array(
              '@name' => $sTarget,
              '@occurs' => '0..1',
              '@table' => 't2:' . $field->getName(),
              '@import' => (string) $this->getSourceFile($this->readx('@import')),
            ),
          ),
        ),
      ),
    ), $this->getNamespace('sql'))->asDOM();

    $doc->registerNamespaces(array(
      't1' => $this->getNamespace(),
      't2' => $field->getNamespace(),
    ));

    $sql = $this->getManager(self::DB_MANAGER)->getConnection($sConnection);

    if (!$sql->read("show tables like '$sName'", false)) {

      $handler = new sql\alter\Handler;
      $handler->setDocument($doc);

      $handler->asString();
    }

    $this->getParser()->changeMode(static::JUNCTION_MODE);

    $sElement = $this->getParser()->addSchema($doc);

    $table = $this->getParser()->getElement($sElement, $this->getNamespace());
    $table->isSub(true);

    $source = $table->getElement($sSource);
    $target = $table->getElement($sTarget);

    $this->getParser()->resetMode();

    return array($table, $source, $target);
  }

}

