<?php

$indexapi = <<<'EOT'
<?php
// PARAMETER

// Include config file
require_once "../../app/config.php";

//Get current URL and parameters for correct pagination
$protocol = $_SERVER['SERVER_PROTOCOL'];
$domain     = $_SERVER['HTTP_HOST'];
$script   = $_SERVER['SCRIPT_NAME'];
$parameters   = $_SERVER['QUERY_STRING'];
$protocol=strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https')
            === FALSE ? 'http' : 'https';
$currenturl = $protocol . '://' . $domain. $script . '?' . $parameters;



$search = array();
$valid  = array('{COLUMNS}');

$cols = array_intersect( $valid, array_keys( $_GET ) );
foreach( $cols as $col )
{
    $search[] = $col . ' = ' . mysqli_real_escape_string($link, $_GET[ $col ] );
}

if (count($search) > 0){
    $sql = 'SELECT * FROM `{TABLE_NAME}` WHERE ' . implode( ' AND ', $search );
}else{
    $sql = 'SELECT * FROM `{TABLE_NAME}`';
}

    header("Access-Control-Allow-Origin: *");

    if($result = mysqli_query($link, $sql)){

        $rows = array();
            while($r = mysqli_fetch_assoc($result)) {
                $rows['{TABLE_NAME}'][] = $r;
            }
        print json_encode($rows['{TABLE_NAME}']);

        // Free result set
        mysqli_free_result($result);

    }else{
            print 'ERROR';
    }

    // Close connection
    mysqli_close($link);
    ?>
EOT;


// ------------------------------------------------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------------------------------------------------

?>
