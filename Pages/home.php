<?php
Trace::add_step(__FILE__,"Loading Page: home");
//Get more classes:


//Set additional head CSS import:
Trace::add_step(__FILE__,"Define css libs for head section");
$Page->include_css(array(
    GPATH_LIB_STYLE."general/cssreset.css"
));

//Set additional head JS import:
Trace::add_step(__FILE__,"Define js libs for head section");
$Page->include_js(array(
    GPATH_LIB_JS."jQuery/jquery-1.11.1.min.js"
));

//Include JS Lang hooks:
Trace::add_step(__FILE__,"Load page js lang hooks");
$Page->set_js_lang(Lang::lang_hook_js("script-home"));

//Set Page:
Trace::add_step(__FILE__,"Set home page data");
$Page->title = Lang::P("gen_title_prefix",false).Lang::P("home_title",false);
$Page->description = Lang::P("home_desc",false);
$Page->keywords = Lang::P("home_keys",false);


//Set additional end body JS import and Conditional JS:
Trace::add_step(__FILE__,"Define conditional js libs for end body section");
$Page->include_js(array(
    GPATH_LIB_JS."jQuery/jquery-1.11.1.min.js",
), false);


//Set header:
Trace::add_step(__FILE__,"Load page header");
require_once PATH_STRUCT.'header.php';

//Page Markup:
Trace::add_step(__FILE__,"Load page HTML");
?>

<section>
    
</section>


<section>
    
</section>


<section>
    
</section>


<!-- START Footer loader -->
<?php 
Trace::add_step(__FILE__,"Load page footer");
require_once PATH_STRUCT.'footer.php'; 
?> 
<!-- END Footer loader -->

<script>
    
    
    
</script>
</body>
</html>