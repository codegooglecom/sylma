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

  protected $iKey;

  protected $sContext;

  public function __construct(php\_window $controler, php\linable $mContent, $iKey = null, $bTemplate = true) {

    $this->setControler($controler);
    $this->setNamespace($controler->getNamespace());

    $this->bTemplate = $bTemplate;
    if (is_null($iKey)) $this->iKey = $controler->getKey('insert');
    else $this->iKey = $iKey;

    if ($bTemplate) $controler->add(new self($controler, $mContent, $this->getKey(), false));
    else $this->addContent($mContent);

    $this->setContext($controler->getContext());
  }

  protected function getContext() {

    return $this->sContext;
  }

  protected function setContext($sContext) {

    $this->sContext = $sContext;
  }

  protected function addContent(php\linable $mContent) {

    if ($this->content) {

      $this->throwException(t('Cannot set more than once the content in insert'));
    }

    $this->content = $mContent;
  }

  public function getKey() {

    return $this->iKey;
  }

  public function asArgument() {

    return $this->createArgument(array(
      ($this->bTemplate ? 'insert-call' : 'insert') => array(
        '@key' => $this->getKey(),
        '@context' => $this->getContext(),
        $this->content,
      ),
    ));
  }
}