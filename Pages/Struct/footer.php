<?php
if (!isset($Page)) { die("No...."); }
//Parse Version:
$use_version = (!empty($Page->version))?"?version=".$Page->version:"";
?>
<!-- page js -->
<?php
    foreach ($Page->get_js(false) as $script) {
        echo "<script src='".$Page::$conf["general"]["site_base_url"].$script.$use_version."' type='application/javascript'></script>";
    }

