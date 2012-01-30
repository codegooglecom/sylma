<?php

namespace sylma\parser\action\php\basic;
use \sylma\core, \sylma\dom, \sylma\parser\action\php;

require_once('core/argumentable.php');
require_once('core/module/Argumented.php');

/**
 * Add content result to template, choose for wich as*() method to use for render
 */
class Insert extends core\module\Argumented implements core\argumentable, core\controled {

  protected $content;
  protected static $iKey = 0;

  public function __construct(php\_window $controler, $mContent) {

    $this->setControler($controler);
    $this->setNamespace($controler->getNamespace());

    $this->addContent($mContent);
  }

  protected function addContent($mContent) {

    if (is_array($mContent)) {

      foreach ($mContent as $mChild) $this->addContent($mChild);
    }
    else if ($mContent instanceof CallMethod) {

      $this->addContent($mContent->getVar());
    }
    else if ($mContent instanceof php\_object) {

      $interface = $mContent->getInstance()->getInterface();

      if ($interface->isInstance('\sylma\dom\node')) {

        $this->content = $mContent;
      }
      else if ($interface->isInstance('\sylma\core\argumentable')) {

        $return = $this->getControler()->loadInstance('\sylma\dom\node', '\sylma\dom2\node.php');
        $call = $this->getControler()->createCall($this->getControler()->getSelf(), 'loadArgumentable', $return, array($mContent));

        $this->content = $call;
      }
      else if ($interface->isInstance('\sylma\dom\domable')) {

        $return = $this->getControler()->loadInstance('\sylma\dom\node', '\sylma\dom2\node.php');
        $call = $this->getControler()->createCall($this->getControler()->getSelf(), 'loadDomable', $return, array($mContent));

        $this->content = $call;
      }
      else {

        $this->throwException(txt('Cannot add @class %s', $interface->getName()));
      }
    }
    else if ($mContent instanceof php\_scalar || $mContent instanceof dom\node) {

      $this->content = $mContent;
    }
    else {

      $frm = \Sylma::getControler('formater');
      $this->throwException(txt('Cannot insert %s', $frm->asToken($mContent)));
    }
  }

  protected static function getKey() {

    return self::$iKey++;
  }

  public function asArgument() {

    return $this->createArgument(array(
      'insert' => array(
        '@key' => $this->getKey(),
        $this->content
      ),
    ));
  }
}