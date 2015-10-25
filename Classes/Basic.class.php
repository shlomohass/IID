<?php
/******************************************************************************/
// Created by: shlomo hassid.
// Release Version : 1.2
// Creation Date: 12/11/20215
// Copyright 2013, shlomo hassid.
/******************************************************************************/

/*****************************      DEPENDENCE      ***************************/
// conf.php
// DB.class
/******************************************************************************/

class Basic {
    
    public static $conn;
    public static $conf;
    public $Func;

    function __construct( $conf ) {
        Trace::add_trace('construct class',__METHOD__);
        self::$conf = $conf;
        self::$conn = new DB( $conf );
        $this->Func = new Func(); 
    }
    
}