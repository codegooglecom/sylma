<?php

namespace sylma\core\argument;
use \sylma\core, sylma\dom;

class Treed extends Filed {

  public function parseTree() {

    return self::parseTreeArray($this->aArray);
  }

  /**
   * Transform an uni-dimensional array of strings in multiple-dimensions array of grouped strings
   * The values are associate or separate if string begins, or not, with the same characters
   * Usefull for statistics on paths
   *
   * <code language="yaml">
   * abc, abdef, abc, ab, defgo, deasd, dearg
   * # become
   * ab :
   *   0 : 1
   *   c : 2
   *   def : 1
   * de :
   *   fgo : 1,
   *   a :
   *     sd : 1
   *     rg : 1
   * </code>
   */
  public static function parseTreeArray(array $aSource) {

    $sPrevious = '';
    $aResult = array();
    ksort($aSource);

    while (list($sKey, $mValue) = each($aSource)) {

      // sequences will be store on original value as a stack of key paths
      $aSource[$sKey] = array();
      $bMatch = false;

      if ($sPrevious) {

        $iMatch = self::compareTreeItem($sKey, $sPrevious);

        if ($iMatch) {

          // 1 or more identicals chars

          $bMatch = true;
          $mGroup =& $aResult;

          if ($iMatch === true) $iDiffMatch = strlen($sPrevious);
          else $iDiffMatch = $iMatch;

          // load parent sequences

          foreach ($aSource[$sPrevious] as $sGroup) {

            $iDiffMatch -= strlen($sGroup);

            if ($iDiffMatch >= 0) {

              $aSource[$sKey][] = $sGroup;
              $mGroup =& $mGroup[$sGroup];

              if ($iDiffMatch === 0) break;
            }
            else break;
          }

          // match = true : current group
          // match > 0 : new group desc
          // match = 0 : current group
          // match < 0 : new group asc

          if ($iMatch === true) {

            if (is_array($mGroup)) {

              if (array_key_exists(0, $mGroup)) $mGroup[0] += $mValue;
              else $mGroup[0] = $mValue;
            }
            else $mGroup += $mValue;
          }
          else {

            if ($iDiffMatch && $iMatch !== strlen($sPrevious)) {

              // value match a part of a parent sequence, must split it

              if ($iMatch > 0) $iSubMatch = $iDiffMatch;
              else $iSubMatch = strlen($sGroup) + $iDiffMatch;

              $sCommon = substr($sGroup, 0, $iSubMatch);
              $mGroupValue = $mGroup[$sGroup];

              unset($mGroup[$sGroup]);

              $mGroup[$sCommon] = array(substr($sGroup, $iSubMatch) => $mGroupValue);
              $mGroup =& $mGroup[$sCommon];

              // build the current path for next loop

              $aSource[$sKey][] = $sCommon;
            }

            // build the new key

            $sNewKey = substr($sKey, $iMatch);
            $aSource[$sKey][] = $sNewKey;

            if (is_array($mGroup)) $mGroup[$sNewKey] = $mValue;
            else {

              $mGroup = array(
                0 => $mGroup,
                $sNewKey => $mValue);
            }
          }
        }
      }

      if (!$bMatch) {

        $aResult[$sKey] = $mValue;
        $aSource[$sKey] = array($sKey);
      }

      $sPrevious = $sKey;
    }

    return $aResult;
  }

  /**
   * Compare two strings and give the position of the first non matching character
   *
   * @param string $sValue1
   * @param string $sValue2
   * @return bool|integer
   *   @bool true : values are identicals
   *   @bool false : first chars are different,
   *   @int 0 : first value is contained in second value
   *   @int +n : value are identical until this char. position
   */
  protected static function compareTreeItem($sFrom, $sTo) {

    $iLength = min(strlen($sFrom), strlen($sTo));
    $iSize = 0;

    for ($iKey = 0; $iKey < $iLength; $iKey++) {

      if ($sFrom[$iKey] == $sTo[$iKey]) $iSize++;
      else break;
    }

    return $iSize && $iSize == strlen($sFrom) ? true : $iSize;
  }

  public static function renderTree(array $aArray, $sPrefix = '', $iDepth = 0) {

    $result = new HTML_Div;

    $iCount = 0;

    foreach ($aArray as $sKey => $mValue) {

      if (is_array($mValue)) {

        if (array_key_exists(0, $mValue)) {

          list($iRealCount, $children) = self::renderTree(array($sKey => $mValue[0]), $sPrefix, $iDepth + 1);
          $result->add($children);
          unset($mValue[0]);
        }
        else $iRealCount = 0;

        list($iSubCount, $children) = self::renderTree($mValue, $sPrefix . $sKey);

        if ($result && !$iRealCount) {

          $count = '#'.$iSubCount;
          $result->addNode('div', array(new HTML_Em($sPrefix . $sKey), $count), array('style' => 'border-bottom: 1px dotted #eee'));
        }

        $result->add($children);
        $iCount += $iSubCount + $iRealCount;
      }
      else {

        if ($sKey) {

          $iCount += $mValue;

          if ($sPrefix) $prefix = new HTML_Em($sPrefix);
          else $prefix = null;

          if ($mValue > 1) $value = new HTML_Strong('#'.$mValue);
          else $value = null;

          $result->addNode('div', array($prefix , $sKey, $value));
        }
      }
    }

    return array($iCount, $result);
  }
}

