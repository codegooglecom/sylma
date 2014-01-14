<?php
namespace sylma\core;
use sylma\core;

interface request extends core\arrayable {

  function parse();

  /**
   * @return \sylma\storage\fs\file
   */
  function asFile();

  /**
   * @return \sylma\core\argument
   */
  function getArguments();
}