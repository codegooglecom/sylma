<?php

namespace sylma\parser\action;
use \sylma\core, sylma\parser, sylma\dom, sylma\storage\fs;

class Manager extends parser\compiler\Builder {

  const FS_EDITABLE = 'fs/editable';

  /**
   * Indent action's result builded with @method buildAction(), must be set to FALSE in production
   */
  const FORMAT_ACTION = false;

  const PHP_TEMPLATE = 'compiler/php.xsl';
  const DOM_TEMPLATE = 'compiler/template.xsl';

  public function __construct() {

    $this->setDirectory(__file__);
    $this->setNamespace(parser\action::NS);

    $this->loadDefaultArguments();
    $this->setArguments('controler.yml');
  }

  public function runAction($sPath, array $aArguments = array()) {

    $action = $this->getAction($sPath, $aArguments);
    return $action->asDOM();
  }

  public function getAction($sPath, array $aArguments = array(), fs\directory $dir = null) {

    require_once('core/functions/Path.php');

    $path = $this->create('path', array(core\functions\path\toAbsolute($sPath, $dir)));
    $fs = \Sylma::getControler('fs');
    //$file = $fs->getFile($sPath, true, );

    return $this->loadAction($path->getFile());
  }

  public function buildAction(dom\handler $doc, array $aArguments = array(), fs\editable\directory $dir = null, fs\directory $base = null, $sName = '') {

    if (!$dir) {

      $fs = $this->getControler(self::FS_EDITABLE);

      $user = $this->getControler('user');
      $tmp = $fs->getDirectory((string) $user->getDirectory('#tmp'));

      $dir = $tmp->createDirectory();
    }

    if ($sName) $file = $dir->createFile($sName . '.eml');
    else $file = $dir->createFile('eml', true);

    $doc->saveFile($file, self::FORMAT_ACTION);

    return $this->loadAction($file, $aArguments, $base);
  }

  protected function loadAction(fs\file $file, array $aArguments = array(), $base = null) {

    $result = $this->create('action', array($file, $aArguments, $base));

    if ($parent = $this->getControler('parser')->getContext('action/current')) {

      $result->setParentParser($parent);
      $result->setContexts($parent->getContexts());
    }

    return $result;
  }

  public function createAction(fs\file $file, array $aArguments = array(), array $aContexts = array(), $dir = null) {

    $dir = $dir ? $dir : $file->getParent();

    $result = $this->load($file, array($aArguments, $aContexts, $dir));

    return $result;
  }

  /**
   * Made public to allow use of an handler
   */
  public function load(fs\file $file, array $aArguments = array()) {

    return parent::load($file, $aArguments);
  }

  protected function createCache(fs\file $file, array $aArguments = array()) {

    array_unshift($aArguments, $file);
    return $this->create('cached', $aArguments);
  }

  public function build(fs\file $file, fs\directory $base) {

    $this->setDirectory(__FILE__);
    $doc = $file->getDocument(array(), \Sylma::MODE_EXECUTE);

    $reflector = $this->createReflector($doc, $base);
    $window = $this->runReflector($reflector, $this->getClass($doc), $file);

    $result = $this->buildFiles($window, $file);

    return $result;
  }

  protected function buildFiles(dom\handler $window, fs\file $file) {

    if ($this->readArgument('debug/show')) {

      $tmp = $this->create('document', array($window));

      echo '<pre>' . $file->asToken() . '</pre>';
      echo '<pre>' . str_replace(array('<', '>'), array('&lt;', '&gt'), $tmp->asString(true)) . '</pre>';
    }

    $tpl = $this->getCachedFile($file, '.tpl.php');

    $template = $this->getTemplate(static::PHP_TEMPLATE);

    $template->setParameters(array(
      'template' => $tpl->getRealPath(),
    ));

    $result = $this->getCachedFile($file);

    $sContent = $template->parseDocument($window, false);
    $result->saveText($sContent);

    if ($window->getRoot()->testAttribute('use-template')) {

      //if ($sContent = $this->getTemplate(self::DOM_TEMPLATE)->parseDocument($window, false)) {
      if ($doc = $this->getTemplate(static::DOM_TEMPLATE)->parseDocument($window)) {

        $sContent = '';

        foreach ($doc->getChildren() as $child) {

          if ($child->getType() == dom\node::ELEMENT) {

            $iString = $this->readArgument('template/indent') ? dom\handler::STRING_INDENT : 0;

            $tmp = $this->createDocument($child);
            $sContent .= $tmp->asString($iString);
          }
          else {

            $sContent .= $child->asString();
          }

        }

        $sContent = $this->parseAttributes($sContent);
        $tpl->saveText($sContent);
      }
    }

    return $result;
  }

  public function buildInto(fs\file $file, fs\directory $base, common\_window $window) {

    $reflector = $this->createReflector($file, $base);
    $reflector->setWindow($window);

    try {

      $reflector->build($window);
    }
    catch (core\exception $e) {

      $e->addPath($file->asToken());
      throw $e;
    }
  }
  
  protected function createReflector(dom\document $doc, fs\directory $base) {

    $result = $this->create('reflector', array($this, $doc, $base));

    return $result;
  }

  protected function parseAttributes($sContent) {

    $sContent = preg_replace('/\[sylma:insert:(\d+)\]/', '<?php echo $aArguments[$1]; ?>', $sContent);

    return $sContent;
  }

  public function createContext() {

    return $this->create('context');
  }

  public function loadTemplate($sTemplate, $iKey, array $aArguments) {

    $sResult = $this->includeTemplate($sTemplate, $iKey, $aArguments);

    $doc = $this->getControler('dom')->createDocument();
    $doc->setContent($sResult);

    return $doc;
  }

  protected function includeTemplate($sTemplate, $iTemplate, array $aArguments) {

    ob_start();

    include($sTemplate);
    $sResult = ob_get_clean();;

    //ob_end_clean();

    return $sResult;
  }

  public function validateString($sVal) {

    if (!is_string($sVal)) {

      $this->throwException(sprintf('Invalid argument type : string expected, %s given', $this->show($sVal)));
    }

    return $sVal;
  }

  public function validateArgument($sName, $mVar, $mVal, $bRequired = true, $bReturn = false, $bDefault = false) {

    $mResult = null;

    if ($bRequired && (is_null($mVal) || $mVal === false)) {

      if ($bDefault) $mResult = null;
      else $this->throwException(sprintf('Validation failed for argument %s', $sName));
    }

    if (!$bDefault) {

      if ($bReturn) $mResult = $mVal;
      else $mResult = $mVar;
    }

    return $mResult;
  }

  public function validateNumeric($iVal) {

    if (!is_numeric($iVal)) {

      $this->throwException(sprintf('Invalid argument type : numeric expected, %s given', $this->show($iVal)));
    }

    return $iVal + 0;
  }

  public function validateArray($aVal) {

    if (!is_array($aVal)) {

      $this->throwException(sprintf('Invalid argument type : array expected, %s given', $this->show($aVal)));
    }

    return $aVal;
  }

  public function validateObject($val, $sInterface) {

    if (!$val instanceof $sInterface) {

      $formater = \Sylma::getControler('formater');
      $this->throwException(sprintf('Invalid argument type : object %s expected, %s given', $sInterface, $formater->asToken($val)));
    }

    return $val;
  }

  public function loadStringable(core\stringable $val, $iMode = 0) {

    return $val->asString($iMode);
  }

  public function loadArgumentable(core\argumentable $val = null) {

    if (!$val) return null;

    $arg = $val->asArgument();

    return $this->loadDomable($arg);
  }

  public function loadDomable(dom\domable $val) {

    $dom = $val->asDOM();

    if (!is_null($dom) && !$dom instanceof dom\node) {

      $this->throwException(sprintf('Bad type return %s when DOM expected', $this->show($dom)));
    }

    return $dom;
  }
}