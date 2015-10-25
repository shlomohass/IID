<?php
/******************************************************************************/
// Created by: shlomo hassid.
// Release Version : 1.2
// Creation Date: 14/04/2015
// Copyright 2013, shlomo hassid.
/******************************************************************************/

class Func {
    public function __construct() {
        Trace::add_trace('construct class',__METHOD__);
        
    }
    public function filter_var($input,$term = null) {
        Trace::add_trace('filter variable',__METHOD__);
        switch ($term) {
            case 'int':
                $input = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            break;  
            case 'email':
                $input = filter_var($input, FILTER_SANITIZE_EMAIL);
            break;
            default: 
                $input = filter_var($input, FILTER_SANITIZE_STRING);
        }
        return $input;
    }
    public function synth($input_arr,$target_keys, $trim = true, $empty = true) {
       Trace::add_trace('synth array',__METHOD__);
       $return = array();
       if (!is_array($input_arr) || empty($input_arr) || empty($target_keys)) {
           return (empty($target_keys)) ? $return : array_fill_keys($target_keys, "");
       }
       foreach ($target_keys as $key) {
           if (isset($input_arr[$key])) {
               $return[$key] = ($trim)?
                                trim($this->filter_var($input_arr[$key])):
                                $this->filter_var($input_arr[$key]);
           } elseif ($empty) {
                   $return[$key] = "";
           }
       } 
       return $return;
    }
    public function synth_for_print($input_arr,$target_keys, $trim = true, $empty = true, $html = true) {
       Trace::add_trace('synth array for print',__METHOD__);
       $return = array();
       if (!is_array($input_arr) || empty($input_arr) || empty($target_keys)) {
           return $return;
       }
       foreach ($target_keys as $key) {
           if (isset($input_arr[$key])) {
               $return[$key] = ($trim)?trim($input_arr[$key]):$input_arr[$key];
               if ($return[$key] === TRUE) { $return[$key] = "True"; }
               elseif ($return[$key] === NULL) {  $return[$key] = ""; }
               elseif ($return[$key] === FALSE) { $return[$key] = "False"; }
               else { $return[$key] = htmlentities($return[$key]); }
               
           } elseif ($empty) {
                $return[$key] = "";
           }
       } 
       return $return;
    }
    public function is_hexcolor($color) {
        Trace::add_trace('check hexcolor',__METHOD__);
        return (bool)preg_match('/^#?+[0-9a-f]{3}(?:[0-9a-f]{3})?$/i', $color);
    }   
    /*
     * Check is rgb color value
     * @param   string
     * @return  boolean
     */
    public function is_rgb($val) {
        Trace::add_trace('check rgbcolor',__METHOD__);
        return (bool)preg_match("/^(rgb(s*b([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])bs*,s*b([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])bs*,s*b([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])bs*))|(rgb(s*(d?d%|100%)+s*,s*(d?d%|100%)+s*,s*(d?d%|100%)+s*))$/",
            $val);
    }
    public function is_url($val) {
        Trace::add_trace('check url',__METHOD__);
        return (bool)preg_match("^((((https?|ftps?|gopher|telnet|nntp)://)|(mailto:|news:))(%[0-9A-Fa-f]{2}|[-()_.!~*';/?:@&=+$,A-Za-z0-9])+)([).!';/?:,][[:blank:]])?$",
            $val);
    }
    public function is_urlexists($link) {
        Trace::add_trace('check exists',__METHOD__);
        return (bool)@fsockopen($link, 80, $errno, $errstr, 30);
    }
    /*
     * check given sting is UTF8
     * @param   string
     * @return  boolean
     */
    public function is_utf8($val) {
        Trace::add_trace('check utf8 string',__METHOD__);
        return preg_match('%(?:
        [xC2-xDF][x80-xBF]
        |xE0[xA0-xBF][x80-xBF]
        |[xE1-xECxEExEF][x80-xBF]{2}
        |xED[x80-x9F][x80-xBF]
        |xF0[x90-xBF][x80-xBF]{2}
        |[xF1-xF3][x80-xBF]{3}
        |xF4[x80-x8F][x80-xBF]{2}
        )+%xs', $val);
    }
    public function is_timezone($val) {
        Trace::add_trace('check timezone correct',__METHOD__);
        return (bool)preg_match("/^[-+]((0[0-9]|1[0-3]):([03]0|45)|14:00)$/", $val);
    }
    /**
     * check given number between given values
     * @param   string
     * @return  boolean
     */
    public function is_rangevalue($number,$min,$max) {
        Trace::add_trace('check rangevalue',__METHOD__);
        return (intval($number) >= $min && intval($number) <= $max);
    }
    /*
     * check given string length is between given range
     * @param   string
     * @return  boolean
     */
    public function is_rangelength($val, $min = '', $max = '') {
        Trace::add_trace('check rangelength',__METHOD__);
        return (strlen($val) >= $min && strlen($val) <= $max);
    }
    /*
    * check a number optional -,+,. values
    * @param   string
    * @return  boolean
    */
    public function is_numeric($val) {
        Trace::add_trace('check numeric value',__METHOD__);
        return (bool)preg_match('/^[-+]?[0-9]*.?[0-9]+$/', $val);
    }
    public function is_integer($val) {
        Trace::add_trace('check integer value',__METHOD__);
        return is_int($val);
    }
    /*
     * Matches only alpha letters
     * @param   string
     * @return  boolean
     */
    public function is_text($val) {
        Trace::add_trace('check text value',__METHOD__);
        return is_string($val);
    }
    public function is_alpha($val) {
        Trace::add_trace('check alpha value',__METHOD__);
        return (bool)preg_match("/^([a-zA-Z])+$/i", $val);
    }
    /*
      * Matches base64 enoding string
      * @param   string $val
     *  @param   string $add => attach to pattern, more chars to match.
      * @return  boolean
      */
    public function is_base64($val, $add = "") {
        Trace::add_trace('check base64 value',__METHOD__);
        $pat = "/[^".$add."a-zA-Z0-9+=\/]/";
        return (bool)!preg_match($pat, $val);
    }
    /*
    * Checks that a field matches a v2 md5 string
    * @param   string
    * @return  boolean
    */
    public function is_md5($val) {
        Trace::add_trace('check md5 value',__METHOD__);
        return (bool)preg_match("/[0-9a-f]{32}/i", $val);
    }
    public function val_email($val) {
        Trace::add_trace('check email value',__METHOD__);
        return (bool)(preg_match("/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i",
        $val));
    }
    public function is_emaildomain($email) {
        Trace::add_trace('check dns domain of email',__METHOD__);
        return (bool)checkdnsrr(preg_replace('/^[^@]++@/', '', $email), 'MX');
    }
    public function is_ipaddress($val) {
        Trace::add_trace('check IP value',__METHOD__);
        return (bool)preg_match("/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?).(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?).(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?).(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/",
        $val);
    }
    public function remove_white($val) {
        Trace::add_trace('remove white spaces',__METHOD__);
        return preg_replace('/\s+/', '', $val);
    }
    /* Computes the intersection of arrays and if they are */
    public function in_array_any($needles, $haystack) {
        Trace::add_trace('search in array',__METHOD__);
        return !!array_intersect($needles, $haystack);
    }
    public function create_cookie($name, $value , $expire) {
       Trace::add_trace('create cookie',__METHOD__);
       $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
       return setcookie($name, $value, (time() + ($expire * 60)), "/", $domain);
    }
    public function delete_cookie($name, $expire) {
        Trace::add_trace('delete cookie',__METHOD__);
        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
        return setcookie($name, false, (time() - ($expire * 60)), "/", $domain);
    }
    /**
     * Returns time diff of now to target in ago format
     * 
     * @access public
     * @param string : valid dateTime 
     * @param int    : number of levels to include.
     * @return string : '' empty on fail other time string
     */
    public function time_elapsed_string($datetime, $level = 7) {
        Trace::add_trace('time ago string',__METHOD__);
        if (!is_integer($level) || $level > 7 || $level < 1) { $level = 7; }
        $now = new DateTime;
        $ago = ((is_string($datetime) && strlen($datetime) > 5) || is_object($datetime))?new DateTime($datetime):false;
        if (!$ago) { return ''; }
        $diff = $now->diff($ago);
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;
        $string = array( 
            'y' => 'year','m' => 'month', 'w' => 'week','d' => 'day',
            'h' => 'hour','i' => 'minute', 's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else { unset($string[$k]); }
        }
        return ($string)?implode(', ',array_slice($string,0,$level)).' ago':'just now';
    }
    /**
     * Search array multy in second dimension and return first dimenssion key
     * 
     * @access public
     * @param   array  : stack
     * @paran   string|int : field second dim target.
     * @param   string|int|bool : value to find
     * @return bool : false not found
     * @return string | int : first dim key found.
     */
    public function search_multi_secondDim($arr, $field, $value) {
       Trace::add_trace('search multi secondDim',__METHOD__);
       if (!is_array($arr)) { return false; }
       foreach($arr as $key => $data) {
          if ( isset($data[$field]) && $data[$field] == $value ){ return $key; }
       }
       return false;
    }
    /**
     * Search search by value pair
     * 
     * @access public
     * @param   array  : stack
     * @paran   string|int : field second dim target.
     * @param   string|int|bool : value to find
     * * @param   string|int : value to find
     * @return bool : false not found
     * @return string | int : first dim key found.
     */
    public function search_by_value_pair($arr, $field, $value, $return_key) {
        Trace::add_trace('search_by_value_pair',__METHOD__);
        if (!is_array($arr)) { return false; }
        foreach($arr as $key => $data) {
            if (
                is_array($data) && 
                isset($data[$field]) && 
                $data[$field] == $value &&
                isset($data[$return_key])
            ) {
                return $data[$return_key];
            }
        }
        return false;
    }
    /** Sort array:
     *
     * @access  public
     * @param   bool - preserve keys?
     * @param   string - lh / hl
     * @param   array - target to sort
     */
    public function sort_array($arr, $preserve = false ,$type = "lh") {
        Trace::add_trace('sort array',__METHOD__);
        if (!is_array($arr)) return $arr;
        switch($type){
            case "hl": 
                if ($preserve) { arsort($arr); }
                else { rsort($arr); }
                return $arr;
            default: 
                if ($preserve) { asort($arr); }
                else { sort($arr); }
                return $arr;
        }
    }
    /** Random string generator
     * 
     * @param int $length
     * @return string
     * 
     */
    public function rand_string($length = 10) {
        Trace::add_trace('rand string',__METHOD__);
        $ch = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $chLength = strlen($ch);
        $rand = '';
        for ($i = 0; $i < $length; $i++) { $rand .= $ch[rand(0, $chLength - 1)]; }
        return $rand;
    }
    /** Conver string date in any known supported format to new format:
     * 
     * @access public
     * @param string $value Target value;
     * @param string $from_templatem value string format;
     * @param string $to_template value return string format;
     * @return mixed on failure false, on success string.
     * 
     */
    public function conv_date($value,$from_templatem = 'm/d/Y g:i A',$to_template = 'Y-m-d H:i:s') {
        if (empty($value) || empty($from_templatem) || empty($to_template)) { 
            return false; 
        }
        $d = DateTime::createFromFormat($from_templatem, $value);
        return ($d)?$d->format($to_template):false;
    }
    /** Decode Base64
     *  
     * @access public
     * @param string $str the base64 string to decode
     * @return mixed return false on failure or decoded string
     * 
     */
    public function decodeBase64($str) {
        if (!is_string($str)) { return ''; }
        if (!$this->is_base64($str)) { return ''; }
        return base64_decode($str) ?: '';
    }
    public function xss_clean($text, $tags = '', $invert = FALSE) {
        preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags); 
        $tags = array_unique($tags[1]); 
        if(is_array($tags) && count($tags) > 0) { 
          if($invert == FALSE) { 
            $text = preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
            return filter_var(rawurldecode($text), FILTER_SANITIZE_STRING); 
          } 
          else { 
            $text = preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text); 
            return filter_var(rawurldecode($text), FILTER_SANITIZE_STRING);
          } 
        } elseif($invert == FALSE) { 
          $text = preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text); 
          return filter_var(rawurldecode($text), FILTER_SANITIZE_STRING); 
        } 
        return $text;
    }
    public function send_email($to, $sub, $mes, $replace, $from = false) {
        $header  = 'MIME-Version: 1.0' . "\r\n" .
                   'Content-type: text/html; charset=utf-8 ' . '\r\n' .
                   'From: '.(($from && is_string($from))?$from:"JSnippet <jsnippet@jsnippet.net>").' \r\n' .
                   'X-Mailer: PHP/'.phpversion();
        foreach($replace as $key => $rep) {
            $mes = str_replace($key, $rep, $mes);
        }
        // Mail it 
        if (@mail($to, $sub, $mes, $header)) { 
            return true;  
        } else { 
            return false; 
        }	
    }
    public function validate_recaptcha($res,$sec) {
        $_get = file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify?".
            "secret=".urlencode($sec)."&".
            "response=".urlencode($res)  
        );
        $get = json_decode($_get,true);
        return (isset($get['success']) && $get['success'] == true)?true:false; 
    }
    /** Paging function
     * 
     * @param int $max          -> results count      
     * @param int $inter        -> display per page
     * @param int $cur          -> current page
     * @param string $link      -> base url
     * @param string $urlquery  -> url query variable name
     * @param int $pad          -> padding arround current page
     * @param string $suffix    -> add url suffix
     * @return string           -> UL
     */
    public function paging($max, $inter, $cur, $link='', $urlquery='', $pad = 3, $suffix = false) {
        $pages = ceil(intval($max) / intval($inter));
        $res = array("<ul>");
        $cur--;
        if ($pages > 8) { 
            for ($i=0; $i<$pages; $i++) { 
                if ($i === 0) {
                    $res[] = "<li class='first-page".(($i === $cur)?" active-page":"")."' data-page='".$i."'><a href='".$link.$urlquery.($i+1).(($suffix)?"/".$suffix:"")."'>First</a></li>";
                } elseif ($i+1 == $pages) { 
                    $res[] = "<li class='last-page".(($i === $cur)?" active-page":"")."' data-page='".$i."'><a href='".$link.$urlquery.($i+1).(($suffix)?"/".$suffix:"")."'>Last</a></li>";
                } elseif($i > $cur - $pad && $i < $cur + $pad) {
                    $res[] = "<li ".(($i === $cur)?"class='active-page'":"")." data-page='".$i."'><a href='".$link.$urlquery.($i+1).(($suffix)?"/".$suffix:"")."'>".($i+1)."</a></li>";
                }
            }
        } else { 
            for ($i=0; $i<$pages; $i++) { 
                $res[] = "<li ".(($i === $cur)?"class='active-page'":"")." data-page='".$i."'><a href='".$link.$urlquery.($i+1).(($suffix)?"/".$suffix:"")."'>".($i+1)."</a></li>";
            }
        }
        $res[] = "</ul>";
        return implode('',$res);
    }
    /** Check if useragent indicates a bot:
     *  @access public
     *  @param string $user_agent
     *  @return bool
     */
    public function is_bot($user_agent) {
        $test = preg_match('/abot|dbot|ebot|hbot|kbot|lbot|mbot|nbot|obot|pbot|rbot|sbot|tbot|vbot|ybot|zbot|bot|crawl|slurp|spider/i', $user_agent);
        return ($test === 1 || $test === true)?true:false;
    }
    public function add3dots($string, $repl, $limit) {
        if(strlen($string) > $limit) {
          return substr($string, 0, $limit) . $repl; 
        } else  { return $string; }
    }
}
