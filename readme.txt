Below is a list of the available options for the envato_marketplace_search() function (all are optional and have a default value). The options can be passed in as an array() or variable string like 'limit=10&site=themeforest'.

@param integer $limit    The returned results limit (max is 50): default 10
@param string  $site     The marketplace site (e.g. activeden, audiojungle)
@param string  $type     The item type (e.g. site-templates, music, graphics) For a full list of types, look at the search select box values on the particular marketplace
@param string  $query    The search query: default is empty
@param string  $variable The search query variable used in the form: default is 's'
@param string  $referral Your marketplace referral ID (e.g. valendesigns)
@param bool    $echo     Echo or return output: default true