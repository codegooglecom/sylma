<?php

namespace sylma\modules\formater;
use sylma\core, sylma\dom;

class Controler extends core\module\Domed {

  const NS = 'http://www.sylma.org/modules/formater';

  public function __construct() {

    $this->setDirectory(__file__);
    $this->setNamespace(self::NS);
    $this->loadDefaultArguments();
  }

  protected function loadArray(array $aVal, $bDeep = true) {

    $aItems = array();

    foreach ($aVal as $mKey => $mVal) {

      if($bDeep) $mVal = $this->loadVar($mVal);
      $mKey = $this->loadVar($mKey);

      $aItems[] = array(
        'key' => $mKey,
        'value' => $mVal,
      );
    }

    return array('array' => array(
      '#item' => $aItems,
    ));
  }

  /**
   *
   * @param \sylma\dom\node $val
   * @return array
   */
  protected function loadObjectElement($val, $mContent) {

    $aResult = array('object' => array(
      '@class' => get_class($val),
      $mContent,
    ));

    if ($val instanceof core\argument) {

      $aResult['object']['note'] = "namespace : {$val->getNamespace()}";
    }

    return $aResult;
  }

  protected function replaceLineBreak($sContent) {

      $result = $this->createDocument('<pre xmlns="http://www.w3.org/1999/xhtml">' . htmlspecialchars($sContent) . '</pre>');

    return $result;
  }

  protected function loadObject($val) {

    $result = null;

    if ($val instanceof core\argument) {

      $aResult = $val instanceof dom\argument\Basic ? $val->asArray() : $val->query();
      $result = is_array($aResult) ? $this->loadArray($aResult) : $this->loadVar($aResult);
    }
    else if ($val instanceof dom\handler) {

      if ($val->getRoot(false)) {

        if (\Sylma::read('debug/html/show')) {

            $result = $this->replaceLineBreak($val->asString(dom\handler::STRING_INDENT));
        }
        else {

          $result = '[]';
        }
      }
      else {

        $result = '[EMPTY]';
      }

    }
    else if ($val instanceof dom\node) {

      if ($val->getDocument()) {

        if (\Sylma::read('debug/html/show')) {

          $result = $this->replaceLineBreak($val->asString(dom\handler::STRING_INDENT));
        }
        else {

          $result = '[]';
        }
      }
      else {

        $result = '[Lost DOM Element]';
      }
    }
    else if ($val instanceof dom\collection) {

      $result = $this->loadDOMCollection($val);
    }
    else if ($val instanceof core\tokenable) {

      $result = $val->asToken();
    }

    /*
    else if ($val instanceof core\argumentable) {

      $arg = $val->asArgument();
      $result = $this->loadArgument($arg);
    }
    else if ($val instanceof dom\domable) {

      if ($val instanceof parser\action\cached ||
        $val instanceof parser\action) {

        $result = '[Action]';
      }
      else {

        $result = $val->asDOM();
      }

    }*/

    return $this->loadObjectElement($val, $result);
  }

  protected function loadDOMCollection(dom\collection $collection) {

    $aResult = array();

    foreach ($collection as $node) {

      $aResult[] = $this->loadObject($node);
    }

    return $this->loadArray($aResult, false);
  }

  protected function loadArgument(core\argument $arg) {

    return $this->loadObject($arg);
  }

  protected function loadString($sVal) {

    return array('string' => mb_check_encoding($sVal) ? $sVal : '[invalid utf-8]');
  }

  protected function loadNumeric($iVal) {

    return array('numeric' => $iVal);
  }

  protected function loadBoolean($bVar) {

    $content = $bVar ? '[TRUE]' : '[FALSE]';

    return array('boolean' => $content);
  }

  protected function loadVar($mVar) {

    $aResult = array();

    if (is_array($mVar)) $aResult = $this->loadArray ($mVar);
    else if (is_object($mVar)) $aResult = $this->loadObject ($mVar);
    else if (is_string($mVar)) $aResult = $this->loadString($mVar);
    else if (is_numeric($mVar)) $aResult = $this->loadNumeric($mVar);
    else if (is_bool($mVar)) $aResult = $this->loadBoolean($mVar);
    else if (is_null($mVar)) $aResult = $aResult = array('null' => array());
    else {

      $aResult = array('unknown' => array('@type' => gettype($mVar)));
    }

    return $aResult;
    //else if ()
  }

  public function errorAsHTML(array $aError) {

    $sFile = array_key_exists('file', $aError) ? $aError['file'] : '-unknown-';
    $sLine = array_key_exists('line', $aError) ? $aError['line'] : '-unknown-';
    $sClass = array_key_exists('class', $aError) ? $aError['class'] : '-unknown-';
    $sFunction = array_key_exists('function', $aError) ? $aError['function'] : '-unknown-';

    return "<a href=\"netbeans://$sFile:$sLine\">$sFile [$sLine] - $sClass->$sFunction()</a><br/>";
  }

  public function asHTML($mVal) {

    $result = null;
    $aResult = array('window' => $this->loadVar($mVal));

    if ($arg = $this->createArgument($aResult)) {

      if ($doc = $arg->asDOM()) {

        $template = $this->getTemplate('default.xsl');
        $result = $template->parseDocument($doc);
      }
    }

    return $result;
  }

  public function asToken($mVal) {

    $sResult = '[unknown]';

    if (is_string($mVal)) {

      $sResult = '[string = ' . $this->limitString($mVal) . ']';
    }
    else if (is_object($mVal)) {

      if ($mVal instanceof core\tokenable) {

        $sResult = $mVal->asToken();
      }
      else {

        $sResult = '[object, @class = ' . get_class($mVal) . ']';
      }
    }
    else if (is_array($mVal)) {

      $sResult = '[array, ' . '@length ' . count($mVal) . ']';
    }
    else if (is_null($mVal)) {

      $sResult = '[null] ';
    }
    else if (is_numeric($mVal)) {

      $sResult = '[numeric = ' . $mVal . ']';
    }
    else if (is_bool($mVal)) {

      $sResult = '[boolean = ' . ($mVal ? 'TRUE' : 'FALSE') . ']';
    }
    else {

      $sResult = '[' . gettype($mVal) . ']';
    }

    return '@var ' . htmlentities($sResult);
  }

  public function limitString($mValue, $iLength = 50, $bXML = false) {

    $sValue = (string) $mValue;

    if (strlen($sValue) > $iLength) $sValue = substr($sValue, 0, $iLength).'...';

    if ($bXML) {

      $iLastSQuote = strrpos($sValue, '&');
      $iLastEQuote = strrpos($sValue, ';');

      if (($iLastSQuote) && ($iLastEQuote < $iLastSQuote)) $sValue = substr($sValue, 0, $iLastSQuote).'...';
    }

    return $sValue;
  }

  public function getBacktrace() {

    foreach (debug_backtrace() as $aLine) {

      echo $aLine['file'].' - '.$aLine['class'].'::'.$aLine['function'] . ' - ' . $aLine['line'] . '<br/>';
    }
  }
}