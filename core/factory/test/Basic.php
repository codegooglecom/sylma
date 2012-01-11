<?php

namespace sylma\core\factory\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs, \sylma\parser;

require_once('modules/tester/Basic.php');

class Basic extends tester\Basic {
  
  const NS = 'http://www.sylma.org/core/factory/test';
  
  protected $sTitle = 'Factory';
  
  /**
   * @var core\factory
   */
  protected $factory;
  
  public function __construct(core\factory $factory = null) {
    
    \Sylma::getControler('dom');
    
    $this->setDirectory(__file__);
    $this->setNamespaces(array(
        'self' => self::NS,
    ));
    
    if (!$factory) $factory = \Sylma::getControler('factory');
    $this->factory = $factory;
    
    $this->setControler($this);
  }
  
  public function createArgument($mArguments, $sNamespace = '') {
    
    return parent::createArgument($mArguments, $sNamespace);
  }
  
  public function getDirectory($sPath = '', $bDebug = true) {
    
    return parent::getDirectory($sPath, $bDebug);
  }
  
  public function getFactory() {
    
    return $this->factory;
  }
}

