<?php

namespace sylma\modules\tester;

require_once('modules/tester/test.php');

abstract class Basic extends \Module implements test {
  
  const NS = 'http://www.sylma.org/modules/test';
  protected $sTitle;
  
  public function __construct() {
    
    $this->setNamespace(self::NS, 'test');
  }
  
  public function parse() {
    
    $result = \Arguments::buildDocument(array(
      'group' => array(
        'description' => t($this->sTitle),
        '#group' => $this->load(),
      ),
    ), $this->getNamespace());
    
    return $result;
  }
}