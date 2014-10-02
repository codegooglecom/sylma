<?php

namespace sylma\modules\html;
use sylma\core, sylma\dom, sylma\storage\fs;

class Cleaner extends core\module\Domed {

  const TEMPLATE_PATH = 'cleaner.xsl';

  public function __construct(fs\file $file = null) {

    $this->setDirectory(__FILE__);

    if (!$file) {

      $file = $this->getFile(self::TEMPLATE_PATH);
    }

    $this->setFile($file);
    $this->loadDefaultArguments();
  }

  public function clean(dom\handler $doc) {

    $sResult = '';

    if (!$doc->isEmpty()) {

      $sResult = $this->cleanValid($doc);
    }

    return $sResult;
  }

  protected function cleanValid(dom\handler $doc) {

    require_once('dom/handler.php');

    $cleaner = $this->getTemplate((string) $this->getFile());

    $cleaned = $cleaner->parseDocument($doc);

    $iMode = 0;
    if (\Sylma::read('initializer/output/indent')) $iMode = dom\handler::STRING_INDENT;

    return $cleaned->asString($iMode); // | dom\handler::STRING_HEAD
  }
}