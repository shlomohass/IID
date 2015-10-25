<?php
/**************************** Headers and ini *********************************/
header('Content-Type: text/html; charset=UTF-8'); 
error_reporting(-1); // -1 all, 0 don't
ini_set('display_errors', 'on');
date_default_timezone_set('Asia/Jerusalem');

/**************************** set gloable path hooks **************************/

define('DS', DIRECTORY_SEPARATOR);
define( 'PATH_CLASSES',         "Classes".DS );
define( 'PATH_LANG',            "Lang".DS );
define( 'PATH_PAGES',           "Pages".DS   );
define( 'PATH_STRUCT',          "Struct".DS   );
define( 'PATH_AGENT',           "Agent".DS   );
define( 'PATH_LIB_STYLE',       "Lib".DS."Style".DS  );
define( 'GPATH_LIB_STYLE',      "Lib/Style/"  );
define( 'PATH_LIB_JS',          "Lib".DS."Js".DS  );
define( 'GPATH_LIB_JS',         "Lib/Js/"  );

/************************** System Configuration ******************************/

$conf = array(
    'host' => '127.0.0.1',
    'dbname' => 'midatacontrol',
    'dbuser' => 'shlomo',
    'dbpass' => 'sh4hs1'
);

define( 'SEND_DB_ERRORS',   false );
define( 'SEND_ERRORS_TO',   'shlomohassid@gmail.com' );
define( 'LOG_DB_ERRORS',    true );
define( 'LOG_DB_TO_TABLE',  'db_error_log' );

define( 'LOG_BAD_PAGE_REQUESTS',    true );
define( 'TOKEN_SALT', 'ssaltSh' );

$expose_debuger = true;
define( 'EXPOSE_OP_TRACE', ( defined('PREVENT_OUTPUT') && PREVENT_OUTPUT ) ? false : $expose_debuger); //Don't touch

/************************** Page Configurations *******************************/
$conf['general'] = array(
    "uselang"           => "en",
    "author"            => "SM projects",
    "app_version"       => '1.0.1',
    "fav_url"           => "",
    "site_base_url"     => "/azure/Midata/",

);