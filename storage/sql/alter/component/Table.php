<?php

namespace sylma\storage\sql\alter\component;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\storage\fs;

class Table extends sql\schema\component\Table implements sql\alter\alterable {

  const SQL_PARSER = 'mysql';

  protected $bDepth = false;

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);
  }

  protected function loadColumns() {

    $sql = $this->getManager(self::SQL_PARSER);
    $cols = $sql->query("SHOW COLUMNS FROM `{$this->getName()}`", false);
    $aResult = array();

    foreach ($cols as $iPosition => $col) {

      $col->set('position', $iPosition);
      $aResult[$col->read('Field')] = $col;
    }

    $this->columns = $this->createArgument($aResult);
  }

  public function getColumn($sName) {

    return $this->columns->get($sName, false);
  }

  public function asUpdate() {

    if (!$this->useDepth()) {

      $sql = $this->getManager('mysql');

      $sQuery = $this->loadUpdate();

      dsp($sQuery);
      $sql->read($sQuery);
    }
  }

  protected function loadUpdate() {

    $this->loadColumns();

    foreach ($this->getElements() as $element) {

      if ($element instanceof sql\schema\reference) continue;
      $aChildren[] = $this->updateChild($element);
    }

    return "ALTER TABLE `{$this->getName()}` " . implode(",\n", $aChildren) . ';';
  }

  protected function updateChild(sql\alter\alterable $element) {

    return $element->asUpdate();
  }

  protected function useDepth($bDepth = null) {

    if (is_bool($bDepth)) $this->bDepth = $bDepth;

    return $this->bDepth;
  }

  public function asCreate($bDepth = false) {

    $this->useDepth($bDepth);
    $aReferences = $aForeigns = array();

    foreach ($this->getElements() as $element) {

      if ($element instanceof sql\schema\reference) {

        $aReferences[] = $element;
      }
      else {

        if ($element instanceof sql\schema\foreign) {

          $aForeigns[] = $element;
        }

        if (!$element->getMaxOccurs(true)) {

          $aChildren[] = $this->createChild($element);
        }
      }
    }

    $sQuery = "CREATE TABLE IF NOT EXISTS `{$this->getName()}` (" . implode(",\n", $aChildren) . ');';

    $sql = $this->getManager('mysql');

    $sql->read($sQuery);
    dsp($sQuery);

    foreach ($aReferences as $ref) {

      $ref->asString();
    }

    foreach ($aForeigns as $ref) {

      $ref->asJunction();
    }
  }

  public function buildSchema(fs\file $file) {

    if ($this->useDepth()) {

      $handler = $this->create('handler');
      $handler->useDepth(true);
      $handler->setFile($file);

      $handler->asString();
    }
  }

  protected function createChild(sql\alter\alterable $element) {

    return $element->asCreate();
  }

  public function fieldAsUpdate($field, $previous) {

    $sResult = '';

    $previous = $field->getPrevious();
    $sPosition = $previous ? " AFTER `{$previous->getName()}`" : ' FIRST';

    if ($col = $field->getParent()->getColumn($field->getName())) {

      $sName = $field->getName();
      $sResult = "CHANGE `{$sName}` " . $field->asString() . $sPosition;
    }
    else {

      $sResult = "ADD " . $field->asString() . $sPosition;
    }

    return $sResult;
  }
}

