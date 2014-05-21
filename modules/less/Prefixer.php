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
    'box-sizing' => array(
      'moz' => self::MODE_PREFIX,
    ),

    'transform' => array(
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

  protected $aContainers = array(
    'transition',
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

          $out->lines[] = $this->formatter->property($sPrefixed, $this->compileValue($this->reduce($aValue), $vendor));
        }
      }
    }

    parent::compileProp($prop, $block, $out);
  }

  public function compileValue($value, Browser $vendor = null) {

    if ($value[0] === 'list') {

      $handler = $this;

      $result = implode($value[1], array_map(function($item) use ($handler, $vendor) {

        return $handler->compileValue($item, $vendor);

      }, $value[2]));
    }
    else if ($value[0] === 'keyword' && $vendor) {

      $sName = $value[1];

      if (in_array($sName, array_keys($this->aProperties))) {

        $value[1] = $vendor->prefixProperty($sName, $this->getProperty($sName));
      }

      $result = parent::compileValue($value);
    }
    else {

      $result = parent::compileValue($value);
    }

    return $result;
  }

  protected function getProperties() {

    return $this->aProperties;
  }

  public function getProperty($sName) {

    return $this->aProperties[$sName];
  }
}
