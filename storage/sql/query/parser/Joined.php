<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\storage\sql;

abstract class Joined extends Wherer {

  protected $aJoins = array();
  protected $aJoinsElements = array();
  protected $aJoinsTables = array();

  /**
   * @usedby sql\template\view\Foreign::reflectFunctionJoin()
   * @usedby sql\template\view\Foreign::buildSingle()
   * @usedby sql\template\view\Foreign::buildMultiple()
   * @usedby sql\template\view\Reference::reflectFunctionJoin()
   */
  public function addJoin(sql\schema\table $result, sql\schema\element $field, $val, $bClone = false) {

    $bAdd = true;

    foreach ($this->aJoinsElements as $aJoin) {

      if ($aJoin[0] === $val && $aJoin[1] === $result->getName()) {

        $bAdd = false;
      }
    }

    if ($bAdd) {

      if (!$bClone) {

        $sName = $result->getName();

        if (isset($this->aJoinsTables[$sName])) {

          $result = clone $result;
          $field = $result->getElement($field->getName());
          //$field = clone $field; // @todo : not great, cloned but not referenced in table

          $result->setAlias($sName . $this->aJoinsTables[$sName]);
          $field->setParent($result);

          $this->aJoinsTables[$sName]++;
        }
        else {

          $this->aJoinsTables[$sName] = 1;
        }
      }

      foreach($this->getClones() as $clone) {

        $clone->addJoin($result, $field, $val, true);
      }

      $this->aJoins[] = array($result->asAlias(), $field, $val);
      $this->aJoinsElements[] = array($val, $result->getName());
    }

    return $result;
  }

  protected function getJoins() {

    $aResult = array();

    foreach ($this->aJoins as $iCurrent => $aJoin) {

      $aResult[] = array(' LEFT JOIN ', $aJoin[0], ' ON ', $this->prepareElement($aJoin[1]), ' = ', $aJoin[2], ' ');
    }

    return $aResult;
  }

  protected function prepareElement(sql\schema\element $element) {

    return $element->asString();
  }

  public function clearJoins() {

    $this->aJoins = array();
  }
}
