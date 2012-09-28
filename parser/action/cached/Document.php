<?php

namespace sylma\parser\action\cached;
use \sylma\core, \sylma\dom, \sylma\parser, \sylma\storage\fs;

require_once('Basic.php');
require_once('dom/domable.php');

class Document extends Basic implements dom\domable {

  protected $sTemplate = '';
  protected $aParsers = array();

  protected function runAction(fs\file $file) {

    $aArguments = parent::runAction($file);

    if ($this->useTemplate()) {
/*
      $controler = $this->getControler();
      $file = $controler->getFile();

      $sTemplate = $file->getParent()->getDirectory(parser\action::EXPORT_DIRECTORY)->getRealPath() . '/' . $file->getName() . '.tpl.php';
*/

      $doc = $this->loadTemplate(0, $aArguments);
      $mResult = $this->loadParsers($doc);
    }
    else {

      $mResult = $aArguments;
    }

    return $mResult;
  }

  public function loadParser($sNamespace) {

    $manager = $this->getControler('parser');

    $result = $manager->getParser($sNamespace, $this);
    $result->setParent($this);

    $this->setParser($result);

    return $result;
  }

  protected function setParser(parser\cached\documented $parser) {

    $this->aParsers[] = $parser;
  }

  protected function getParsers() {

    return $this->aParsers;
  }

  protected function loadParsers(dom\document $result) {

    foreach ($this->getParsers() as $parser) {

      $result = $parser->parseDocument($result);
    }

    return $result;
  }

  protected function loadTemplate($iKey, array $aArguments) {

    $sResult = $this->includeTemplate($this->sTemplate, $iKey, $aArguments);

    $doc = $this->create('document');
    $doc->setContent($sResult);

    return $doc;
  }

  protected function includeTemplate($sTemplate, $iTemplate, array $aArguments) {

    ob_start();

    include($sTemplate);
    $sResult = ob_get_contents();

    ob_end_clean();

    return $sResult;
  }

  protected function setTemplate($sTemplate) {

    $this->sTemplate = $sTemplate;
  }

  protected function useTemplate() {

    return (bool) $this->sTemplate;
  }

  protected function loadDomable(dom\domable $val) {

    $dom = $val->asDOM();

    return $dom;
  }

  public function asDOM() {

    return $this->getContext()->asDOM();
  }
}
