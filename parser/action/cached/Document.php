<?php

namespace sylma\parser\action\cached;
use sylma\core, sylma\dom, sylma\parser, sylma\storage\fs;

class Document extends Basic implements core\stringable, dom\domable {

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

      $doc = $this->getHandler()->getControler()->loadTemplate($this->sTemplate, 0, $aArguments);
      $mResult = $this->loadParsers($doc);
    }
    else {

      $mResult = $aArguments;
    }

    return $mResult;
  }

  protected function getParser($sNamespace) {

    return array_key_exists($sNamespace, $this->aParsers) ? $this->aParsers[$sNamespace] : null;
  }

  public function loadParser($sNamespace) {

    if (!$result = $this->getParser($sNamespace)) {

      $manager = $this->getControler('parser');

      $result = $manager->getParser($sNamespace, $this);
      $result->setParent($this);

      $this->addParser($sNamespace, $result);
    }

    return $result;
  }

  protected function addParser($sNamespace, parser\cached\documented $parser) {

    $this->aParsers[$sNamespace] = $parser;
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

  protected function setTemplate($sTemplate) {

    $this->sTemplate = $sTemplate;
  }

  protected function useTemplate() {

    return (bool) $this->sTemplate;
  }

  public function asDOM() {

    return $this->getContext()->asDOM();
  }
}
