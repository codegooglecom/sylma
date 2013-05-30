<?php

namespace sylma\modules\html;
use sylma\core, sylma\dom;

class Cleaner extends core\module\Domed {

  public function __construct() {

    $this->setDirectory(__FILE__);
    $this->loadDefaultArguments();
  }

  public function clean(dom\handler $doc) {

    require_once('dom/handler.php');

    $cleaner = $this->getTemplate('cleaner.xsl');

    $cleaned = $cleaner->parseDocument($doc);

    $iMode = 0;
    if (\Sylma::read('initializer/output/indent')) $iMode = dom\handler::STRING_INDENT;

    return $cleaned->asString($iMode); // | dom\handler::STRING_HEAD
  }
}