<?php

namespace sylma\parser\action\test\grouped;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs, \sylma\parser;

require_once('modules/tester/Basic.php');

class Grouped extends tester\Basic {

  const NS = 'http://www.sylma.org/parser/action/test/grouped';
  const FS_CONTROLER = 'fs/editable';

  protected $sTitle = 'Grouped';

  public function __construct(parser\action\Controler $controler = null) {

    \Sylma::getControler('dom');

    require_once('parser/action.php');

    $this->setDirectory(__file__);
    $this->setNamespaces(array(
        'self' => self::NS,
        'le' => parser\action::NS,
    ));

    if (!$controler) $controler = \Sylma::getControler('action');

    $this->setControler($controler);
    //$this->setFiles(array($this->getFile('basic.xml')));
  }

  public function getResult($sType) {

    $action = $this->getArgument('action');

    switch ($sType) {

      case 'dom' : $result = $action->asDOM(); break;
      case 'txt' : $result = $action->asString(); break;
      case 'object' : $result = $action->asObject(); break;
      case 'array' : $result = $action->asArray(); break;
      default :

        $this->throwException(txt('Unknown action type : %s', $sType));
    }

    return $result;
  }

  protected function test(dom\element $test, $controler, dom\document $doc, fs\file $file) {

    $bResult = null;
    $node = $test->getx('le:action');

    $fs = $this->getControler('fs');

    $dir = $this->createTempDirectory();

    if ($sException = $test->readAttribute('catch', null, false)) {

      $bResult = false;

      try {

        $action = $controler->buildAction($this->createDocument($node), array(), $dir, $file->getParent());
        $action->asArray();
      }
      catch (core\exception $e) {

        $bResult = true;
      }
    }
    else {

      if ($nodeResult = $test->getx('self:node', array(), false)) {

        $this->setArgument('node', $nodeResult->getFirst());
      }

      try {

        if ($sPrepare = $test->readx('self:prepare', array(), false)) {

          if (eval('$closure = function($controler) { ' . $sPrepare . '; };') === null) {

            $mResult = $this->evaluate($closure, $controler);
          }
        }

        $aArguments = array();
        
        if ($args = $controler->getArgument('arguments')) {

          $aArguments = $args->asArray();
        }

        $action = $controler->buildAction($this->createDocument($node), $aArguments, $dir, $file->getParent());
        $this->setArgument('action', $action);

        $bResult = parent::test($test->getx('self:expected'), $this, $doc, $file);
      }
      catch (core\exception $e) {

        $e->save();
      }
    }

    //if ($bRun)

    //$dir->delete();

    return $bResult;
  }

  public function getArgument($sPath, $mDefault = null, $bDebug = false) {

    return parent::getArgument($sPath, $mDefault, $bDebug);
  }

}

