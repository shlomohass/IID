<?php
/******************************************************************************/
// Created by: shlomo hassid.
// Release Version : 1.2
// Creation Date: 14/04/2015
// Copyright 2013, shlomo hassid.
/******************************************************************************/
class Page extends Basic {
    
    /** Class propertirs:
     *
     */
    public  $page_variables = array();
    public  $page_notification = array();
    private $page_js_include_head = array();
    private $page_js_include_body = array();
    private $page_css_include = array();
    private $js_lang_hooks = array();
    
    public  $token = false;
    public  $target = false;
    public  $template = "";
    
    public $title = "";
    public $description = "";
    public $keywords = "";
    public $author = "";
    public $version = "";

    
    
    private $err_letter = "E";
    private $suc_letter = "S";
    private $code_spacer = ":";
    private $err_codes = array(
        "general"       => "01",
        "not-secure"    => "02",
        "bad-who"       => "03",
        "query"         => "04",
        "empty-results" => "05",
        "results-false" => "06",
        "not-loged"     => "07",
        "not-legal"     => "08"
    );
    private $suc_codes = array(
        "general"       => "01",
        "with-results"  => "02"
    );
    
    /** Constructor
     * 
     * @param array $conf
     */
    public function __construct( $conf ) {
        
        parent::__construct( $conf );
        Trace::add_trace('construct class',__METHOD__);
        $this->author = (isset(self::$conf['general']['author'])?self::$conf['general']['author']:'');
        $this->version = (isset(self::$conf['general']['app_version'])?self::$conf['general']['app_version']:'');
    }
    
    /** Page Variable getter setter:
     * 
     * @param string $name
     * @param mixed $variable
     * @return mixed
     * 
     */
    public function variable($name = FALSE, $variable = NULL) {
        if ($variable === NULL) {  
            return $this->_get_page_variable($name);
        }
        return $this->_set_page_variable($name, $variable);
    }
    
    /** Page Array variable traverse:
     * 
     * @param string $name
     * @param string | number $key1
     * @param string | number $key2
     * @return mixed
     */
    public function in_variable($name = FALSE, $key1 = NULL, $key2 = NULL) {
        if (!is_string($name)) { return null; }
        if (!is_null($key1) && !is_null($key2)) {
            if (
                   isset($this->page_variables[$name])
                && is_array($this->page_variables[$name])
                && isset($this->page_variables[$name][$key1])
                && is_array($this->page_variables[$name][$key1])
                && isset($this->page_variables[$name][$key1][$key2])
            ) { return $this->page_variables[$name][$key1][$key2]; }
        } elseif (!is_null($key1)) {
            if (
                   isset($this->page_variables[$name])
                && is_array($this->page_variables[$name])
                && isset($this->page_variables[$name][$key1])
            ) { return $this->page_variables[$name][$key1]; }
        }
        return null;
    }
    
    /** Page variable setter:
     * 
     * @param string $name
     * @param mixed $variable
     * @return boolean
     */
    private function _set_page_variable($name, $variable) {
        Trace::add_trace('SET page variable',__METHOD__);
        if (!is_string($name) || $variable === NULL) {
            return false;
        }
        $this->page_variables[$name] = $variable;
        return true;
    }
    
    /** Page variable getter:
     * 
     * @param string $name
     * @return mixed; 
     */
    private function _get_page_variable($name) {
        Trace::add_trace('GET page variable',__METHOD__);
        if (!is_string($name)) { return ''; }
        if (!isset($this->page_variables[$name])) { return ''; }
        return $this->page_variables[$name];
    }
    
    /** ADD scripts to be included in head:
     * 
     * @param mixed $script => array, string
     * @param bool $inhead  => whether to include in head or in body
     * @return boolean
     */
    public function include_js($script, $inhead = true) {
        $topush = array();
        if (empty($script) || (!is_string($script) && !is_array($script))) {
            return false;
        }
        if (is_array($script)) {
            $topush = $script;
        } else {
            $topush[] = $script;
        }
        foreach ($topush as $sc) {
            if($inhead) {
                $this->page_js_include_head[] = $sc;
            } else {
                $this->page_js_include_body[] = $sc;
            }
        }
        return true;
    }
    
    /** get all scripts to be included in page
     * @param bool $head head scripts or body?
     * @return array
     */
    public function get_js($head = true) {
        if ($head) {
            return $this->page_js_include_head;
        }
        return $this->page_js_include_body;
    }
    
    /** ADD stylesheets to be included in head:
     * 
     * @param mixed $css => array, string
     * @return boolean
     */
    public function include_css($css) {
        $topush = array();
        if (empty($css) || (!is_string($css) && !is_array($css))) {
            return false;
        }
        if (is_array($css)) {
            $topush = $css;
        } else {
            $topush[] = $css;
        }
        foreach ($topush as $cs) {
            $this->page_css_include[] = $cs;
        }
        return true;
    }
    
    /** get all css to be included in page
     * 
     * @return array
     */
    public function get_css() {
        return $this->page_css_include;
    }
    
    /** Set (pushes) all javascrit lang hooks to include in page:
     * 
     * @param string $hooks => use Lang::lang_hook_js(string script-name) to generate
     * 
     */
    public function set_js_lang($hooks) {
        
        $this->js_lang_hooks[] = $hooks;
    }
    
    /** get all js hooks:
     * 
     * @return array js_lang_hooks
     */
    public function get_js_lang() {
        return $this->js_lang_hooks;
    }
    
    /** Validate a secure page request with a secure POST variables
     * 
     * Will log if shtoken is invalid but wont if shtoken is not set.
     * 
     * @param object $Shtoken
     * @return boolean
     */
    public function page_req_secure($Shtoken) {
        Trace::add_trace('secure request check',__METHOD__);

    }
    
    /** Return all needed encoded form fields to comunicate with 
     *  the page request proccess
     * 
     * @param string $page
     * @param string $type
     * @return string::html input fields
     * 
     */
    public function page_secure_form($page, $type = 'dynamic') {
        return "<input type='hidden' name='shtoken' value='".$this->token."' />"
               ."<input type='hidden' name='req_type' value='".$type."' />"
               ."<input type='hidden' name='page' value='".$page."' />";
    }
    
    /** Set page target name 
     * 
     * @return boolean
     */
    public function target() {
        Trace::add_trace('get page target',__METHOD__);
        $target = $this->Func->filter_var(filter_input(INPUT_GET,'page'));
        if (!is_null($target) && $target) {
            $this->target = $target;
            return true;
        }
        $this->target = false;
        return false;
    }
 
    /** Kills bad requests that uses tokens.
     * 
     * @param mixed reference $arr_rec
     */
    public function page_kill_request(&$arr_rec) {
        if (is_array($arr_rec)) {
            $arr_rec = array();
        } elseif (is_number($arr_rec)) {
            $arr_rec = null;
        } elseif (is_string($arr_rec)) {
            $arr_rec = null;
        } elseif (is_bool($arr_rec)) { 
            $arr_rec = null;
        }
    }
    
    /** Response construct:
     * 
     * @param array|bool $results
     * @return array : not empty or false.
     */
    public function page_dynamic_response($success,$results) {
        if (is_array($results) && !empty($results)) {
            $this->page_dynamic_success($success,false);
            echo json_encode($results);
        } else {
            $this->page_dynamic_success($success);
        }
    }
    
    /** Output defined error codes:
     * 
     *  @param string $type : code name,
     *  @param bool $die : dye or echo
     *  
     */
    public function page_dynamic_error($type = 'general', $die = true) {
        if (!isset($this->err_codes[$type])) {  $type = 'general'; }
        if (!is_bool($die)) { $die = true; }
        if ($die) {
            die(
                $this->err_letter.
                $this->code_spacer.
                $this->err_codes[$type]
            );
        } else {
            echo $this->err_letter.$this->code_spacer.$this->err_codes[$type];
        }
    }
    
    /** Output defined success codes:
     * 
     *  @param string $type : code name,
     *  @param bool $die : dye or echo
     *  
     */
    public function page_dynamic_success($type, $die = true) {
        if (!isset($this->suc_codes[$type])) {  $type = 'general'; }
        if (!is_bool($die)) { $die = true; }
        if ($die) {
            die(
                $this->suc_letter.
                $this->code_spacer.
                $this->suc_codes[$type]
            );
        } else {
            echo $this->suc_letter.$this->code_spacer.$this->suc_codes[$type];
        } 
    }
}

