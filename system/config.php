<?php

define('SYLMA_PATH_LOGIN',    '/login');
define('SYLMA_PATH_LOGOUT',   '/logout');
define('SYLMA_PATH_USER_EDIT',   '/sylma/modules/users/edit/');
define('SYLMA_PATH_ERROR',    '/error');
define('SYLMA_XAMPP_BUG',    false);

define('SYLMA_PATH_EDITOR',   '/modules/editeur'.SYLMA_ADMIN_EXTENSION);
define('SYLMA_PATH_MODULES',   '/modules');
define('SYLMA_PATH_TEMP',   '/config/tmp');

define('SYLMA_PATH_INTERFACES', '/sylma/interfaces');
define('SYLMA_PATH_INTERFACES_INDEX', '/sylma/system/interfaces.cml');
define('SYLMA_PATH_SPECIALS', '/sylma/system/specials.cml');

//define('NS_XMLNS', 'http://www.w3.org/2000/xmlns/');
define('SYLMA_NS_XHTML', 'http://www.w3.org/1999/xhtml');
define('SYLMA_NS_XSLT', 'http://www.w3.org/1999/XSL/Transform');
define('SYLMA_NS_XINCLUDE', 'http://www.w3.org/2001/XInclude');
define('SYLMA_NS_XSD', 'http://www.w3.org/2001/XMLSchema');

define('SYLMA_NS_EXECUTION', 'http://www.sylma.org/execution');
define('SYLMA_NS_DIRECTORY', 'http://www.sylma.org/directory');
define('SYLMA_NS_SECURITY', 'http://www.sylma.org/security');
define('SYLMA_NS_INTERFACE', 'http://www.sylma.org/interface');
define('SYLMA_NS_MESSAGES', 'http://www.sylma.org/messages');
define('SYLMA_NS_SCHEMAS', 'http://www.sylma.org/schemas');

define('SYLMA_NS_PROCESSOR_FORM', '/sylma/processors/form/schema');
define('SYLMA_NS_FORM_SCHEMA', 'http://schemas.sylma.org/form-schema');

//define('SYLMA_HTML_TAGS', "//html:*[local-name() != 'link']");
define('SYLMA_HTML_TAGS', '//html:div | //html:span | //html:a | //html:ul | //html:h2 | //html:iframe | //html:textarea | //html:script | //html:table | //html:strong | //html:p | //html:button');
define('SYLMA_FIELD_PREFIX', 'field-');

define('SYLMA_SECURITY_FILE', 'directory.sml');
define('SYLMA_DEFAULT_MODE', 0770);
define('SYLMA_DEFAULT_GROUP', 'internet');
define('SYLMA_AUTHENTICATED', 'users');

define('SYLMA_UPLOAD_MAX_SIZE', 10000000);

define('SYLMA_MESSAGES_DEFAULT_STAT', 'notice');

define('MODE_READ', 4);
define('MODE_WRITE', 2);
define('MODE_EXECUTION', 1);

define('SYLMA_MAX_INCLUDE_DEPTH', 10);

$aActionExtensions = array('', '.iml', '.eml', '.dml');
$aExecutableExtensions = array('', 'eml', 'htm', 'html', 'xml', 'txt', 'popup', 'action', 'rss', 'sylma', 'redirect', 'print');


