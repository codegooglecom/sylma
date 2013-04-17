<?php

namespace sylma\core\argument;
use sylma\core;

class Importer extends core\module\Filed {

  public static function import($sPath) {

    $fs = \Sylma::getManager(self::FILE_MANAGER);
    $parser = \Sylma::getManager(self::PARSER_MANAGER);

    return $parser->load($fs->getFile($sPath));
  }
}

