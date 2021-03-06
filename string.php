<?php
/**
 * Custom is_int function that works on strings as well.
 *
 * @author  Batandwa Colani
 * @date    2014-02-04
 * @version 1
 *
 * @param   mixed      $val The potential integer to be evaluated
 *
 * @return  bool            Whether the input is an integer or not.
 */
function is_int($val){
  return (filter_var($val, FILTER_VALIDATE_INT) !== false && strpos($val, '-') === false);
}

/**
 * Checks if the passed string is a JSON string.
 *
 * @author  h0mayun
 * @author  Batandwa Colani
 * @date    2014-02-08
 * @version 1
 *
 * @param   string     $str The string to be examined.
 * @return  boolean         Whether  the string is JSON or not.
 */
function is_json($str) {

  return is_string($str) && is_array(json_decode($str,true));
}