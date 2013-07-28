<?php

namespace sylma\storage\sql\alter\component;
use sylma\core, sylma\schema\xsd as schema;

class Simple extends schema\component\SimpleType {

  public function asString() {

    if ($this->doExtends($this->getParser()->getType('datetime', $this->getNamespace('sql')))) {

      $sValue = 'TIMESTAMP';
    }
    else if ($this->doExtends($this->getParser()->getType('text', $this->getNamespace('sql')))) {

      $sValue = 'MEDIUMTEXT';
    }
    else if ($this->doExtends($this->getParser()->getType('string', $this->getNamespace('xs')))) {

      $iSize = $this->getDefine()->getRule('maxLength');

      if (!$iSize) {

        $this->launchException('No @maxLength defined');
      }

      if ($iSize > 255) {

        $sValue = 'TEXT';
      }
      else {

        $sValue = "VARCHAR({$iSize})";
      }
    }
    else if ($this->doExtends($this->getParser()->getType('integer', $this->getNamespace('xs')))) {

      $iSize = $this->getDefine()->getRule('totalDigits');

      if (!$iSize) {

        $this->launchException('No @totalDigits defined');
      }

      $sValue = "INT({$iSize})";
    }
    else if ($this->doExtends($this->getParser()->getType('boolean', $this->getNamespace('xs')))) {

      $sValue = "TINYINT(1)";
    }
    else if ($this->doExtends ($this->getParser ()->getType('id', $this->getNamespace ('sql')))) {

      $sValue = 'BIGINT UNSIGNED';
    }
    else {

      $iSize = 11;
      $sValue = "INT({$iSize})";
    }

    return $sValue;
  }
}

