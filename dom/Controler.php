<?php

namespace sylma\dom;
use sylma\core, sylma\core\functions\path;

require_once('core/functions/Path.php');

class Controler extends core\module\Domed {

  //protected static $sArgumentClass = 'sylma\core\argument\Filed';

  const NS = 'http://www.sylma.org/dom';
  const SETTINGS = 'settings.xml.php';

  const FILE_MANAGER = 'fs/free';

  protected $aDefaultClasses = array();

  protected $aClasses = array(
    'document' => 'DOMDocument',
    'element' => 'DOMElement',
    'fragment' => 'DOMDocumentFragment',
    'text' => 'DOMText',
    'attribute' => 'DOMAttr',
    'comment' => 'DOMComment',
    'instruction' => 'DOMProcessingInstruction',
    'cdata' => 'DOMCdataSection'
    //'collection' => 'DOMNodeList', // See https://bugs.php.net/bug.php?id=48352
  );

  protected $directory;

  protected $aStats = array();

  public function __construct() {

    $this->setDirectory(__file__);
    //$this->setArguments('settings.yml');
    $this->setArguments(include(self::SETTINGS));
    $this->setNamespace(self::NS);

    foreach ($this->getArgument('namespaces')->query() as $sPrefix => $sNamespace) {

      $this->setNamespace($sNamespace, $sPrefix, false);
    }
  }

  public function createCollection(\DOMNodeList $list) {

    require_once('basic/Collection.php');
    return new basic\Collection($list);
  }

  public function createDocument($mContent = '') {

    return parent::createDocument($mContent);
  }

  public function getClasses(core\argument $settings = null) {

    $aClasses = array();

    if (!$this->aDefaultClasses || $settings) {

      $factory = $this->getFactory();

      $classes = $this->getArguments()->get('classes');
      if ($settings) $classes->merge($settings);

      foreach ($this->aClasses as $sKey => $sClass) {

        if ($class = $classes->get($sKey)) {

          $aClasses[$sClass] = $class->read('name');
        }
      }

      $factory->setArguments($this->getArguments());

      if (!$settings) $this->aDefaultClasses = $aClasses;
    }
    else {

      $aClasses = $this->aDefaultClasses;
    }

    return $aClasses;
  }

  public function readArgument($sPath, $mDefault = null, $bDebug = false) {

    return parent::readArgument($sPath, $mDefault, $bDebug);
  }

  public function addStat($sName, array $aArguments) {

    if ($this->readArgument('stats/enable')) $this->aStats[$sName][] = $aArguments;
  }

  public function stringToBool($sValue, $bDefaut = false) {

    $sValue = strtolower($sValue);

    if (strtolower($sValue) == 'true') return true;
    else if (strtolower($sValue) == 'false') return false;
    else return $bDefaut;
  }

  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }
}
