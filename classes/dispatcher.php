<?php
class dispatcherBup {
    static protected $_pref = 'bup_';

    static public function addAction($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
        if(strpos($tag, 'bup_') === false) 
            $tag = self::$_pref. $tag;
        return add_action( $tag, $function_to_add, $priority, $accepted_args );
    }
    static public function doAction($tag) {
        if(strpos($tag, 'bup_') === false)
            $tag = self::$_pref. $tag;
        $numArgs = func_num_args();
        if($numArgs > 2) {
            $args = array();
            for($i = 1; $i < $numArgs; $i++) {
                $args[] = func_get_arg($i);
            }
        } elseif($numArgs == 2) {
            $args = func_get_arg(1);
        } else
            $args = NULL;
        return do_action($tag, $args);
    }
    static public function addFilter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
        if(strpos($tag, 'bup_') === false)
            $tag = self::$_pref. $tag;
        return add_filter( $tag, $function_to_add, $priority, $accepted_args );
    }
    static public function applyFilters($tag, $value) {
        if(strpos($tag, 'bup_') === false)
            $tag = self::$_pref. $tag;
        if(func_num_args() > 2) {
            $args = array($tag);
            for($i = 1; $i < func_num_args(); $i++) {
                $args[] = func_get_arg($i);
            }
            return call_user_func_array('apply_filters', $args);
        } else {
            return apply_filters( $tag, $value );
        }
    }
}
