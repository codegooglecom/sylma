<?php

namespace sylma\parser\reflector\basic;
use \sylma\core, sylma\parser\languages\common, sylma\dom, sylma\parser;

\Sylma::load('Domed.php', __DIR__);

abstract class Master extends Domed {

  /**
   * Sub parsers
   * @var array
   */
  protected $aParsers = array();

  /**
   *
   * @param string $sUri
   * @return parser\domed
   */
  public function getParser($sUri) {

    $parser = null;

    if (array_key_exists($sUri, $this->aParsers)) {

      $parser = $this->aParsers[$sUri];
      $parser->setParent($this);
    }

    return $parser;
  }

  public function setParser(parser\reflector\domed $parser, array $aNS) {

    $aResult = array();

    foreach ($aNS as $sNamespace) {

      $aResult[$sNamespace] = $parser;
    }

    $this->aParsers = array_merge($this->aParsers, $aResult);
  }

  protected function loadParser($sNamespace, $sParser = 'element') {

    $result = $this->getParser($sNamespace);

    if ($result) {

      $bResult = false;

      switch ($sParser) {

        case 'element' : $bResult = $result instanceof parser\reflector\elemented; break;
        case 'attribute' : $bResult = $result instanceof parser\reflector\attributed; break;
      }

      if (!$bResult) {

        $this->throwException(sprintf('Cannot use parser %s in %s context', $sNamespace, $sParser));
      }
    }

    return $result;
  }

  public function parse(dom\node $node) {

    return $this->parseNode($node);
  }

  protected function parseElementForeign(dom\element $el) {

    $mResult = null;
    $parser = $this->loadParser($el->getNamespace());

    if ($parser) {

      $mResult = $parser->parseRoot($el);
    }
    else {

      $parent = $this->getControler('dom')->createDocument();

      $newElement = $parent->addElement($el->getName(), null, array(), $el->getNamespace());

      if ($this->useForeignAttributes($el)) {

        $mResult = $this->parseAttributes($el, $newElement->getHandler());
      }
      else {

        foreach ($el->getAttributes() as $attr) {

          $newElement->add($this->parseAttribute($attr));
        }

        $mResult = $newElement->getHandler();
      }

      if ($aChildren = $this->parseChildren($el->getChildren())) {

        $newElement->add($aChildren);
      }
      $mResult = $this->parseElementUnknown($el);
    }

    return $mResult;
  }
}
