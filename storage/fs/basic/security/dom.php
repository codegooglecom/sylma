<?php

namespace sylma\storage\fs\basic\security;
use \sylma\dom;

require_once('dom2/document.php');
require_once('dom2/element.php');

class document extends \XML_Document implements dom\document {
  
  public function __construct($mChildren = '', $iMode = MODE_READ, $bInclude = false) {
    
    parent::__construct($mChildren, $iMode, $bInclude);
    $this->registerNodeClass('DOMElement', '\sylma\storage\fs\basic\security\element');
  }
}

class element extends \XML_Element implements dom\element {

}

