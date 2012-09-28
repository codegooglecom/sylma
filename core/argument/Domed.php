<?php

namespace sylma\core\argument;
use sylma\core, sylma\dom;

require_once('Iterator.php');
require_once('dom/domable.php');

class Domed extends Iterator implements dom\domable {

  protected static $aPrefixes = array();
  /**
   * Build an @class Options's object with this argument's array
   *
   * @param dom\node $oRoot The root node to insert the results to
   * @param? dom\document|null $oSchema The schema that will be used by the Options object
   * @param? string $sPath An optional sub-path to extract the arguments from
   *
   * @return ElementInterface The new builded node, containing the xml version of this array
   */
  public function getOptions(dom\document $schema = null, $sPath = '') {

    require_once('dom\Argument.php');

    $doc = $this->getDocument();
    self::getElement($doc, $sPath);

    return new dom\Argument($doc, $schema);
  }

  public function asDOM($sNamespace = '') {

    if (!$sNamespace) $sNamespace = $this->getNamespace();

    if (!$sNamespace) {

      $this->throwException(t('No namespace defined for export as dom document'));
    }

    $bChildren = false;

    $this->normalize();

    if (count($this->aArray) > 1) {

      $bChildren = true;
      $aValues = array('root' => $this->aArray);
    }
    else {

      $aValues = $this->aArray;
    }

    $result = self::buildDocument($aValues, $sNamespace);

    if (!$result || $result->isEmpty()) {

      $this->throwException (sprintf('No result or invalid result when exporting @namespace %s', $sNamespace));
    }

    if ($bChildren) $result = $result->getChildren();

    return $result;
  }

  public static function buildDocument(array $aArray, $sNamespace) {

    $dom = \Sylma::getControler('dom');
    $doc = $dom->create('handler');

    self::buildNode($doc, $aArray, $sNamespace);

    return $doc;
  }

  public function getElement(dom\complex $parent, $sPath = '') {

    if ($sPath) $aArray = $this->get($sPath);
    else $aArray = $this->aArray;

    return self::buildNode($parent, $aArray);
  }

  protected static function buildPrefix($sNamespace) {

    if (!array_key_exists($sNamespace, self::$aPrefixes)) {

      $sPrefix = 'ns' . count(self::$aPrefixes);
      self::$aPrefixes[$sNamespace] = $sPrefix;
    }

    return self::$aPrefixes[$sNamespace] . ':';
  }

  private static function buildNode(dom\complex $parent, array $aArray, $sNamespace) {

    foreach ($aArray as $sKey => $mValue) {

      if ($mValue !== null) {

        if (is_integer($sKey)) {

          // when integer key use duplicated element's name

          $node = $parent;
        }
        else {

          if ($sKey[0] == '@') {

            $parent->setAttribute(substr($sKey, 1), $mValue);
            continue;
          }
          else if ($sKey[0] == '#') {

            foreach ($mValue as $mSubValue) {

              $node = $parent->addElement(self::buildPrefix($sNamespace) . substr($sKey, 1), null, array(), $sNamespace);

              if (is_array($mSubValue)) self::buildNode($node, $mSubValue, $sNamespace);
              else $node->add($mSubValue);
            }

            continue;
          }
          else {

            $node = $parent->addElement(self::buildPrefix($sNamespace) . $sKey, null, array(), $sNamespace);
          }
        }

        if (is_array($mValue)) {

          self::buildNode($node, $mValue, $sNamespace);
        }
        else {

          $node->add($mValue);
        }
      }
    }
  }

  protected static function normalizeObject($val, $bEmpty = false) {

    if ($val instanceof dom\node ||
        $val instanceof dom\collection) {

      $mResult = $val;
    }
    else {

      $mResult = parent::normalizeObject($val, $bEmpty);
    }

    return $mResult;
  }
}
