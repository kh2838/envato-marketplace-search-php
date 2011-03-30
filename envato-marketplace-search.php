<?php
/**
 * Plugin Name: Envato Marketplace Search PHP
 * Plugin URI: https://github.com/valendesigns/envato-marketplace-search-php
 * Description: Retrieves items from Envato Marketplace's using the search API and displays the results as an unordered lists of linked 80px thumbnails.
 * Version: 1.0.0
 * Author: Derek Herman
 * Author URI: http://valendesigns.com
 * License: GPLv2
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/**
 * Get the marketplace search results
 *
 * @since 1.0.0
 * @access public
 *
 * @uses ems_parse_args()
 * @uses extract()
 * @uses isset()
 * @uses trim()
 * @uses mb_convert_encoding()
 * @uses htmlentities()
 * @uses str_replace()
 * @uses ems_get_json_contents()
 * @uses json_decode()
 *
 * @param array $args The argument array.
 *    - @param integer $limit The returned results limit (max is 50): default 10
 *    - @param string $site The marketplace site (e.g. activeden, audiojungle)
 *    - @param string $type The item type (e.g. site-templates, music, graphics) 
 *        - For a full list of types, look at the search select box values on the particular marketplace
 *    - @param string $query The search query: default is empty
 *    - @param string $variable The search query variable used in the form: default is 's'
 *    - @param string $referral Your marketplace referral ID (e.g. valendesigns)
 *    - @param bool $echo Echo or return output: default true
 * @return null|string The output, if echo is set to false.
 */
function envato_marketplace_search( $args = '' )
{
  // default arguments
  $defaults = array(
		'limit'     => 10, 
		'site'      => '', 
		'type'      => '',
		'query'     => '',
		'variable'  => 's',
		'referral'  => '',
		'echo'      => true
	);
  
  // parse incomming $args into an array and merge it with $defaults
	$args = ems_parse_args( $args, $defaults );
	
	// declare each item in $args as its own variable
	extract( $args, EXTR_SKIP );
	
	// setup query
	if ( !$query )
    $query = ( isset( $_REQUEST[$variable] ) ) ? trim( $_REQUEST[$variable] ) : '';
  
  // combat XSS attempts on search query
  $query = mb_convert_encoding( $query, 'UTF-8', 'UTF-8' );
  $query = htmlentities( $query, ENT_QUOTES, 'UTF-8');
  
  // missing query return false
  if ( !$query )
    return;
	
	// set empty return variable
  $return = '';
  
  // build search expresssion
  $query = str_replace( ' ', '|', $query );
  
  // build API url
  $json_url = "http://marketplace.envato.com/api/edge/search:{$site},{$type},{$query}.json";
  
  // get API JSON results
  $json_contents = ems_get_json_contents( $json_url ); 
  
  // if get_json_data() returns data
  if ( $json_contents ) 
  {
    // test for the json_decode() function
    // PHP 4 backwards compatibility
    if ( !function_exists('json_decode') ) 
    {
      include( 'JSON.php' );
      function json_decode( $data, $output_mode=false ) 
      {
        $param = $output_mode ? 16 : null;
        $json = new Services_JSON($param);
        return $json->decode($data);
      }
    }
  
    // decode json data
    $json_data = json_decode( $json_contents, true );
    
    // set count to zero
    $count = 0;
    
    // loop through results
    foreach( $json_data['search'] as $item ) 
    {
      // file type not item, continue and preserve loop count
      if ( $item['type'] != 'item' )
        continue;
      
      // stop adding results to the content if count is >= limit
      if ( $count >= $limit )
        continue;

      // set variables
      $url    = $item['url'];
      $image  = $item['item_info']['thumbnail'];
      $title  = $item['item_info']['item'];
      $ref    = ( $referral ) ? '?ref='.$referral : '';
      
      // set return data 
      $return .= "<li><a href='{$url}{$ref}' rel='nofollow external'><img src='{$image}' alt='{$title}' height='80' width='80' /></a></li>";
      
      // increment count total
      $count++;
    }
    
    // wrap results in a UL
    if ( $return )
      $return = '<ul id="envato-marketplace-search">'.$return.'</ul>';
  }
  
  if ( !$echo )
		return $return;
  
  echo $return;
}

/**
 * Get the contents of a remote url with a curl fallback
 *
 * @since 1.0.0
 * @access public
 *
 * @uses file_get_contents()
 * @uses ems_get_json_contents_via_curl()
 *
 * @param string $address The remote address of the JSON file
 * @return null|string Returns the contents of the file 
 */
function ems_get_json_contents( $address )
{
  // no addredd return false
  if ( !$address )
    return;
  
  // grab the file contents 
  $data = @file_get_contents( $address );
  
  // no data use curl
  if ( $data === false )
    $data = ems_get_json_contents_via_curl( $address );
  
  // return data
  if ( $data )
    return $data;
  
  return false;
}

/**
 * Get the contents of a remote usinf curl
 *
 * @since 1.0.0
 * @access public
 *
 * @uses curl_init()
 * @uses curl_setopt()
 * @uses curl_exec()
 * @uses curl_getinfo()
 * @uses curl_close()
 *
 * @param string $address The remote address of the JSON file
 * @return null|string Returns the contents of the file 
 */
function ems_get_json_contents_via_curl( $address )
{
  // no addredd return false
  if ( !$address )
    return;
  
  // grab the file contents
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $address);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
  $data = curl_exec($ch);
  $info = curl_getinfo($ch);
  curl_close($ch);
  
  // HTTP is 200 (success) return data
  if ( $info['http_code'] == 200 ) 
    return $data;
  
  return false;
}

/**
 * Merge user defined arguments into defaults array.
 *
 * @since 1.0.0
 * @access public
 *
 * @uses ems_parse_str()
 *
 * @param string|array $args Value to merge with $defaults
 * @param array $defaults Array that serves as the defaults.
 * @return array Merged user defined values with defaults.
 */
function ems_parse_args( $args, $defaults = '' ) {
	if ( is_object( $args ) )
		$r = get_object_vars( $args );
	elseif ( is_array( $args ) )
		$r =& $args;
	else
		ems_parse_str( $args, $r );

	if ( is_array( $defaults ) )
		return array_merge( $defaults, $r );
	return $r;
}

/**
 * Parses a string into variables to be stored in an array.
 *
 * Uses {@link http://www.php.net/parse_str parse_str()} and stripslashes if
 * {@link http://www.php.net/magic_quotes magic_quotes_gpc} is on.
 *
 * @since 1.0.0
 * @access public
 *
 * @uses ems_stripslashes_deep()
 *
 * @param string $string The string to be parsed.
 * @param array $array Variables will be stored in this array.
 */
function ems_parse_str( $string, &$array ) 
{
	parse_str( $string, $array );
	if ( get_magic_quotes_gpc() )
		$array = ems_stripslashes_deep( $array );
		
	return $array;
}

/**
 * Navigates through an array and removes slashes from the values.
 *
 * If an array is passed, the array_map() function causes a callback to pass the
 * value back to the function. The slashes from this value will removed.
 *
 * @since 1.0.0
 * @access public
 *
 * @param array|string $value The array or string to be striped.
 * @return array|string Stripped array (or string in the callback).
 */
function ems_stripslashes_deep($value) 
{
	if ( is_array($value) ) {
		$value = array_map('ems_stripslashes_deep', $value);
	} elseif ( is_object($value) ) {
		$vars = get_object_vars( $value );
		foreach ($vars as $key=>$data) {
			$value->{$key} = ems_stripslashes_deep( $data );
		}
	} else {
		$value = stripslashes($value);
	}

	return $value;
}