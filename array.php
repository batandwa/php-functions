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
