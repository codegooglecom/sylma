<?php

function dsp() {

  $mArgument = func_get_args();
  if (!$mArgument) $mArgument = 'No message';
  else if (count($mArgument) == 1) $mArgument = current ($mArgument);

  \Sylma::dsp($mArgument);
}
