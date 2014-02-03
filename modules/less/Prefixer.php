<?php

namespace sylma\modules\less;
use sylma\core;

\Sylma::load('lessc.php', __DIR__);

class Prefixer extends \lessc {

  const MODE_PREFIX = 0;
  const MODE_DEFAULT = 1;

  protected $aProperties = array(

    'background-clip' => array(),
    'background-origin' => array(),
    'background-size' => array(),

    'border-radius' => array(),
    'box-sizing' => array(),

    'transform' => array(
      'moz' => self::MODE_PREFIX,
      'o' => self::MODE_PREFIX,
    ),

    'transition' => array(
      'o' => self::MODE_PREFIX
    ),
    'transition-duration' => array(
      'o' => self::MODE_PREFIX
    ),
    'transition-delay' => array(
      'o' => self::MODE_PREFIX
    ),
    'transition-property' => array(
      'o' => self::MODE_PREFIX
    ),
  );

  protected $aVendors = array(
    'moz' => array(
      'mode' => self::MODE_DEFAULT,
    ),
    'webkit' => array(
      'mode' => self::MODE_PREFIX,
    ),
    'o' => array(
      'mode' => self::MODE_DEFAULT,
    ),
    'ms' => array(
      'mode' => self::MODE_DEFAULT,
    ),
  );

  public function __construct($fname = null) {

    parent::__construct($fname);

    $this->aVendors = array_map(function(array $aVendor, $sPrefix) {

      return new Browser($sPrefix, $aVendor);

    }, $this->aVendors, array_keys($this->aVendors));
  }

  protected function compileProp($prop, $block, $out) {

    if ($prop[0] === 'assign' && array_key_exists($prop[1], $this->getProperties())) {

      list(, $sName, $aValue) = $prop;

      foreach ($this->aVendors as $vendor) {

        if ($sPrefixed = $vendor->prefixProperty($sName, $this->getProperty($sName))) {

          $out->lines[] = $this->formatter->property($sPrefixed, $this->compileValue($this->reduce($aValue)));
        }
      }
    }

    parent::compileProp($prop, $block, $out);
  }

  protected function getProperties() {

    return $this->aProperties;
  }

  protected function getProperty($sName) {

    return $this->aProperties[$sName];
  }
}

/*
 Array[4](
0 => 'assign'
, 1 => 'transition-property'
, 2 =>
Array[3](
0 => 'list'
, 1 => ', '
, 2 =>
Array[2](
0 =>
Array[2](
0 => 'keyword'
, 1 => 'margin'
)

, 1 =>
Array[2](
0 => 'keyword'
, 1 => 'opacity'
)

)

)
 , -1 => 479
)
 *
 *
 *
 Array[4](
0 => 'assign'
, 1 => 'opacity'
, 2 =>
Array[3](
0 => 'number'
, 1 => '0'
, 2 => ''
)

, -1 => 587
)
 *
 *
 Array[4](
0 => 'assign'
, 1 => 'margin-left'
, 2 =>
Array[3](
0 => 'number'
, 1 => '100'
, 2 => '%'
)

, -1 => 850
)
 *
 *
 *
 Array[4](
0 => 'assign'
, 1 => 'background-size'
, 2 =>
Array[2](
0 => 'keyword'
, 1 => 'cover'
)

, -1 => 596
)
 */