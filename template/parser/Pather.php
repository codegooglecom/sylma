<?php

namespace sylma\template\parser;
use sylma\core, sylma\template as template_ns;

class Pather extends component\Child {

  const ALL_TOKEN = '*';

  protected $source;
  protected $aOperators = array('<', '>', '=', '<=', '>=', '!=', 'and', 'or', '+', '*', '/', 'in', '?', ':', '%');

  public function setSource(template_ns\parser\tree $source) {

    if (!is_object($source)) {

      $this->launchException('SET : Bad source', get_defined_vars());
    }

    $this->source = $source;
  }

  /**
   *
   * @return template_ns\parser\tree
   */
  protected function getSource() {

    if (!$this->source) {

      $this->launchException('No source defined');
    }

    return $this->source;
  }

  protected function getOperators() {

    return $this->aOperators;
  }

  public function parseExpression($sPath, $bStatic = false) {

    $aResult = array();

    $aTokens = explode(' ', $sPath);
    $window = $this->getWindow();

    $bOp = false; // alternate between op and val
    $bIN = false;

    foreach ($aTokens as $sToken) {

      if ($bOp) {

        $result = $this->parseExpressionOp($sToken, $bIN);

        if (!is_null($result) && !$bIN) {

          $aResult[] = $result;
        }

        $bOp = false;
      }
      else {

        $result = $this->parseExpressionValue($sToken, $bStatic);

        if ($bIN) {

          $needle = $window->extractValue(array_pop($aResult));
          $haystack = $window->extractValue($result);
          $aResult[] = $window->callFunction('in_array', 'php-boolean', array($needle, $haystack));
        }
        else {

          $aResult[] = $result;
        }

        $bIN = false;
        $bOp = true;
      }
    }

    return $window->flattenArray($aResult);
  }

  protected function parseExpressionOp($sToken, &$bIN) {

    if (!in_array($sToken, $this->getOperators())) {

      $this->launchException("Unknown operator : {$sToken}");
    }

    $result = null;
    $window = $this->getWindow();

    if ($sToken == 'in') {

      $bIN = true;
    }
    else {

      if ($sToken == '=') {

        $sToken = '==';
      }

      $result = $window->createOperator($sToken);
    }

    return $result;
  }

  protected function parseExpressionValue($sToken, $bStatic) {

    $bNot = false;

    $window = $this->getWindow();

    if ($sToken{0} == '!') {

      $bNot = true;
      $sToken = (trim(substr($sToken, 1)));
    }

    if ($sValue = $this->matchString($sToken) or !is_null($sValue)) {

      if ($bStatic) {

        $result = $this->getTemplate()->parseValue($sValue);
      }
      else {

        $result = $window->createString($sValue);
      }
    }
    else if ($sValue = $this->matchNumeric($sToken) or !is_null($sValue)) {

      if ($bStatic) {

        $result = $sValue;
      }
      else {

        $result = $window->createNumeric($sToken);
      }
    }
    else if ($sToken) {

      $result = $this->readPath($sToken, '');
    }
    else {

      $result = null;
    }

    if ($bNot) {

      $result = $window->createNot($result);
    }

    return $result;
  }

  protected function matchExpression($sValue) {

    return $sValue && $sValue{0} == '(' ? substr($sValue, 1, -1) : null;
  }

  protected function matchString($sValue) {

    return $sValue && $sValue{0} == "'" ? mb_substr($sValue, 1, -1) : null;
  }

  protected function matchNumeric($sValue) {

    return is_numeric($sValue) ? $sValue : null;
  }

  /**
   * Parse then apply path to tokens at level 0.
   * Can find : all (with exclusion), expression, variable, string, numeric, function, default
   *
   * @param type $sPath
   * @param type $sMode
   * @param array $aArguments
   * @return array|\sylma\parser\languages\common\argumentable
   * Multiple tokens level 1 (with comma ex: id,name) return array.
   * Single token level 1 (without comma) return object or array.
   *
   * @usedby template\parser\component\Argument::getDefault()
   * @usedby template\parser\component\Apply::buildDefault()
   * @usedby template\parser\component\Read::build()
   */
  public function applyPath($sPath, $sMode, array $aArguments = array()) {

    if ($sPath) {

      $sPath = trim($sPath);

      if ($this->matchAll($sPath)) {

        $mResult = $this->parsePathAll($sPath, $sMode, $aArguments);
      }
      else {

        $aPath = $this->parsePaths($sPath);

        if (count($aPath) === 1) {

          $aPath = $this->parsePath(current($aPath));
          $sPath = array_shift($aPath);

          $mResult = $this->parsePathTokenValue($sPath, $aPath, $sMode, false, $aArguments);
        }
        else {

          $mResult = $this->parsePathTokens($aPath, $sMode, false, $aArguments);
        }
      }
    }
    else {

      $mResult = $this->getSource()->reflectApply($sMode, $aArguments);
    }

    return $mResult;
  }

  public function readPath($sPath, $sMode, array $aArguments = array()) {

    if ($sPath) {

      $sPath = trim($sPath);
      $aResult = $this->parsePathTokens($this->parsePaths($sPath), $sMode, true, $aArguments);
    }
    else {

      $aResult = $this->getSource()->reflectRead($aArguments);
    }

    return $aResult;
  }

  /**
   * Parse and trim token expressions (using "()")
   * @param type $sPath
   * @return array
   */
  protected function parsePaths($sPath) {

    $aResult = array();

    if ($sPath{0} == "'") {

      $aResult[] = $sPath;
    }
    else {

      $sLast = '';
      $bSub = 0;

      foreach (str_split($sPath) as $sChar) {

        if ($sChar === ',' && !$bSub) {

          $aResult[] = $sLast;
          $sLast = '';
        }
        else {

          if ($sChar === '(') {

            $bSub++;
          }
          else if ($sChar === ')') {

            $bSub--;
          }

          $sLast .= $sChar;
        }
      }

      $aResult[] = $sLast;
      $aResult = array_map('trim', $aResult);
    }

    return $aResult;
  }

  /**
   * Parse token at level 1 (using "/")
   * @param type $sPath
   * @return type
   */
  protected function parsePath($sPath) {

    if ($sPath{0} == "'") {

      $aResult = array($sPath);
    }
    else {

      $aResult = explode('/', $sPath);
    }

    return $aResult;
  }

  /**
   * Apply path to all tokens at level 1 (using ",")
   *
   * @param array $aPaths List of token
   * @return array
   */
  public function parsePathTokens(array $aPaths, $sMode, $bRead = false, array $aArguments = array()) {

    $aResult = array();

    foreach ($aPaths as $sPath) {

      $aResult[] = $this->parsePathToken($this->parsePath($sPath), $sMode, $bRead, $aArguments);
    }

    return $aResult;
  }

  /**
   * Apply path to all tokens at level 2 (using "/")
   * @param array $aPath
   * @param type $sMode
   * @param type $bRead
   * @param array $aArguments
   * @return type
   */
  public function parsePathToken(array $aPath, $sMode, $bRead, array $aArguments = array()) {

    if ($aPath) {

      $result = $this->parsePathTokenValue(array_shift($aPath), $aPath, $sMode, $bRead, $aArguments);
    }
    else {

      $el = $this->getSource();
      $result = $bRead ? $el->reflectRead($sMode) : $el->reflectApply($sMode, $aArguments);
    }

    return $result;
  }

  /**
   * Apply single token, can find : variable, expression, string, numeric, function, default (key)
   */
  protected function parsePathTokenValue($sPath, array $aPath, $sMode, $bRead, array $aArguments = array()) {

    if ($aMatch = $this->matchVariable($sPath)) {

      $mResult = $this->parseVariable($aMatch, $aPath, $sMode);
    }
    else if ($sValue = $this->matchExpression($sPath)) {

      if ($aPath) {

        $this->launchException('Expression must not contains sub path');
      }

      $mResult = array($this->getWindow()->createExpression($this->parseExpression($sValue)));
    }
    else if ($sValue = $this->matchString($sPath) or !is_null($sValue)) {

      $mResult = $this->getParser()->xmlize($this->getTemplate()->parseValue($sValue));
    }
    else if ($aMatch = $this->matchFunction($sPath)) {

      $mResult = $this->parsePathFunction($aMatch, $aPath, $sMode, $bRead, $aArguments);
    }
    else if ($sPath) {

      $mResult = $this->parsePathDefault($sPath, $aPath, $sMode, $bRead, $aArguments);
    }
    else {

      $mResult = $this->parsePathRoot($aPath, $sMode, $bRead, $aArguments);
      //$this->launchException('Invalid path token', get_defined_vars());
    }

    return $mResult;
  }

  protected function parsePathDefault($sPath, array $aPath, $sMode, $bRead, array $aArguments = array()) {

    $this->launchException('No default action defined');
  }

  protected function matchAll($sVal) {

    return $sVal{0} === self::ALL_TOKEN;
  }

  protected function matchVariable($sVal) {

    preg_match('/^\$(\$?[\w-]+)$/', $sVal, $aResult);

    return $aResult;
  }

  protected function matchFunction($sVal) {

    //preg_match('/^([\w-]+)\\(/', $sVal, $aResult);
    preg_match('/^([\w-]+)\\((.*)\\)$/', $sVal, $aResult);

    return $aResult;
  }

  public function parseArguments($sArguments, $sMode = '', $bRead = false, $bApply = true) {

    $aResult = array();

    if ($sArguments) {

      foreach (array_map('trim', explode(',', $sArguments)) as $sArgument) {

        if (strpos($sArgument, '=') !== false) {

          list($mKey, $sValue) = explode('=', $sArgument);
        }
        else {

          $mKey = count($aResult);
          $sValue = $sArgument;
        }

        if ($aMatch = $this->matchVariable($sValue)) {

          $aResult[$mKey] = $this->parseVariable($aMatch, array(), $sMode);
        }
        else if ($sString = $this->matchString($sValue)) {

          $aResult[$mKey] = $sString;
        }
        else if ($this->matchNumeric($sValue) !== null) {

          $aResult[$mKey] = $sValue;
        }
        else {

          $aResult[$mKey] = $bApply ? $this->applyPath($sValue, $sMode) : $this->parseArgumentDefault($sValue, $sMode, $bRead, $bApply);
        }
      }
    }

    return $aResult;
  }

  protected function parseArgumentDefault($sValue, $sMode, $bRead, $bApply) {

    $this->launchException('No behaviour defined');
  }

  protected function parsePathAll($sPath, $sMode, array $aArguments = array()) {

    $this->launchException('Not yet implemented');
  }

  protected function parseVariable(array $aMatch, array $aPath, $sMode) {

    $sName = $aMatch[1];

    if ($sName{0} === '$') {

      $result = $this->getParser()->getConstant(substr($sName, 1));
    }
    else {

      $result = $this->getTemplate()->getVariable($sName)->getContent();
    }

    return $result;
    //return $source->parseVariable($aPath, $aMatch[1], $sMode);
  }

  protected function parsePathFunction(array $aMatch, array $aPath, $sMode, $bRead, array $aArguments = array()) {

    $this->launchException('Not yet implemented');
  }

  protected function parsePathRoot(array $aPath, $sMode, $bRead, array $aArguments = array()) {

    $tree = $this->getHandler()->getRootDocument();
    $pather = clone $this;

    $pather->setSource($tree);
    
    return $pather->parsePathToken($aPath, $sMode, $bRead, $aArguments);
  }
}

