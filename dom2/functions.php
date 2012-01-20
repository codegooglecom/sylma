<?php

namespace sylma\dom;

/**
 * Conversion in UTF-8 of the characters : & " < >
 */
function xmlize($sString) {

  return htmlspecialchars($sString, ENT_COMPAT, 'UTF-8');
}




