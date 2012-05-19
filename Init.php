<?php

namespace sylma;

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') define(__NAMESPACE__ . "\PATH_SEPARATOR", ";");
else define(__NAMESPACE__ . "\PATH_SEPARATOR", ":");

preg_match('`[^/]+/[^/]+$`', str_replace('\\', '/', dirname(__FILE__)), $result);

// only used to set include path
define(__NAMESPACE__ . '\PATH', $result[0]); // ex : protected/sylma
set_include_path(get_include_path() . PATH_SEPARATOR . PATH);

define(__NAMESPACE__ . '\SYLMA_PATH', substr($result[0], strlen(ROOT))); // ex : /sylma
define(__NAMESPACE__ . '\SYSTEM_PATH', $_SERVER['DOCUMENT_ROOT']); // ex : /var/www/mysite or C:/xampp/htdocs/mysite

require('core/Sylma.php');

