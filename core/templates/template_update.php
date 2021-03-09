<?php

$updatefile = <<<'EOT'
<?php
// Include config file
require_once "config.php";

// Define variables and initialize with empty values
{CREATE_RECORDS}
{CREATE_ERR_RECORDS}

// Processing form data when form is submitted
if(isset($_POST["{COLUMN_ID}"]) && !empty($_POST["{COLUMN_ID}"])){
    // Get hidden input value
    ${COLUMN_ID} = $_POST["{COLUMN_ID}"];

        // Prepare an update statement
        
        {CREATE_POST_VARIABLES}

        $dsn = "mysql:host=$db_server;dbname=$db_name;charset=utf8mb4";
        $options = [
          PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
          PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
        ];
        try {
          $pdo = new PDO($dsn, $db_user, $db_password, $options);
        } catch (Exception $e) {
          error_log($e->getMessage());
          exit('Something weird happened');
        }
        $stmt = $pdo->prepare("UPDATE {TABLE_NAME} SET {UPDATE_SQL_PARAMS} WHERE {UPDATE_SQL_ID}");

        if(!$stmt->execute([ {UPDATE_SQL_COLUMNS}  ])) {
                echo "Something went wrong. Please try again later.";
                header("location: error.php");
            } else{
                $stmt = null;
                header("location: {TABLE_NAME}-index.php");
            }
} else {
    // Check existence of id parameter before processing further
	$_GET["{COLUMN_ID}"] = trim($_GET["{COLUMN_ID}"]);
    if(isset($_GET["{COLUMN_ID}"]) && !empty($_GET["{COLUMN_ID}"])){
        // Get URL parameter
        ${COLUMN_ID} =  trim($_GET["{COLUMN_ID}"]);

        // Prepare a select statement
        $sql = "SELECT * FROM {TABLE_NAME} WHERE {COLUMN_ID} = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            // Set parameters
            $param_id = ${COLUMN_ID};

            // Bind variables to the prepared statement as parameters
			if (is_int($param_id)) $__vartype = "i";
			elseif (is_string($param_id)) $__vartype = "s";
			elseif (is_numeric($param_id)) $__vartype = "d";
			else $__vartype = "b"; // blob
			mysqli_stmt_bind_param($stmt, $__vartype, $param_id);

            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);

                if(mysqli_num_rows($result) == 1){
                    /* Fetch result row as an associative array. Since the result set
                    contains only one row, we don't need to use while loop */
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                    // Retrieve individual field value

                    {UPDATE_COLUMN_ROWS}

                } else{
                    // URL doesn't contain valid id. Redirect to error page
                    header("location: error.php");
                    exit();
                }

            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }

        // Close statement
        mysqli_stmt_close($stmt);

    }  else{
        // URL doesn't contain id parameter. Redirect to error page
        header("location: error.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Record</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <style>
        form {margin: 50px;}
    </style>
</head>
<body>
    <section class="pt-5">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 mx-auto">
                    <div class="page-header">
                        <h2>Update Record</h2>
                    </div>
                    <p>Please edit the input values and submit to update the record.</p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">

                        {CREATE_HTML}

                        <input type="hidden" name="{COLUMN_ID}" value="<?php echo ${COLUMN_ID}; ?>"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="{TABLE_NAME}-index.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
</body>
</html>

EOT;

// ------------------------------------------------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------------------------------------------------

?>
