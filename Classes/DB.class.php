<?php
/******************************************************************************/
// Created by: shlomo hassid.
// Release Version : 1.3
// Creation Date: 14/10/2015
// Copyright 2013, shlomo hassid.
/******************************************************************************/

/*****************************      DEPENDENCE      ***************************/
// conf.php => defined values $conf db values
// trace.class.php => debuger.
/******************************************************************************/

class DB {
    
    public $link;
    public $filter;
    
    public function __construct( $conf ) {
        Trace::add_trace('construct class',__METHOD__);
        
        mb_internal_encoding( 'UTF-8' );
        mb_regex_encoding( 'UTF-8' );
        $this->link = new mysqli( $conf['host'] , $conf['dbuser'] , $conf['dbpass'], $conf['dbname'] );
        $this->link->set_charset( "utf8" );
        
        if( $this->link->connect_errno )
        {
            $this->log_db_errors( "Connect failed", $this->link->connect_error );
            exit();
        }
    }
    public function __destruct() {
        Trace::add_trace('destruct class',__METHOD__);
        $this->disconnect();
    }
    /**
     * Disconnect from db server
     * Called automatically from __destruct function
     */
    public function disconnect() {
        Trace::add_trace('Run DB disconnect',__METHOD__);
	$this->link->close();
    }
    public function ping_db() {
        Trace::add_trace('ping to DB connection',__METHOD__);
        if (!$this->myConn->link->ping()) {
            return false;
        }
        return true;
    }  
    
//BASIC:
    /**
     * Sanitize user data
     *
     * @access public
     * @param mixed $data
     * @return mixed $data
     */
    public function filter( $data ) {
        Trace::add_trace('filter DB data',__METHOD__);
        if( !is_array( $data ) )
        {
            $data = $this->link->real_escape_string( $data );
        }
        else
        {
            $data = array_map(array('DB','filter'),$data);
        }
    	return $data;
    }
    /**
     * Determine if common non-encapsulated fields are being used
     *
     * @access public
     * @param string
     * @param array
     * @return bool
     *
     */
    public function db_common( $value = '' ) {
        if( is_array( $value ) )
        {
            foreach( $value as $v )
            {
                if( preg_match( '/AES_DECRYPT/i', $v ) || preg_match( '/AES_ENCRYPT/i', $v ) || preg_match( '/now()/i', $v ) )
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }
        else
        {
            if( preg_match( '/AES_DECRYPT/i', $value ) || preg_match( '/AES_ENCRYPT/i', $value ) || preg_match( '/now()/i', $value ) )
            {
                return true;
            }
        }
    }

//QUERY EXECUTION:
    /**
     * Perform queries will log execution time
     * All following functions run through this function
     * All data run through this function is automatically sanitized using the filter function
     *
     * @access private
     * @param string
     * @return string
     * @return array
     * @return bool
     *
     */
    public function exec_query($query) {
        $test_micro = microtime(true);
        $results = $this->link->query( $query );
        Trace::add_query_trace($query, (microtime(true) - $test_micro), $this->link->info);
        return $results;
    }
    /**
     * Perform queries
     * All following functions run through this function
     * All data run through this function is automatically sanitized using the filter function
     *
     * @access public
     * @param string
     * @return bool
     *
     */
    public function query( $query ) {
        Trace::add_trace('Run DB query => Q:'.(Trace::get_qindex()),__METHOD__);
        $result = $this->exec_query($query);
        if( $this->link->error ) {
            $this->log_db_errors( $this->link->error, $query );
            try { (is_object($result))?$result->free():null; } catch (Exception $e) {  }
            return false; 
        } else {
            try { (is_object($result))?$result->free():null; } catch (Exception $e) {  }
            return true;
        }
    }

//SIMPLE SELECT QUERIES:
    /** Select query builder.
     *
     * @access public
     * @param string $table_name : table name
     * @param array|string|False $cols : conditions.
     * @param array|string|False $where : conditions. 
     * @param array|string|False $gorup : conditions.
     * @param array|string|False $order : conditions.
     * @param array|string|False $limit : conditions.
     * @param string variable : ORDER BY 
     * @return array : results
     * @return bool : sql error
     * @return null : no results
     * 
     */
    public function select($table_name, $cols = '* ', $where = false, $gorup = false, $order = false, $limit = false) {
        Trace::add_trace('Run DB select query',__METHOD__);
        $query  = "SELECT ".$this->columns_parser($cols);
        $query .= "FROM ".$this->name_parser($table_name)." ";
        $query .= ($where !== false)?"WHERE ".$this->where_parser($where)." ":'';
        $query .= ($gorup !== false)?"GROUP BY ".$this->groupby_parser($gorup)." ":'';
        $query .= ($order !== false)?"ORDER BY ".$this->orderby_parser($order)." ":'';
        $query .= ($limit !== false)?"LIMIT ".$this->join_limit_parser($limit, false)." ":'';
        return $this->get_results($query);
    }
    /** Query Column part parser:
     * 
     * @param array|string|False $cols
     * @return string
     */
    private function columns_parser($cols) {
        Trace::add_trace('Run DB cols parser',__METHOD__);
        if (is_string($cols)) { return trim($cols)." "; }
        $return = $this->name_parser($cols);
        if (is_array($return)) {
            $return = implode(",",$return);
        }
        return $return." ";
    }
    /** Query tablename backtick prepare:
     * 
     * @param string|array $name
     * @return string
     */
    private function name_parser($name) {
        Trace::add_trace('Run DB name parser',__METHOD__);
        if (!is_array($name)&&!is_string($name)) { return ''; }
        $return = (is_array($name))?array():'';
        if (is_array($return)) {
            foreach ($name as $data) {
                if (is_string($data)) {
                    $return[] = "`".preg_replace('%[` ]%','',$data)."`";
                }
            }
        } else {
            $return = 
                (preg_replace('%[`\s]%','',$name) !== "*")?
                    "`".preg_replace('%[` ]%','',$name)."`":
                    $name;
        }
        return $return;
    }
    /** Query where part prepare:
     * 
     * @param string|array $where : Array(Array('col','cond','value'));
     * @return string
     */
    private function where_parser($where) {
        Trace::add_trace('Run DB where parser',__METHOD__);
        if (!is_array($where)&&!is_string($where)) { return ''; }
        $return = (is_array($where))?array():'';
        if (is_array($return)) {
            $count = count($where) - 1;
            foreach ($where as $key => $data) {
                if (is_array($data) && count($data) > 2) {
                    $data[2] = (
                        trim(strtolower($data[2]."")) != 'now()' 
                        && trim(strtolower($data[2]."")) != 'null'
                    )?"'".$this->filter($data[2])."'":$data[2];
                    $return[$key] = $this->name_parser($data[0])." ".trim($data[1])." ".$data[2];
                }
                if ( $key < $count ) {
                    $return[$key] .= (!isset($data[3]))?" AND ":" ".trim($data[3])." ";
                }
            }
            $return = implode("",$return);
        } else { $return = trim($where); }
        return $return." ";
    } 
    /** Query Group by part parser:
     * 
     * @param array|string|False $group : array(cols)
     * @return string
     */
    private function groupby_parser($group) {
        Trace::add_trace('Run DB groupby parser',__METHOD__);
        $return = $this->name_parser($group);
        if (is_array($return)) {
            $return = implode(",",$return);
        }
        return $return." ";
    }
    /** Query Order by part parser:
     * 
     * @param array|string|False $order : array(['ASC','DESC'],array(cols))
     * @return string
     */
    private function orderby_parser($order) {
        Trace::add_trace('Run DB orderby parser',__METHOD__);
        $return = '';
        if (!is_string($order) && !is_array($order)) { return $return; }
        if (is_string($order)) { return $order.' '; }
        if (count($order) === 0) { return $return; }
        $dir = (   is_string($order[0]) && (
                   strtoupper(trim($order[0])) === 'ASC'
                || strtoupper(trim($order[0])) === 'DESC'
                ))?strtoupper(trim($order[0])):'ASC';
        if (isset($order[1]) && is_array($order[1])) {
            $return = $this->columns_parser($order[1]);
        } elseif (is_array($order[0])) {
            $return = $this->columns_parser($order[0]);
        } else {  
            return $return; 
        }
        return $return.$dir.' ';
    }
    /**
     * Output results of given table: by id or the entire table.
     *
     * @access public
     * @param string variable : table name
     * @param int variable : specific row.
     * @param string variable : ORDER BY 
     * @return array : results
     * @return bool : sql error
     * @return null : no results
     * 
     */
    public function get($table_name,$row = false, $order = '') {
        Trace::add_trace('Run DB get query',__METHOD__);
        $query = "SELECT * FROM `".$this->filter($table_name)."` ";
        if ($row) {
            $query .= "WHERE `id`='".$this->filter($row)."' ";
            return $this->get_row($query);
        }
        $query .= ($order != '')?"ORDER BY ".$order:'';
        return $this->get_results($query);
    }
    /**
     * Return specific row based for expected one row result query
     *
     * @access public
     * @param string : query
     * @param bool : assoc or not
     * @return array : results
     * @return bool : sql error
     * @return null : no result
     *
     */
    public function get_row( $query, $assoc = true ) {
        Trace::add_trace('Run DB get row => Q:'.(Trace::get_qindex()),__METHOD__);
        $result = $this->exec_query($query);
        if( $this->link->error ) {
            $this->log_db_errors( $this->link->error, $query );
            try { (is_object($result))?$result->free():null; } catch(Exception $e) {  }
            return false;
        }
        if ($result) {
            $return = ($assoc)?$result->fetch_assoc():$result->fetch_row();
            try { (is_object($result))?$result->free():null; } catch(Exception $e) {  }
            return $return;
        } else {
            try { (is_object($result))?$result->free():null; } catch(Exception $e) {  }
            return null;
        }
    }      
    /**
     * Perform query to retrieve array of associated results of query
     *
     * @access public
     * @param string : query
     * @return array : results assoc array
     * @return bool : sql error
     */
    public function get_results( $query ) {
        Trace::add_trace('Run DB get results => Q:'.(Trace::get_qindex()),__METHOD__);
        $row = null;
        $result = $this->exec_query($query);
        if( $this->link->error ) {
            $this->log_db_errors( $this->link->error, $query );
            try { (is_object($result))?$result->free():null; } catch(Exception $e) {  }
            return false;
        } else {
            $row = array();
            while( $r = $result->fetch_assoc() ) {
                $row[] = $r;
            }
            try { (is_object($result))?$result->free():null; } catch(Exception $e) {  }
            return $row;   
        }
    }
    /**
     * Determine if database table exists
     *
     * @access public
     * @param string : table name
     * @return bool
     *
     */
    public function table_exists( $name ) {
        Trace::add_trace('Run DB table exists => Q:'.(Trace::get_qindex()),__METHOD__);
        $query = "SELECT * FROM '".$name."' LIMIT 1";
        $result = $this->exec_query($query);
        if( $result ) {
            try { (is_object($result))?$result->free():null; } catch (Exception $e) {  }
            return true;
        } else {
            try { (is_object($result))?$result->free():null; } catch (Exception $e) {  }
            return false;
        }
    } 
    /**
     * Count number of rows found matching a specific query
     *
     * @access public
     * @param string : query
     * @return int : number of rows
     *
     */
    public function num_rows( $query ) {
        Trace::add_trace('Run DB num rows query => Q:'.(Trace::get_qindex()),__METHOD__);
        $result = $this->exec_query($query);
        if( $this->link->error ) {
            $this->log_db_errors( $this->link->error, $query );
            try { (is_object($result))?$result->free():null; } catch (Exception $e) {  }
            return false;
        } else {
            $return = $result->num_rows;
            try { (is_object($result))?$result->free():null; } catch (Exception $e) {  }
            return $return;
        }
    }   
    /**
     * Run check to see if value exists, returns true or false
     *
     * Example Usage:
     * $check_user = array(
     *    'user_email' => 'someuser@gmail.com', 
     *    'user_id' => 48
     * );
     * $exists = exists( 'your_table', 'user_id', $check_user );
     *
     * @access public
     * @param string : table name
     * @param string field to check (i.e. 'user_id' or COUNT(user_id))
     * @param array column name => column value to match
     * @return bool
     *
     */
    public function exists( $table = '', $check_val = '', $params = array() ) {
        Trace::add_trace('Run DB exists query',__METHOD__);
        if( empty($table) || empty($check_val) || empty($params) )
        {
            return false;
        }
        $check = array();
        foreach( $params as $field => $value )
        {
            
            if( !empty( $field ) && !empty( $value ) )
            {
                //Check for frequently used mysql commands and prevent encapsulation of them
                if( $this->db_common( $value ) )
                {
                    $check[] = "$field = $value";   
                }
                else
                {
                    $check[] = "$field = '$value'";   
                }
            }

        }
        $check = implode(' AND ', $check);

        $rs_check = "SELECT ".$check_val." FROM ".$table." WHERE ".$check." ";
    	$number = $this->num_rows( $rs_check );
        if( $number === 0 || $number === false )
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    
//ADVANCED SELECT WITH JOIN:
    /** JOIN query builder and execution:
     *
     * @access private
     * @param array             : joins (@move - join_joins_parser)
     * @param array | string    : value (@move - join_values_parser)
     * @param string            : where (@move - join_where_parser)
     * @param array             : order (@move - join_order_parser)
     * @param array             : limit (@move - join_limit_parser)
     * @return array            : results assoc array
     * @return bool             : false sql error
     * 
     */
    public function get_joined($joins, $values = "*", $where=false, $group_by=false, $order=false, $limit=false) {
        Trace::add_trace('Run DB joins query',__METHOD__);
        //Eraly out:
        if (    !is_array($joins) || count($joins) === 0 ||
                (!is_string($values) && !is_array($values)) ||
                (!is_bool($where) && !is_string($where)) ||
                (!is_bool($order) && !is_array($order)) ||
                (!is_bool($limit) && !is_array($limit))
           ) { return false; }
           
        //SELECT Values:
        $query = $this->join_values_parser($values, true);
        //JOINS:
        $query .= $this->join_joins_parser($joins, true);
        //WHERE:
        $query .= $this->join_where_parser($where, true);
        //GROUP:
        $query .= $this->join_groupby_parser($group_by, true);
        //ORDER:
        $query .= $this->join_order_parser($order, true);
        //LIMIT:
        $query .= $this->join_limit_parser($limit, true);

        return $this->get_results($query);
    }
    /** For `join_joins_parser` creates the query part of join statments
     * Will work with: - Joins can be chained -
     *      INNER|OUTER JOIN will have ON
     *      LEFT|RIGHT JOIN will have ON
     *      LEFT|RIGHT OUTER JOIN will have ON
     *      LEFT|RIGHT INNER JOIN will have ON
     *      NATURAL JOIN will ignore ON
     *      JOIN ON or not
     * Example:
     *      $joins = array(
     *          [First JOIN]array('jointype', 'table1.row',[ON] 'table2.row'),
     *          [Next JOIN]array('jointype', 'table1.row',[ON] 'table2.row',)
     *       )
     * @access private
     * @param array 
     * @param bool : Add FROM ?
     * @return string : empty string when none;
     */
    private function join_joins_parser($joins, $add_name = true) {
        Trace::add_trace('Run DB joins parser',__METHOD__);
        $return = ($add_name)?"FROM ":'';
        $added_join = false;
        foreach ($joins as $data) {
            if (is_array($data) && count($data) === 3) {
                $broke1 = (is_string($data[1]))?
                            explode('.',str_replace('`','',$data[1])):
                            array();
                $broke2 = (is_string($data[2]))?
                            explode('.',str_replace('`','',$data[2])):
                            array();
                $type  = strtoupper(trim($data[0]));
                if (count($broke1) === 2 && count($broke2) === 2) {
                    if (!$added_join) $return .= "`".$broke1[0]."` ";
                    $added_join = true;
                    switch($type) {
                        case "INNER JOIN":
                        case "OUTER JOIN":
                        case "LEFT JOIN":
                        case "RIGHT JOIN":
                        case "RIGHT OUTER JOIN":
                        case "LEFT OUTER JOIN":
                        case "RIGHT OUTER JOIN":
                        case "LEFT OUTER JOIN":  
                            $return .= $type." `".$broke2[0]."` "."ON `".
                                       $broke1[0]."`.`".$broke1[1]."` = `".
                                       $broke2[0]."`.`".$broke2[1]."` ";
                        break;
                        default:
                            $return .= "JOIN `".$broke2[0]."` "."ON `".
                                       $broke1[0]."`.`".$broke1[1]."` = `".
                                        $broke2[0]."`.`".$broke2[1]."` ";
                    }
                } elseif ( 
                           $type === "NATURAL JOIN" 
                        && count($broke1) === 1 
                        && count($broke2) === 1 
                ) {
                    if (!$added_join) { $return .= "`".$broke1[0]."` "; }
                    $added_join = true;
                    $return .= "NATURAL JOIN `".$broke2[0]."` ";    
                } elseif ( 
                            $type === "NATURAL JOIN" 
                        && count($broke1) === 0 
                        && count($broke2) === 1 
                        && $added_join
                ) {
                    $return .= "NATURAL JOIN `".$broke2[0]."` ";   
                } elseif ( 
                            $type === "NATURAL JOIN" 
                        && count($broke1) === 1 
                        && count($broke2) === 0 
                        && $added_join
                ) {
                    $return .= "NATURAL JOIN `".$broke1[0]."` ";    
                } elseif ( 
                            $type === "JOIN" 
                        && count($broke1) === 1 
                        && count($broke2) === 1
                ) {
                    if (!$added_join) { $return .= "`".$broke1[0]."` "; }
                    $added_join = true;
                    $return .= "JOIN `".$broke2[0]."` ";
                } elseif ( 
                            $type === "JOIN" 
                        && count($broke1) === 0 
                        && count($broke2) === 1 
                        && $added_join
                ) {
                    $return .= "JOIN `".$broke2[0]."` ";   
                } elseif ( 
                            $type === "JOIN" 
                        && count($broke1) === 1 
                        && count($broke2) === 0 
                        && $added_join
                ) {
                    $return .= "JOIN `".$broke1[0]."` ";   
                } elseif ( 
                            $type === "JOIN" 
                        && count($broke1) > 1 
                        && count($broke2) > 1 
                ) {
                    if (!$added_join) { $return .= "`".$broke1[0]."` "; }
                    $added_join = true;
                    $return .= "JOIN `".$broke2[0]."` ";
                    $return .= "ON `".$broke1[0]."`.`".$broke1[1]."` = `".
                                $broke2[0]."`.`".$broke2[1]."` ";
                }
            }
        }
        return ($added_join)?$return:'';  
    }
    /*
     * For `join_joins_parser` creates the query part of values with tables names
     * 
     * @access private
     * @param array : Example: array('tablename1.colname','tablename2.colname');
     * @param bool : Add SELECT ?
     * @return string : empty string when none;
     * 
     */
    private function join_values_parser($values, $add_name = true) {
        Trace::add_trace('Run DB values parser',__METHOD__);
        $return = ($add_name)?'SELECT ':'';
        $added_values = false;
        if (is_array($values)) {
            $temp_values = "";
            foreach ($values as $data) {
                $broke = (is_string($data))?
                          explode(".", str_replace('`','',$data)):
                          array();
                if (count($broke) > 1) {
                    $added_values = true;
                    $temp_values .= "`".$broke[0]."`.`".$broke[1]."`,";
                }
            }
            if ($added_values) {
                $return .= $temp_values;
                $return = substr($return, 0, -1)." ";
            } else {
               $return .= "* "; 
            }
        } elseif (is_string($values)) {
            $return .= $values." ";
            $added_values = true;
        }
        return ($added_values)?$return:'';
    }
    /*
     * For `join_joins_parser` creates the query part of where statments
     * @access private
     * @param string
     * @param bool : Add WHERE ?
     * @return string : empty string when none;
     */
    private function join_groupby_parser($group,$add_name = true) {
        Trace::add_trace('Run DB group parser',__METHOD__);
        $return = '';
        if (is_string($group)) {
           $return .= ($add_name)?"GROUP BY ".$group." ":$group." ";
        }
        return $return;
    }
    /*
     * For `join_joins_parser` creates the query part of ORDER BY
     * 
     * @access private
     * @param array : Example: array(array("table.row","table.row"),[ASC, DESC])
     * @param bool : Add ORDER BY ?
     * @return string : empty string when none;
     * 
     */
    private function join_order_parser($order, $add_name = true) {
        Trace::add_trace('Run DB order parser',__METHOD__);
        $return = ($add_name)?"ORDER BY ":'';
        if ( is_array($order)  && count($order) > 0 
            && is_array($order[0]) && count($order[0]) > 0 ) {
            foreach ($order[0] as $data) {
                $broke = (is_string($data))? explode(".",str_replace('`','',$data)):
                            array();
                if (count($broke) > 0) {
                    if (isset($broke[1])) {
                        $return .= "`".$broke[0]."`.`".$broke[1]."`,";
                    } else { $return .= "`".$broke[0]."`,"; }
                }
            }
            $return = substr($return, 0, -1)." ";
            if (isset($order[1]) && is_string($order[1])) {
               $return .= strtoupper($order[1])." ";
            }
        }
        return ($return !== "ORDER BY " && $return !== "")?$return:'';
    }
    /*
     * For `join_joins_parser` creates the query part of LIMIT
     * 
     * @access private
     * @param array : Example: array(1,2) | array(10)
     * @param bool : Add LIMIT ?
     * @return string : empty string when none;
     * 
     */
    private function join_limit_parser($limit, $add_name = true) {
        Trace::add_trace('Run DB limit parser',__METHOD__);
        $return = ($add_name)?"LIMIT ":"";
        if (is_array($limit) && count($limit) > 0) {
            $return .= $limit[0];
            if (isset($limit[1])) {
               $return .= ",".$limit[1];
            }
        }
        return ($return !== "LIMIT " && $return !== "")?$return." ":"";
    }
    /*
     * For `join_joins_parser` creates the query part of where statments
     * @access private
     * @param string
     * @param bool : Add WHERE ?
     * @return string : empty string when none;
     */
    private function join_where_parser($where,$add_name = true) {
        Trace::add_trace('Run DB where parser',__METHOD__);
        $return = '';
        if (is_string($where)) {
           $return .= ($add_name)?"WHERE ".$where." ":$where." ";
        }
        return $return;
    }

//INSERT TO DB:
    /**
     * Insert data into database table
     *
     * @access public
     * @param string table name
     * @param array table column => column value
     * @return bool
     *
     */
    public function insert( $table, $variables = array(),  $log = true ) {
        Trace::add_trace('Run DB insert query => Q:'.(Trace::get_qindex()),__METHOD__);
        if( empty( $variables ) ) { return false; }
        $query = "INSERT INTO ".$this->name_parser($table);
        $fields = array();
        $values = array();
        foreach( $variables as $field => $value ) {
            $fields[] = $field;
            $values[] = "'".$value."'";
        }
        $fields = ' (`' . implode('`, `', $fields) . '`)';
        $values = "('". implode("', '", $values) ."')";
        $query .= $fields .' VALUES '. $values;
        $this->exec_query($query);
        if( $this->link->error ) {
            if ($log) { $this->log_db_errors( $this->link->error, $query ); }
            return false;
        } else { return true; }
    }
    /**
    * Insert data KNOWN TO BE SECURE into database table
    * Ensure that this function is only used with safe data
    * No class-side sanitizing is performed on values found to contain common sql commands
    * As dictated by the db_common function
    * All fields are assumed to be properly encapsulated before initiating this function
    *
    * @access public
    * @param string table name
    * @param array table column => column value
    * @return bool
    */
    public function insert_safe( $table, $variables = array(), $log = true ) {
        Trace::add_trace('Run DB insert safe query => Q:'.(Trace::get_qindex()),__METHOD__);
        if( empty( $variables ) ) { return false; }
        $query = "INSERT INTO ".$this->name_parser($table);
        $fields = array();
        $values = array();
        foreach( $variables as $field => $value ) {
            $fields[] = $this->filter( $field );
            $values[] = ( trim(strtoupper($value)) == 'NOW()' ||
                          trim(strtoupper($value)) == 'NULL'
                        )?trim(strtoupper($value)):"'".$this->filter( $value )."'";
        }
        $fields = ' (`' . implode('`, `', $fields) . '`)';
        $values = "(". implode(", ", $values) .")";
        $query .= $fields .' VALUES '. $values;
        $this->exec_query($query);
        if( $this->link->error ) {
            if ($log) { $this->log_db_errors( $this->link->error, $query ); }
            return false;
        } else { return true; }
    }
      
//UPDATE DB:
    /**
     * Update data in database table
     *
     * @access public
     * @param string table name
     * @param array values to update table column => column value
     * @param array|string|False $where : conditions. 
     * @param array : Example: array(1,2) | array(10)
     * @return bool
     *
     */
    public function update( $table, $variables = array(), $where = false, $limit = false ) {
        Trace::add_trace('Run DB update query => Q:'.(Trace::get_qindex()),__METHOD__);
        if(empty( $variables) || !is_string($table)) { return false; }
        $query = "UPDATE ".$this->name_parser($table)." SET ";
        foreach( $variables as $field => $value ) {
            $updates[]=(strtolower($value) === 'null'||strtolower($value) === 'now()')?
              "`".$field."` = ".$value:
              "`".$field."` = '".$this->filter($value)."'";
        }
        $query .= implode(', ', $updates)." ";
        $query .= ($where !== false)?"WHERE ".$this->where_parser($where)." ":'';
        $query .= ($limit !== false)?"LIMIT ".$this->join_limit_parser($limit, false)." ":'';
        $this->exec_query($query);
        if( $this->link->error ) {
            $this->log_db_errors( $this->link->error, $query );
            return false;
        } else { return true; }
    }
   
//DELETE FROM DB: 
    /**
     * Delete data from table
     *
     * @access public
     * @param string table name
     * @param array|string|False $where : conditions. 
     * @param array : Example: array(1,2) | array(10)
     * @return bool
     *
     */
    public function delete( $table, $where = array(), $limit = false ) {
        Trace::add_trace('Run DB delete query => Q:'.(Trace::get_qindex()),__METHOD__);
        if( empty( $where ) || !is_string($table) ) { return false; }
        $query = "DELETE FROM ".$this->name_parser($table)." ";
        $query .= ($where !== false)?"WHERE ".$this->where_parser($where)." ":'';
        $query .= ($limit !== false)?"LIMIT ".$this->join_limit_parser($limit, false)." ":'';

        $this->exec_query($query);
        if( $this->link->error ) {
            $this->log_db_errors( $this->link->error, $query );
            return false;
        } else { return true; }
    }
    /**
     * Truncate entire tables
     *
     * @access public
     * @param array database table names
     * @return int number of tables truncated
     *
     */
    public function truncate( $tables = array() ) {
        if( !empty( $tables ) ) {
            $truncated = 0;
            foreach( $tables as $table ) {
                Trace::add_trace('Run DB truncate table => Q:'.(Trace::get_qindex()),__METHOD__);
                $query = "TRUNCATE TABLE `".trim($table)."`";
                $this->exec_query($query);
                if( !$this->link->error ) {
                    $truncated++;
                }
            }
            return $truncated;
        }
    }
    
//OTHER OPERATINS:   
    /**
     * Output results of queries
     *
     * @access public
     * @param string variable
     * @param bool echo [true,false] defaults to true
     * @return string
     *
     */
    public function display( $variable, $echo = true ) {
        Trace::add_trace('Run DB output results',__METHOD__);
        $out = '';
        if( !is_array( $variable ) ) {
            $out .= $variable;
        } else {
            $out .= '<pre>';
            $out .= print_r( $variable, TRUE );
            $out .= '</pre>';
        }
        if( $echo === true ) {
            echo $out;
        } else {
            return $out;
        }
    }
    /**
     * Get last auto-incrementing ID associated with an insertion
     *
     * @access public
     * @param none
     * @return int
     *
     */
    public function lastid() {
        Trace::add_trace('Run DB grab last ID',__METHOD__);
        return $this->link->insert_id;
    }
    public function lasterror() {
        Trace::add_trace('Run DB grab last Error',__METHOD__);
        return $this->link->error;
    }
    /**
     * Get number of fields
     *
     * @access public
     * @param query
     * @return int
     */
    public function num_fields( $query ) {
        Trace::add_trace('Run DB grab num fields of query => Q:'.(Trace::get_qindex()),__METHOD__);
        $result = $this->exec_query($query);
        $fields = $result->field_count;
        try { (is_object($result))?$result->free():null; } catch(Exception $e) {  }
        return $fields;
    }
    /**
     * Get field names associated with a table
     *
     * @access public
     * @param query
     * @return array
     */
    public function list_fields( $query ) {
        Trace::add_trace('Run DB grab fields of query => Q:'.(Trace::get_qindex()),__METHOD__);
        $result = $this->exec_query($query);
        $listed_fields = $result->fetch_fields();
        try { (is_object($result))?$result->free():null; } catch(Exception $e) {  }
        return $listed_fields;
    }

//LOGS AND ERROR HANDLING:
    /**
     * Allow the class to send admins a message alerting them to errors
     * on production sites
     *
     * @access public
     * @param string $error
     * @param string $query
     * @return void
     */
    public function log_db_errors( $error, $query ) {   
        Trace::add_trace('Run DB log error',__METHOD__);
        $message = '<p>Error at '. date('Y-m-d H:i:s').':</p>';
        $message .= '<p>Query: '. htmlentities( $query ).'<br />';
        $message .= 'Error: ' . $error;
        $message .= '</p>';
        if ( SEND_DB_ERRORS ) { $this->send_errors($message); }
        if ( LOG_DB_ERRORS )  { $this->save_db_errors($error, $query); }
    }
    private function send_errors( $message ) {
        Trace::add_trace('Run DB send error',__METHOD__);
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'To: Admin <'.SEND_ERRORS_TO.'>' . "\r\n";
        $headers .= 'From: MySite report <system@IID.com>' . "\r\n";
        @mail( SEND_ERRORS_TO, 'Database Error', $message, $headers);

    }
    private function save_db_errors( $error, $query ) {
        Trace::add_trace('Run DB save error',__METHOD__);
        if ( getenv("HTTP_X_FORWARDED_FOR") && getenv("HTTP_X_FORWARDED_FOR") !== "") {
           $IP = getenv("HTTP_X_FORWARDED_FOR");
           $proxy = getenv("REMOTE_ADDR");
           $host = gethostbyaddr(getenv("HTTP_X_FORWARDED_FOR"));
        } else {
           $IP = getenv("REMOTE_ADDR");
           $proxy = "No proxy detected";
           $host = gethostbyaddr(getenv("REMOTE_ADDR"));
        }
        if(!$IP || $IP === '' || $IP === null) { $IP = 'cant'; }
        if(!$proxy || $proxy === '' || $proxy === null) { $proxy = 'cant'; }
        if(!$host || $host === '' || $host === null) { $host = 'cant'; }
        $this->insert_safe(
                $this->filter(LOG_DB_TO_TABLE), 
                array(
                    'page' => basename($_SERVER['PHP_SELF']),
                    'user_ip' => $IP,
                    'proxy' => $proxy,
                    'host' => $host,
                    'sql_message' => $error,
                    'query_used' => $query
                ), false /* PREVENT LOG INCASE OF ERROR WHILE LOGING */
        );
    }
}
