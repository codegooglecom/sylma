<?php

namespace sylma\parser;
use sylma\dom, sylma\storage\fs, sylma\parser, sylma\parser\languages\php;

interface caller {

  const NS = 'http://www.sylma.org/parser/caller';

  function __construct(parser\caller\Controler $controler, fs\file $file);
  function getName();
  function parseCall(dom\element $el, php\basic\_ObjectVar $obj);
  function loadCall(php\basic\_ObjectVar $obj, parser\caller\Method $method, dom\collection $args);
  function loadMethod($sMethod, $sToken = 'method');
}

