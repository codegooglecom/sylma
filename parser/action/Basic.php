<?php

namespace sylma\parser\action;
use \sylma\core, \sylma\dom, \sylma\parser, \sylma\storage\fs;

require_once('parser/action.php');
require_once('core/controled.php');
require_once('dom2/domable.php');
require_once('core/argumentable.php');
require_once('core/module/Domed.php');

abstract class Basic extends core\module\Domed implements core\controled, dom\domable {
  
  public function __construct(fs\directory $dir, core\factory $controler) {
    
    $this->setControler($controler);
    $this->setDirectory($dir);
    $this->setNamespace(parser\action::NS);
  }
  
  protected function loadTemplate(array $aArguments) {
    
    $controler = $this->getControler();
    $file = $controler->getFile();
    
    $sTemplate = $file->getParent() . '/#tmp/' . $file->getName() . '.tpl.php';
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
  
  protected function loadArgumentable(core\argumentable $val) {
    
    $arg = $val->asArgument();
    
    return $this->loadDomable($arg);
  }
  
  protected function loadDomable(dom\domable $val) {
    
    $dom = $val->asDOM();
    
    return $dom;
  }
  
  public function asDOM() {
    
    $mResult = null;
    $mAction = $this->parseAction();
    
    if (is_array($mAction)) {
      
      $mResult = $this->getControler()->create('document');
      $mResult->add($mAction);
    }
    else if ($mAction instanceof dom\node) {
      
      dspf($this->getControler()->getArguments()->get('classes/document'));
      $mResult = $this->getControler()->create('document');
      dspf($mResult);
    }
    else if ($mAction instanceof dom\domable) {
      
      $mResult = $mAction->asDOM();
    }
    
    return $mResult;
  }
}
