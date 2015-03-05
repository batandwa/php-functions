<?php
/**
 * <code>array_merge_recursive<code> does indeed merge arrays, but it converts values with duplicate
 * keys to arrays rather than overwriting the value in the first array with the duplicate
 * value in the second array, as array_merge does. i.e., with array_merge_recursive,
 * this happens (documented behavior):
 *
 * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
 *     => array('key' => array('org value', 'new value'));
 *
 * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
 * Matching keys' values in the second array overwrite those in the first array, as is the
 * case with array_merge, i.e.:
 *
 * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
 *     => array('key' => array('new value'));
 *
 * Parameters are passed by reference, though only for performance reasons. They're not
 * altered by this function.
 *
 * @param array $array1
 * @param array $array2
 * @return array
 * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
 * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
 */
function array_merge_recursive_distinct ( array &$array1, array &$array2 )
{
  $merged = $array1;

  // $keys = array_merge(array_keys($array1), array_keys($array2));
  $keys = array_merge(array_keys($array1) + array_keys($array2));
  // dsm($array1);
  // dsm($array2);

  foreach ( $keys as $key)
  {

    if ( isset ( $array2[$key] ) && is_array ( $array2[$key] ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
    {
      // dsm('Merging...');
      $merged [$key] = array_merge_recursive_distinct ( $merged [$key], $array2[$key] );
    }
    else if(isset($array2 [$key]))
    {
      // dsm('Over writtng... ' . $key);
      $merged [$key] = $array2[$key];
    }
  }
// dsm($merged);
  return $merged;
}

/**
 * Check if an array only consistis of values that might be deemed blank,
 * as per the <code>empty()</code> function. ie. 0, FALSE, "", NULL, etc...
 *
 * @param  array  $array The array to be tested
 *
 * @return boolean       Whether or not the array only contains empty items
 */
function array_of_empties($array) {
  foreach ($array as $value) {
    if(!empty($value)) {
      return FALSE;
    }
  }

  return TRUE;
}

/**
 * Implodes a single dimensional associative array with various formatting options / modifiers.
 *
 * @param array $array single dimensional array to implode
 * @param array $overrideOptions is an key->value array with the following valid values:
 * - inner_glue           =>  string to connect keys to values with
 * - outer_glue           =>  string to connect keys-value pairs together
 * - prepend              =>  string to attach to the front of the final result
 * - append               =>  string to attach to the end of the final result
 * - skip_empty           =>  bool if true then do not include entries with values that evaluate to false
 * - prepend_inner_glue   =>  bool if true then stick the inner_glue on to the front of all key-value pairs
 * - append_inner_glue    =>  bool if true then stick the inner_glue on to the end of all key-value pairs
 * - prepend_outer_glue   =>  bool if true then stick the outer_glue on to the front of the return string
 * - append_outer_glue    =>  bool if true then stick the outer_glue on to the end of the return string
 * - urlencode            =>  bool if true then urlencode() all returned values
 * - part                 =>  string setting what part(s) of the key-value pairs to return; valid values:
 *   - both   ->  display both the key and the value
 *   - key    ->  display the key and NOT the value; inner_glue will not display except with prepend/append
 *   - value  ->  display the value and NOT the key; inner_glue will not display except with prepend/append
 *
 * @author Sean P. O. MacCath-Moran -- http://emanaton.com/code/php/implode_assoc
 *
 * @example
 *  $titleParts = array('Type'=>'Image', 'Size'=>'16 Meg', 'Description'=>'',
 *                      'Author'=>'Sean P. O. MacCath-Moran', 'Site'=>'www.emanaton.com');
 *  echo implode_assoc($titleParts, array('inner_glue'=>': ', 'outer_glue'=>' || ',
 *                                        'skip_empty'=>true));
 *      Type: Image || Size: 16 Meg || Arther: Sean P. O. MacCath-Moran || Site: www.emanaton.com
 *
 * $htmlArgs = array('href'=>'http://www.emanaton.com/', 'title'=>'emanaton dot com', 'style'=>'',
 *                   'class'=>'promote siteLink');
 * echo implode_assoc($htmlArgs, array('inner_glue'=>'="', 'outer_glue'=>'" ', 'skip_empty'=>true,
 *  'append_outer_glue'=>true, 'prepend'=>'<a ', 'append'=>'>'));
 *     <a href="http://www.emanaton.com/" title="emanaton dot com" class="promote siteLink" >
 *
 * $getArgs = array('page'=>'2', 'id'=>'alpha1', 'module'=>'acl', 'controller'=>'role', 'action'=>'',
 *                  'homepage'=>'http://www.emanaton.com/');
 * echo implode_assoc($getArgs, array('skip_empty'=>true, 'urlencode'=>true));
 *     page=2&id=alpha1&module=acl&controller=role&template=default&value=http%3A%2F%2Fwww.emanaton.com%2F
 *
 * @return string of the imploded key-value pairs
*/
function implode_assoc($array, $overrideOptions = array()) {
   
  // These default options set the defaults but are over-written by matching values from $overrideOptions
  $options = array(
    'inner_glue'=>'=',
    'outer_glue'=>'&',
    'prepend'=>'',
    'append'=>'',
    'skip_empty'=>false,
    'prepend_inner_glue'=>false,
    'append_inner_glue'=>false,
    'prepend_outer_glue'=>false,
    'append_outer_glue'=>false,
    'urlencode'=>false,
    'part'=>'both' //'both', 'key', or 'value'
  );
   
  // Use values from $overrideOptions that match keys in $options and then extract those values into
  // the current workspace.
  foreach ($overrideOptions as $key=>$val) { if (isset($options[$key])) {$options[$key] = $val;} }
  extract($options);
   
  // $output holds the imploded results of the key-value pairs
  $output = array();
   
  // Create a collection of the inner key-value pairs and glue them as indicated by the $options
  foreach($array as $key=>$item) {
    // If not skipping empty values OR if the item evaluates to true.
    // i.e. If $skip_empty is true then check to see if the array item's value evaluates to true.
    if (!$skip_empty || $item) {
      $output[] =
        ($prepend_inner_glue ? $inner_glue : '').
        ($part != 'value' ? $key : ''). // i.e. show the $key if $part is 'both' or 'key'
        ($part == 'both' ? $inner_glue : '').
        // i.e. show the $item if $part is 'both' or 'value' and optionally urlencode $item
        ($part != 'key' ? ($urlencode ? urlencode($item) : $item) : '').
        ($append_inner_glue ? $inner_glue : '')
      ;
    }
  }
   
  return
    $prepend.
    ($prepend_outer_glue ? $outer_glue : '').
    implode($outer_glue, $output).
    ($append_outer_glue ? $outer_glue : '').
    $append
  ;
}


