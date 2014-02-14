<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\storage\sql, sylma\schema;

abstract class Ordered extends Joined {

  protected $order;
  protected $orderFunction;
  protected $orderPath;
  protected $orderDynamic;

  public function setOrderPath($sValue) {

    $this->orderPath = $sValue;
  }

  protected function getOrderPath() {

    return $this->orderPath;
  }

  public function setOrderFunction($sValue) {

    $this->orderFunction = $sValue;
  }

  protected function getOrderFunction() {

    return $this->orderFunction;
  }

  public function setOrderDynamic($content) {

    $this->orderDynamic = $content;
  }

  protected function getOrderDynamic() {

    return $this->orderDynamic;
  }

  protected function getOrder() {

    return $this->order;
  }

  protected function prepareOrder() {

    $aResult = array();
    $aElements = array();
    $obj = null;

    $string = $this->getParser()->getType('string', $this->getParser()->getNamespace('sql'));

    if ($sPath = $this->getOrderFunction()) {

      $this->order = $this->getWindow()->toString(" ORDER BY $sPath");
    }
    else {

      if ($sPath = $this->getOrderPath()) {

        $obj = $this->createOrderStatic($sPath, $aElements, $string);
      }
      else if ($content = $this->getOrderDynamic()) {

        $obj = $this->createOrderDynamic($content, $aElements, $string);
      }

      if ($obj) {

        $aResult[] = $obj->getInsert();
        $aResult[] = $obj->call('setElements', array($aElements));

        $this->order = $obj;
      }
    }

    return $aResult;
  }

  protected function createOrderStatic($sPath, array &$aElements, schema\parser\type $string) {

    $result = $this->createObject('order', array($sPath));

    $order = $this->create('order', array($sPath));
    $table = $this->aTables[0];

    foreach ($order->extractPath() as $aElement) {

      $field = $table->getElement($aElement['name']);

      $aElements[$field->getName()] = array(
        'alias' => $field,
        'string' => $field->getType()->doExtends($string),
      );
    }

    return $result;
  }

  protected function createOrderDynamic($content, array &$aElements, schema\parser\type $string) {

    foreach ($this->getElements() as $field) {

      $aElements[$field->getName()] = array(
        'alias' => $field,
        'string' => $field->getType()->doExtends($string),
      );
    }

    $result = $this->createObject('order', array($content));

    // On join, only first element is used as order, maybe todo

    foreach ($this->aJoins as $aJoin) {

      $foreign = $aJoin[2];
      $ref = $aJoin[1];

      if (!$ref instanceof sql\schema\element) {

        $this->launchException('Cannot prepare unknown for order', get_defined_vars());
      }

      foreach ($this->getElements() as $el) {

        if ($el->getParent() === $ref->getParent()) {

          $aElements[$foreign->getName()] = array(
            'alias' => $el,
            'string' => $el->getType()->doExtends($string),
          );

          break;
        }
      }
    }

    return $result;
  }

  public function clearOrder() {

    $this->order = null;
  }
}
