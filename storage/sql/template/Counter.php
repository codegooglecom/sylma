<?php

namespace sylma\storage\sql\template;
use sylma\core, sylma\parser\reflector, sylma\parser\languages\common, sylma\storage\sql;

class Counter extends reflector\component\Foreigner implements reflector\component, common\argumentable, common\instruction {

  protected $bUsed = false;
  protected $query;
  protected $content;

  public function setQuery(sql\query\parser\Select $query) {

    $query->isMultiple(false);
    $query->setMethod('read');

    $this->query = $query;
  }

  protected function getQuery() {

    return $this->query;
  }

  public function getVar() {

    $this->isUsed(true);

    return $this->getQuery()->getVar();
  }

  protected function isUsed($bVal = NULL) {

    if (is_bool($bVal)) $this->bUsed = $bVal;

    return $this->bUsed;
  }

  public function setDistinct($content) {

    $this->content = $content;
  }

  protected function getDistinct() {

    return $this->content;
  }

  public function asArgument() {

    if ($this->isUsed()) {

      $query = $this->getQuery();

      $query->clearColumns();
      $query->clearLimit();
      $query->clearOrder();
      //$query->clearJoins();

      if ($content = $this->getDistinct()) {

        $aContent = array(
          'COUNT(DISTINCT ',
          $content,
          ')',
        );
      }
      else {

        $aContent = array('COUNT(*)');
      }

      $query->setColumn($aContent);

      $result = $this->getQuery()->asArgument();
    }
    else {

      $result = null;
    }

    return $result;
  }
}

