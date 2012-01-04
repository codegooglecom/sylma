<?php

namespace sylma\parser\action;
use \sylma\core, \sylma\dom, \sylma\parser, \sylma\storage\fs;

require_once('Reflector.php');
require_once('parser/domed.php');

class Domed extends Reflector implements parser\domed {
  
  const PREFIX = 'le';
  const CONTROLER = 'parser/action';
  const FORMATER_ALIAS = 'formater';
  
  /**
   * See @method setFile()
   * @var storage\fs
   */
  private $document;
  private $aArguments = array();
  
  private $bTemplate = false;
  /**
   * Sub parsers
   * @var array
   */
  private $aParsers = array();
  
  public function __construct(core\factory $controler, dom\handler $doc, fs\directory $dir) {
    
    $this->setDocument($doc);
    $this->setControler($controler);
    $this->setNamespace(self::NS, self::PREFIX);
    $this->setDirectory($dir);
  }
  
  protected function setDocument(dom\handler $doc) {
    
    $this->document = $doc;
  }
  
  protected function getDocument() {
    
    return $this->document;
  }
  
  public function setArguments(array $aArguments) {
    
    $this->aArguments = $aArguments;
  }
  
  public function getArguments() {
    
    return $this->aArguments;
  }
  
  private function getParser($sUri) {
    
    return array_key_exists($sUri, $this->aParsers) ? $this->aParsers[$sUri] : null;
  }
  
  protected function extractArguments(dom\element $settings) {
    
    $aResult = array();
    $args = $settings->query('le:argument');
    
    foreach ($args as $arg) {
      
      
    }
    
    return $aResult;
  }
  
  protected function parseSettings(dom\element $settings) {
    
    foreach ($settings->getChildren() as $el) {
      
      if ($el->getNamespace() == $this->getNamespace()) {
        
        switch ($el->getName()) {
          
          case 'argument' : break;
          case 'name' : $this->setName($el->read()); break;
          
          default : $this->parseElement($el);
        }
      }
      else {
        
        $this->parseElement($el);
      }
    }
  }
  
  protected function parseDocument(dom\document $doc) {
    
    $aResult = array();
    
    if ($doc->isEmpty()) {
      
      $this->throwException(t('empty doc'));
    }
    
    $doc->registerNamespaces($this->getNS());
    
    $settings = $doc->get(self::PREFIX . ':settings', $this->getNS());
    
    // arguments
    
    if ($settings) {
      
      $aArguments = $this->extractArguments($settings);
      $this->parseSettings($settings);
      $settings->remove();
    }
    
    $aResult = $this->parseChildren($doc);
    
    return $aResult;
  }
  
  protected function parseNode(dom\node $node) {
    
    $mResult = null;
    
    switch ($node->getType()) {
      
      case dom\node::ELEMENT :
        
        $mResult = $this->parseElement($node);
      
      break;
      
      case dom\node::TEXT :
        
        $mResult = $node->read();
        
      break;
      
      case dom\node::COMMENT :
      
      break;
      
      default : 
        
        $this->throwException(txt('Unknown node type : %s', $node->getType()));
    }
    
    return $mResult;
  }
  
  /**
   *
   * @param dom\element $el
   * @return type core\argumentable|array|null
   */
  public function parseElement(dom\element $el) {
    
    $sNamespace = $el->getNamespace();
    $mResult = null;
    
    if ($sNamespace == $this->getNamespace()) {
      
      $mResult = $this->parseElementSelf($el);
    }
    else {
      
      $mResult = $this->parseElementForeign($el);
    }
    
    return $mResult;
  }
  
  protected function useTemplate($bValue = null) {
    
    if (!is_null($bValue)) $this->bTemplate = $bValue;
    
    return $this->bTemplate;
  }
  /**
   *
   * @param dom\element $el
   * @return dom\node|array|null
   */
  protected function parseElementForeign(dom\element $el) {
    
    $mResult = null;
    
    if ($parser = $this->getParser($el->getNamespace())) {
      
      $mResult = $parser->parseElement($el);
    }
    else {
      
      $this->useTemplate(true);
      
      $mResult = $this->getControler()->create('document');
      $mResult->addElement($el->getName(), null, array(), $el->getNamespace());
      
      $mResult->add($this->parseChildren($el));
      
      $mResult = $mResult;
    }
    
    return $mResult;
  }
  
  /**
   *
   * @param dom\element $el
   * @return array
   */
  protected function parseChildren(dom\complex $el) {
    
    $aResult = array();
    
    foreach ($el->getChildren() as $child) {
      
      if ($mResult = $this->parseElement($child)) {
        
        if ($mResult instanceof dom\node) $mResult = $mResult;
        else $mResult = $this->getWindow()->createInsert($mResult);
        
        $aResult[] = $mResult;
      }
    }
    
    return $aResult;
  }
  
  /**
   *
   * @param dom\element $el
   * @return core\argumentable|array|null
   */
  protected function parseElementSelf(dom\element $el) {
    
    $mResult = null;
    
    switch ($el->getName()) {
      
      case 'action' : $mResult = $this->reflectAction($el); break;
    
      case 'call' :
        
        $this->throwException(txt('Cannot use %s here', $el->asToken()));
        
      case 'directory' :
        
        $call = $this->reflectDirectory($el);
        
        if ($el->hasChildren()) {
          
          $var = $call->getVar();
          $mResult = $this->runElement($el, $var);
        }
        else {
          
          $mResult = $call;
        }
        
      break;
      
      case 'argument' :
      case 'test-argument' :
      case 'get-all-arguments' :
      case 'get-argument' :
      // case 'get-settings' :
      case 'set-variable' :
      case 'get-variable' :
      case 'switch' :
      case 'function' :
      case 'interface' :
      break;
      case 'xquery' :
      case 'recall' :
      case 'namespace' :
      case 'ns' :
      case 'php' :
      case 'special' :
      case 'controler' :
        
        $sName = $el->getAttribute('name');
        $mResult = $window->setControler($sName);
        
      break;
      
      case 'redirect' :
        
        $mResult = $window->createCall($window->getSelf(), 'getRedirect', 'core\redirect');
        
      break;
      
  // <object name="window" call="Controler::getWindow()"/>
  // <object name="redirect" call="$oRedirect"/>
  // <object name="user" call="Controler::getUser()"/>
  // <object name="path" call="$oAction-&gt;getPath()" return="true"/>
  // <object name="path-simple" call="$oAction-&gt;getPath()-&gt;getSimplePath()" return="true"/>
  // <object name="path-action" return="true" call="$oAction-&gt;getPath()-&gt;getActionPath()"/>
  // <object name="self" call="$oAction" return="true"/>
  // <object name="directory" call="$oAction-&gt;getPath()-&gt;getDirectory()" return="true"/>
  // <object name="parent-directory" call="$oAction-&gt;getPath()-&gt;getDirectory()-&gt;getParent()" return="true"/>
  // <object name="parent" call="$oAction-&gt;getParent()"/>
  // <object name="database" call="Controler::getDatabase()"/>
      case 'document' :
        
        //if ($el->hasChildren())
        
        $mResult = $this->reflectDocument($el);
        
      break;
      
      case 'file' : 
        
        $mResult = $this->reflectFile($el);
        
      break;
        
      case 'template' :
    }
    
    return $mResult;
  }
  
  /**
   *
   * @param dom\element $el
   * @param php_objecte $obj
   * @return core\argumentable|array|null
   */
  protected function runElement(dom\element $el, php\_object $obj) {
    
    $mResult = null;
    
    //if ($el->testAttribute('return', false)) $aResult[] = $obj;
    
    foreach ($el->getChildren() as $child) {
      
      if ($child->getName() != 'call' || $child->getNamespace() != $this->getNamespace()) {
        
        $this->throwException(txt('Cannot use %s in call context', $el->asToken()));
      }
      
      $sMethod = $child->getAttribute('name');
      
      if (!$sMethod) {
        
        $this->throwException(txt('No method defined for call in %s', $child->asToken()));
      }
      
      $window = $this->getWindow();
      
      // todo arguments
      
      $call = $window->createCall($obj, $sMethod, '\sylma\storage\fs\directory', array());
      
      if ($child->hasChildren()) {
        
        $var = $call->getVar($mResult);
        $mResult = $this->runElement($child, $var);
      }
      else {
        
        $mResult = $call;
      }
    }
    
    return $mResult;
  }
  
  protected function parseAttributes(dom\element $el) {
  
  }
  
  public function asDOM() {
    
    $window = $this->getControler()->create('window', array($this->getControler()));
    $this->setWindow($window);
    
    $doc = $this->getDocument();
    $window = $this->getWindow();
    
    if ($aResult = $this->parseDocument($doc)) {
      
      $window->add($aResult);
    }
    //dspf($aResult[1]->asArgument());
    //dspf($aResult);
    
    $arg = $window->asArgument();
    
    //$tst = $arg->get('window')->query();
    //dspm((string) $tst[1]);
    
    $result = $arg->asDOM();
    $result->getRoot()->setAttribute('use-template', 'true');
    
    return $result;
  }
}
