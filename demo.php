<?php
/**
 * include the envato search functions
 */
include('envato-marketplace-search.php'); 

?><html>
  <head>
  	<meta charset="utf-8">
  	<title>Envato Marketplace Search PHP - Demo</title>
  </head>
  <body>
    
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" id="searchform" method="get" role="search">
    	<div>
    	<label for="search">Search for:</label>
    	<input type="text" id="search" name="search" value="">
    	<input type="submit" value="Search" id="submit">
    	</div>
    </form>
    <?php 
    /**
     * checks for envato_marketplace_search() function and echo's the items
     * $variable set to 'search' which is the name value of the search input.
     * $referral is set to my Marketplace ID
     * a full list of the available options can be found in the readme.txt
     */
    if ( function_exists('envato_marketplace_search') ) 
      envato_marketplace_search( array( 'variable' => 'search', 'referral' => 'valendesigns' ) ); 
    ?>
  </body>
</html>