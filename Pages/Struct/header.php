<?php
if (!isset($Page)) { die("No...."); }

//Parse Version:
$use_version = (!empty($Page->version))?"?version=".$Page->version:"";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="description" content="<?php echo $Page->description; ?>" />
    <meta name="keywords" content="<?php echo $Page->keywords; ?>" />
    <meta name="author" content="<?php echo $Page->author; ?>" />
    
    <link rel="shortcut icon" href="<?php echo $Page::$conf["general"]["fav_url"]; ?>/favicon.ico" />
    <link rel="icon" href="<?php echo $Page::$conf["general"]["fav_url"]; ?>/favicon.ico" sizes="16x16 32x32" />
    <link rel="icon" type="image/png" href="<?php echo $Page::$conf["general"]["fav_url"]; ?>/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/png" href="<?php echo $Page::$conf["general"]["fav_url"]; ?>/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="<?php echo $Page::$conf["general"]["fav_url"]; ?>/favicon-16x16.png" sizes="16x16" />

    <title><?php echo $Page->title; ?></title>
    <!-- CSS head extend -->
    <?php
        //Load css resources:
        foreach ($Page->get_css() as $sheet) {
            echo "<link rel='stylesheet' href='".$Page::$conf["general"]["site_base_url"].$sheet.$use_version."' />";
        }
        //Load base style sheet if its set:
        if (!empty($Page->template)) {
            echo "<!-- Theme CSS-->";
            echo "<link rel='stylesheet' href='".$Page::$conf["general"]["site_base_url"].GPATH_LIB_STYLE.$Page->template.$use_version."' />";
        }
    ?>
    <!-- JS Script head extend -->
    <?php
        //Load js lang hooks:
        if (!empty($Page->get_js_lang())) {
            echo "<script>window['lang'] = { ";
            echo implode(",", array_filter($Page->get_js_lang()));
            echo " }; </script>";
        }
        //Load js libs to head:
        foreach ($Page->get_js() as $script) {
            echo "<script src='".$Page::$conf["general"]["site_base_url"].$script.$use_version."' type='application/javascript'></script>";
        }
    ?>
</head>
<body>
<?php
    //Tokenize page:
    if ($Page->token !== false) {
        echo "<input type='hidden' name='token' id='pageShtoken' value='".$Page->token."' />";
    } else {
        echo "<input type='hidden' name='token' id='pageShtoken' value='' />";
    }
    





