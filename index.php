<?php 
define('PREVENT_OUTPUT', false );  

require_once 'conf.php';
require_once PATH_CLASSES.'Trace.class.php';
require_once PATH_CLASSES.'Func.class.php';
require_once PATH_CLASSES.'DB.class.php';
require_once PATH_CLASSES.'Basic.class.php';
require_once PATH_CLASSES.'Page.class.php';
require_once PATH_CLASSES.'Lang.class.php';
require_once PATH_CLASSES.'FStable.class.php';

/******************* Load Page (DB, Func, conf, page) *************************/

Trace::add_step(__FILE__,"Create DB object");
$Page = new Page( $conf );

/************************* Load User Pref Lang ********************************/
Trace::add_step(__FILE__,"Load Language Dictionary");
if (isset($Page::$conf["general"]["uselang"]) && is_string($Page::$conf["general"]["uselang"])) {
    require_once PATH_LANG.$Page::$conf["general"]["uselang"].'.php';
}
Lang::load($Lang);

/*********************** Set global shared variables ***************************/

$vglob = array (
    "req_secure"    => false
);
Trace::reg_var('Load VGlobals',$vglob);

/****************************** Page Target ***********************************/

$Page->target();
Trace::add_step(__FILE__,"Set page target", $Page->target);

/****************************** Page Loader ***********************************/

switch ($Page->target) {
    case "api":
        Trace::add_step(__FILE__,"Load secure api");
        if ($vglob["req_secure"]) {
            
        }
    break;
    case "home":
    default:
        Trace::add_step(__FILE__,"Load page home");
        
        $Page->template = "pages/home.css";
        include_once PATH_PAGES."home.php";
}

/**************************** Debuger Expose **********************************/

//Expose Trace
Trace::expose_trace();