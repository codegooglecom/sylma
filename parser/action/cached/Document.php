<?php

namespace sylma\parser\action\cached;
use \sylma\core, \sylma\dom, \sylma\parser, \sylma\storage\fs;

require_once('Basic.php');
require_once('dom2/domable.php');

abstract class Document extends Basic implements dom\domable {

  protected function loadTemplate(array $aArguments) {

    $controler = $this->getControler();
    $file = $controler->getFile();

    $sTemplate = $file->getParent()->getDirectory(parser\action::EXPORT_DIRECTORY)->getRealPath() . '/' . $file->getName() . '.tpl.php';
    $sResult = $this->includeTemplate($sTemplate, $aArguments);

    $doc = $controler->create('document');
    $doc->loadText($sResult, false);

    return $doc;
  }

  protected function includeTemplate($sTemplate, array $aArguments) {

    ob_start();

    include($sTemplate);
    $sResult = ob_get_contents();

    ob_end_clean();

    return $sResult;
  }

  protected function loadDomable(dom\domable $val) {

    $dom = $val->asDOM();

    return $dom;
  }

  public function asDOM() {

    $mAction = $this->parseAction();

    if ($this->useTemplate()) {

      $mResult = $mAction;
    }
    else {

      $iAction = count($mAction);

      if ($iAction == 1) {

        $mAction = array_pop($mAction);
      }

      if ($iAction > 1 || !($mAction instanceof dom\handler)) {

        $mResult = $this->getControler()->create('document');
        $mResult->add($mAction);
      }
      else {

        $mResult = $mAction;
      }
    }

    return $mResult;
  }
}
