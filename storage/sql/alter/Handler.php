<?php

namespace sylma\storage\sql\alter;
use sylma\core, sylma\dom, sylma\storage\sql;

class Handler extends core\module\Domed implements core\stringable {

  public function asString() {

    $this->setDirectory(__FILE__);

    $file = $this->getFile();
    $builder = $this->getManager(self::PARSER_MANAGER)->loadBuilder($file, null, $this->getScript('builder.xml'));
    $sql = $this->getManager('mysql');

    $schema = $builder->getSchema($file);

    $table = $schema->getElement();

    //dsp($table->asCreate());
    //dsp($table->asUpdate());

    $sql->read($table->asCreate());
    $sql->read($table->asUpdate());
  }
}

