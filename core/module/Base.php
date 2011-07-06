<?php

class ModuleBase extends Namespaced {
  
  private $oSchema = null;  
  
  // array of classe's object to use within this class with $this->create() loaded in [settings]/classes
  private $aClasses = array();
  
  private $sName = '';
  
  private $oDirectory = null;
  protected $arguments = null;
  
  protected function setDirectory($mPath) {
    
    if (is_string($mPath)) $this->oDirectory = extractDirectory($mPath);
    else $this->oDirectory = $mPath;
  }
  
  public function getDirectory() {
    
    return $this->oDirectory;
  }
  
  /**
   * Get a file object relative to the module's directory set in @method setDirectory()
   *
   * @param string $sPath The relative or absolute path to the file
   * @return storage\filesys\FileInterface|null The file corresponding to the path given, or NULL if none found
   */
  protected function getFile($sPath) {
    
    return Controler::getFile(Controler::getAbsolutePath($sPath, $this->getDirectory()));
  }
  
  /**
   * Build an object defined in @settings classes
   * 
   * @param string $sName The short name of the class
   * @param array $aArguments The arguments sent to the object on contstruction
   *
   * @return mixed The object builded
   */
  public function create($sName, array $aArguments = array()) {
    
    $result = null;
    
    if (!$this->getArguments()) {
      
      $this->throwException(txt('Cannot build object @class %s. No settings defined', $sName));
    }
    
    $class = $this->loadClass($sName, $this->getArguments('classes'));
    if ($sFile = $class->read('file', false)) $class->set('file', path_absolute($sFile, $this->getDirectory()));
    
    return Controler::createObject($class, $aArguments);
  }
  
  protected function loadClass($sName, $args) {
    
    $aPath = explode('/', $sName);
    array_unshift($aPath, null);
    
    $sPath = implode('/classes/', $aPath);
    
    if (!$class = $args->get($sPath)) {
      
      $this->throwException(txt('Cannot build object @class %s. No settings defined for these class', $sName));
    }
    
    // set absolute path for relative classe file's path
    
    if (!$sFile = $class->read('file', false)) {
      
      $sFile = $class->read('name') . '.php';
    }
    
    
    
    return $class;
  }
  
  protected function setArguments($mArguments = null, $bMerge = true) {
    
    if ($mArguments) {
      
      if (is_array($mArguments)) {
        
        if ($this->getArguments() && $bMerge) $this->getArguments()->mergeArray($mArguments);
        else $this->arguments = new XArguments($mArguments, $this->getNamespace());
      }
      else if (is_string($mArguments)) {
        
        $this->arguments = new XArguments((string) $this->getFile($mArguments));
      }
      else {
        
        if ($this->getArguments() && $bMerge) $this->getArguments()->merge($mArguments);
        else $this->arguments = $mArguments;
      }
    }
    else {
      
      $this->arguments = null;
    }
    
    return $this->getArguments();
  }
  
  protected function getArguments() {
    
    return $this->arguments;
  }
  
  protected function getArgument($sPath, $mDefault = null, $bDebug = false) {
    
    $mResult = $mDefault;
    
    if (!$this->getArguments()) $this->throwException(t('No arguments has been defined'));
    
    $mResult = $this->getArguments()->get($sPath, $bDebug);
    if ($mResult === null && $mDefault !== 'null') $mResult = $mDefault;
    
    return $mResult;
  }
  
  protected function setSchema($mSchema, $bNamespace = true, $sPrefix = '') {
    
    if (is_string($mSchema)) $mSchema = $this->getDocument($mSchema, Sylma::MODE_EXECUTE);
    
    if ($mSchema && !$mSchema->isEmpty()) { // !$this->getNamespace() && TODO REM
      
      if ($sNamespace = $mSchema->getAttribute('targetNamespace')) {
        
        if (!$sPrefix) $sPrefix = $this->getPrefix();
        $this->setNamespace($sNamespace, $sPrefix, $bNamespace);
      }
      
      $this->oSchema = $mSchema;
    }
  }
  
  protected function getSchema() {
    
    return $this->oSchema;
  }
  
  /**
   * Throw a customized exception to the main controler
   * 
   * @param string $sMessage The message describing the exception
   * @param array|string $mSender A list of keys or a single key describing the previous classes throwing this exception
   * @param integer $iOffset The number of calls before final sent to main controler. This will be used to localize the call in backtrace
   */
  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {
    
    $mSender = (array) $mSender;
    $mSender[] = '@namespace ' . $this->getNamespace();
    
    Sylma::throwException($sMessage, $mSender, $iOffset);
  }
  
  /**
   * Escape a string for secured queries to module's related storage system
   * <code>
   * list($spUser, $spPassword) = $this->escape(array($sUser, sha1($sPassword)));
   * </code>
   * 
   * @param string|array A single or a list of values to escape
   * @return string|array An escaped string or array of strings
   */
  
  protected function escape() {
    
    if (func_num_args() == 1) return addQuote(func_get_arg(0));
    else return addQuote(func_get_args());
  }
  
  /**
   * Log a message
   * @param mixed|DOMNode|string|array $mMessage The message to send, will be parsed or stringed
   * @param string $sStatut The statut of the message : see @file /system/allowed-messages.xml for more infos
   */
  protected function log($mMessage, $sStatut = Sylma::LOG_STATUT_DEFAULT) {
    
    return Sylma::log($this->getNamespace(), $mMessage, $sStatut);
  }
  
  /**
   * Alias of log for ascendent compatibility
   */
  protected function dspm($mMessage, $sStatut = Sylma::LOG_STATUT_DEFAULT) {
    
    $oPath = new HTML_Div(xt('Module %s -&gt; %s', view($this->getNamespace()), new HTML_Strong($this->getDirectory())),
      array('style' => 'font-weight: bold; padding: 5px 0 5px;'));
    return dspm(array($oPath, $mMessage, new HTML_Tag('hr')), $sStatut);
  }
}


