<?php

namespace sylma\dom;

require_once('node.php');

interface element extends node {
  
  public function getByName($sName, $sUri = null);
}