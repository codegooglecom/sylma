<?php

namespace sylma\core\exception;
use sylma\core, sylma\modules;

require_once('core/exception.php');
require_once('core/functions/Path.php');

class Basic extends \Exception implements core\exception {

  const NS_MESSAGE = 'http://www.sylma.org/core/message';
  /**
   * Number of exceptions created during the script
   * @var integer
   */
  protected static $iCount = 0;

  protected $aPath = array();
  protected $aCall = array();
  protected static $bThrowError = true;
  protected $aVariables = array();

  /**
   * Allow import of other classes, used class is showed in message
   */
  protected $iOffset = 1;

  public function __construct($message, $code = 0, $previous = null) {
//echo round(microtime(true), 2);
//echo $message;
//echo '<br/>';
    self::$iCount++;

    parent::__construct($message, $code, $previous);
  }

  public static function getCount() {

    return self::$iCount;
  }

  public function load($iOffset = 1, array $aTrace = array()) {

    // for exceptions : line 1, file 2, class/method 2

    //if ($aTrace) dspm(\Controler::getBacktrace($aTrace));
    $aTrace = $aTrace ? $aTrace : $this->getTrace();

    if (count($aTrace) < $iOffset + 2) {

      echo 'bad exception call'; // TODO
    }
    else {

      $aCall = $aTrace[$iOffset];
      $aCaller = $aTrace[$iOffset + 1];

      $this->aCall = array(
        'type' => array_key_exists('class', $aCaller) ? 'method' : 'function',
        'value' => self::loadKey('class', $aCaller) . self::loadKey('type', $aCaller) . self::loadKey('function', $aCaller));

      if (array_key_exists('line', $aCall)) $this->line = $aCall['line'];
      if (array_key_exists('file', $aCall)) $this->file = $aCall['file'];
    }

    //$this->save();
  }

  protected static function loadKey($sKey, array $aArray) {

    return array_key_exists($sKey, $aArray) ? $aArray[$sKey] : null;
  }

  public function addPath($sValue) {

    $this->aPath[] = $sValue;
  }

  public function setPath(array $aPath) {

    $this->aPath = $aPath;
  }

  protected function parsePath($aPath) {

    $aResult = array();

    foreach ($aPath as $mPath) {

      if (is_array($mPath)) $aResult[] = $this->parsePath($mPath);
      else $aResult[] = $this->parseString($mPath);
    }

    return $aResult;
  }

  protected function parseString($sValue) {

    $aResult = array();

    if ($fs = \Sylma::getManager('fs', false, false)) {

      if (preg_match_all('/@file [^\s]*/', $sValue, $aMatch, PREG_OFFSET_CAPTURE)) {

        $sFile = substr($aMatch[0][0][0], 6);
        $iFile = $aMatch[0][0][1];
        $iLength = strlen($sFile) + 6;

        $aResult[] = substr($sValue, 0, $iFile) . ' ';
        $aResult[] = '@file';

        $sDirectory = $fs->getDirectory()->getSystemPath();

        $aResult[] = '<a href="netbeans://' . $sDirectory . $sFile.'">' . $sFile . '</a>';
        $aResult[] = substr($sValue, $iFile + $iLength);
      }
      else {

        $aResult[] = $sValue;
      }
    }
    else {

      $aResult[] = $sValue;
    }

    return implode(' ', $aResult);
  }

  protected function getPath() {

    $sCall = self::loadKey('value', $this->aCall, 'unknown');

    //$link = new \HTML_A('netbeans://' . $this->getFile() . ':' . $this->getLine(), $sCall . '()');
    $link = '<a href="netbeans://' . $this->getFile() . ':' . $this->getLine() . '">' . $sCall . '()' . '</a>';
    //$message = $this->parseString($this->getMessage());
    $path = $this->parsePath($this->aPath);

    return array($link, $path);
  }

  public static function loadError($iNo, $sMessage, $sFile, $iLine, $aContext) {

    //if ($iNo & \Sylma::read('users/root/error-level')) {

      $exception = new \Sylma::$sExceptionClass($sMessage);
      $exception->importError($iNo, $sMessage, $sFile, $iLine);
      $exception->setVariables($aContext);

      if (self::throwError()) {

        throw $exception;
      }
    //}

  }

  /**
   * If set to TRUE, errors will be thrown as exceptions, else errors will only be logged and displayed to admin
   */
  public static function throwError($bThrow = null) {

    if ($bThrow !== null) self::$bThrowError = $bThrow;
    return self::$bThrowError;
  }

  public function loadException(\Exception $e) {

    $this->code = $e->getCode();
    $this->message = $e->getMessage();
    $this->file = $e->getFile();
    $this->line = $e->getLine();
    $this->sClass = get_class($e);

    $this->load(0, $e->getTrace());
  }

  public function importError($iNo, $sMessage, $sFile, $iLine) {

    $this->code = $iNo;
    //$this->message = checkEncoding($sMessage);
    $this->message = $sMessage;

    // for error : line def, file def, class/method 1

    $this->file = $sFile;
    $this->line = $iLine;

    $aTrace = $this->getTrace();

    if (count($aTrace) < 2) {

      // echo 'bad exception call'; // TODO
    }
    else {

      $aCall = $aTrace[1];

      $this->aCall = array(
        'type' => isset($aCall['class']) ? 'method' : 'function',
        'value' => isset($aCall['class']) ?
          $aCall['class'] . $aCall['type'] . $aCall['function'] :
          $aCall['function']);

    }

    //$this->save();
  }

  protected function strongLast($sValue, $sSep = '\\') {

    $sResult = $sValue;
    $iLast = strrpos($sValue, $sSep);

    if ($iLast !== false) {

      $sResult = substr($sValue, 0, $iLast + 1) . '<strong>' . substr($sValue, $iLast + 1) . '</strong>';
    }

    return $sResult;
  }

  public function errorAsHTML($iIndex, array $aError, $bHTML) {

    $sFile = array_key_exists('file', $aError) ? $aError['file'] : '-unknown-';
    $sLine = array_key_exists('line', $aError) ? $aError['line'] : '-unknown-';
    $sClass = array_key_exists('class', $aError) ? $aError['class'] : '-unknown-';
    $sFunction = array_key_exists('function', $aError) ? $aError['function'] : '-unknown-';

    $sDisplay = $this->strongLast(substr($sFile, strlen(\Sylma::PATH_SYSTEM)), '/');
    $sClass = $this->strongLast($sClass);

    $bHTML = $bHTML ? \Sylma::read('debug/backtrace/html') : false;
    $bArgs = \Sylma::read('debug/backtrace/arguments');

    if ($bArgs && array_key_exists('args', $aError)) { // TODO

      $sFunctionArgs = count($aError['args']);

      $sArgs = '<ul>';

      foreach ($aError['args'] as $arg) {

        $sArgs .= '<li>' . \Sylma::show($arg, !$bHTML) . '</li>';
      }

      $sArgs .= '</ul>';
    }
    else {

      $sArgs = '';
      $sFunctionArgs = 0;
    }

    return "<li tabindex=\"1\"><a href=\"netbeans://$sFile:$sLine\">$iIndex. $sDisplay [$sLine]</a> <span>$sClass-></span><span class=\"sylma-function\">$sFunction($sFunctionArgs)</span>$sArgs</li>";
  }

  public function setVariables($aVars) {

    $this->aVariables = $aVars;
  }

  protected function getVariables() {

    return $this->aVariables;
  }

  protected function loadVariables($bHTML = true) {

    $sResult = '';

    if ($this->getVariables()) {

      $sResult .= '<ul class="sylma-variables" tabindex="1">';
      $sResult .= '<h3>Variables</h3>';

      foreach ($this->getVariables() as $sName => $mVar) {

        $sResult .= '<li><strong>' . $sName . '</strong> :' . \Sylma::show($mVar, !$bHTML) . '</li>';
      }

      $sResult .= '</ul>';
    }

    return $sResult;
  }

  protected function loadTraces($bHTML = true) {

    $sResult = '';

    if (\Sylma::read('debug/backtrace/show')) {

      $aTraces = $this->getTrace();

      $sResult .= '<ul class="sylma-backtrace">';

      foreach ($aTraces as $iTrace => $aTrace) {

        $sResult .= $this->errorAsHTML($iTrace, $aTrace, $bHTML);
      }

      $sResult .= '</ul>';
    }

    return $sResult;
  }

  public function save($bPrint = true, $bHTML = true, $bTrace = true) {

    $sResult = '';

    $aPath = $this->getPath();

    $aResult['message'] = $this->parseString( htmlspecialchars($this->getMessage())) . '<br/>';
    $aResult['paths'] = $this->readPaths($aPath);

    $aResult['vars'] = $this->loadVariables($bHTML);

    if ($bTrace) {

      $aResult['trace'] = $this->loadTraces($bHTML);
    }

    if (\Sylma::read('exception/show')) {

      $this->show($aResult, $bPrint);
    }

    if (\Sylma::isAdmin()) {

      if (\Sylma::read('exception/send')) {

        $this->send($aResult);
      }
    }
    else {

      $this->send($aResult);
    }

    return $sResult;
  }

  protected function show(array $aResult, $bPrint) {

    $sResult = implode('', $aResult);

    if ($bPrint) {

      echo $sResult;
    }
    else {

      \Sylma::display($sResult);
    }
  }

  protected function send(array $aResult) {

    try {

      if (!$this->insert($aResult)) {

        $this->mail($aResult);
      }

    }
    catch (core\exception $e) {

      $this->mail($aResult);
    }
  }

  protected function insert($aContent) {

    $parser = \Sylma::getManager('parser');
    $fs = \Sylma::getManager('fs');

    $file = $fs->extractDirectory(__FILE__)->getFile('insert.vml');

    return $parser->load($file, array(
      'post' => new \sylma\core\argument\Readable(array(
        'message' => $this->getMessage(),
        'message_html' => $aContent['message'],
        'context' => $aContent['paths'] . $aContent['vars'],
        'backtrace' => $aContent['trace'],
        'session' => $this->dump($_SESSION),
        'request' => $this->dump($_REQUEST),
        'files' => $this->dump($_FILES),
        'server' => $this->dump($_SERVER),
        //'browser' => $this->dump(get_browser(null, true)),
      )),
      //'contexts' => array('messages' => $parser->getContext('messages')),
    ), false);
  }

  protected function dump($var) {

    ob_start();
    var_dump($var);

    return $this->stripInvalidXml(ob_get_clean());
  }

  protected function mail(array $aResult) {

    $sResult = implode('', $aResult);

    $now = new \DateTime();
    $sError = $now->format('Y-m-d::H:m:s') . ' - ' . $this->getMessage();

    if (\Sylma::read('exception/mail/enable')) {

      try {

        $mailer = new modules\mailer\Mailer;

        if (!$mailer->send('Website', \Sylma::read('exception/mail/to'), 'Exception uncatch : ' . $sError, $sResult, true)) {

          $this->logFile($sError);
        }
      }
      catch (core\exception $e) {

        $this->logFile($sError);
      }
    }
    else {

      $this->logFile($sError);
    }
  }

  protected function logFile($sValue) {

    try {

      $sPath = $_SERVER['DOCUMENT_ROOT'] . '/cache/' . \Sylma::read('exception/file');

      if (!file_put_contents($sPath, $sValue, \FILE_APPEND)) {

        // :(
      }
    }
    catch (core\exception $e) {

      // :(
    }
  }

  protected function readPaths(array $aPaths) {

    $sResult = '';

    foreach ($aPaths as $mPath) {

      if (is_array($mPath)) {

        $sResult .= $this->readPaths($mPath);
      }
      else if (trim($mPath)) {

        $sResult .= '<li>' .  $mPath . '</li>';
      }
    }

    return '<ul>' . $sResult .'</ul>';
  }

  protected function stripInvalidXml($value) {

    // http://stackoverflow.com/questions/3466035/how-to-skip-invalid-characters-in-xml-file-using-php

    $ret = "";
    $current;

    if (empty($value))
    {
        return $ret;
    }

    $length = strlen($value);
    for ($i=0; $i < $length; $i++)
    {
        $current = ord($value{$i});
        if (($current == 0x9) ||
            ($current == 0xA) ||
            ($current == 0xD) ||
            (($current >= 0x20) && ($current <= 0xD7FF)) ||
            (($current >= 0xE000) && ($current <= 0xFFFD)) ||
            (($current >= 0x10000) && ($current <= 0x10FFFF)))
        {
            $ret .= chr($current);
        }
        else
        {
            $ret .= " ";
        }
    }

    return $ret;
  }

  protected function implodePath($aPath, $sSeparator = '') {

    $sResult = '';

    foreach ($aPath as $mKey => $mValue) {

      if (is_array($mValue)) $sValue = $sSeparator . $this->implodePath($mValue);
      else $sValue = $mValue;

      if (is_string($mKey)) $sKey = $mKey . ' ';
      else $sKey = '';

      $sResult .= $sKey . $sValue;
    }

    return $sResult;
  }

  /**
   * Associate properties of exceptions into a path with tokens
   */
  public function __toString() {

    return  $this->implodePath($this->getPath()) . ' @message ' . $this->getMessage();
  }
}

