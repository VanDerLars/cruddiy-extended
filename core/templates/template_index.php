<?php

$indexfile = <<<'EOT'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/6b773fe9e4.js" crossorigin="anonymous"></script>
    <link href="//cdn.jsdelivr.net/npm/featherlight@1.7.14/release/featherlight.min.css" type="text/css" rel="stylesheet" />

    <style type="text/css">
        .page-header h2{
            margin-top: 0;
        }
        table tr td:last-child a{
            margin-right: 5px;
        }
        body {
            font-size: 14px;
        }
        .featherlight-content iframe {
            width: 100%;
            height: 100%;
        }
        .featherlight-content {
            width: 60%;
            max-width: 960px;
            min-width: 480px;
            height: 80%;
            border-radius: 10px;
        }
        form {
            margin: 50px;
        }
    </style>
</head>
<body>
    <section class="pt-5">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <a href="index.php" class="btn btn-secondary float-left mr-4"><i class='far fa-arrow-alt-circle-left'></i> Back</a>
                    <div class="page-header clearfix">
                        <h2 class="btn-toolbar">{TABLE_DISPLAY} Details</h2>
                        <a href="#" data-featherlight="{TABLE_NAME}-create.php" class="btn btn-success float-right"><i class='far fa-file'></i> Add New Record</a>
                        <a href="{TABLE_NAME}-index.php" class="btn btn-warning float-right mr-2"><i class='far fa-share-square'></i> Reset View</a>
                        <a href="../api/{TABLE_NAME}" target="_blank" class="btn btn-primary float-right mr-2"><i class='far fa-file-code'></i> API-Endpoint</a>
                    </div>

                    <div class="form-row">
                        <form action="{TABLE_NAME}-index.php" method="get">
                        <div class="col">
                          <input type="text" class="form-control" placeholder="Search this table" name="search">
                        </div>
                    </div>
                        </form>
                    <br>

                    <?php
                    // Include config file
                    require_once "config.php";

                    //Get current URL and parameters for correct pagination
                    $protocol = $_SERVER['SERVER_PROTOCOL'];
                    $domain     = $_SERVER['HTTP_HOST'];
                    $script   = $_SERVER['SCRIPT_NAME'];
                    $parameters   = $_SERVER['QUERY_STRING'];
                    $protocol=strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https')
                                === FALSE ? 'http' : 'https';
                    $currenturl = $protocol . '://' . $domain. $script . '?' . $parameters;

                    //Pagination
                    if (isset($_GET['pageno'])) {
                        $pageno = $_GET['pageno'];
                    } else {
                        $pageno = 1;
                    }

                    //$no_of_records_per_page is set on the index page. Default is 10.
                    $offset = ($pageno-1) * $no_of_records_per_page;

                    $total_pages_sql = "SELECT COUNT(*) FROM {TABLE_NAME}";
                    $result = mysqli_query($link,$total_pages_sql);
                    $total_rows = mysqli_fetch_array($result)[0];
                    $total_pages = ceil($total_rows / $no_of_records_per_page);
                    
                    //Column sorting on column name
                    $orderBy = array('{COLUMNS}'); 
                    $order = '{COLUMN_ID}';
                    if (isset($_GET['order']) && in_array($_GET['order'], $orderBy)) {
                            $order = $_GET['order'];
                        }

                    //Column sort order
                    $sortBy = array('asc', 'desc'); $sort = 'desc';
                    if (isset($_GET['sort']) && in_array($_GET['sort'], $sortBy)) {                                                                    
                          if($_GET['sort']=='asc') {                                                                                                                            
                            $sort='desc';
                            }                                                                                   
                    else {
                        $sort='asc';
                        }                                                                                                                           
                    }

                    // Attempt select query execution
                    $sql = "{INDEX_QUERY} ORDER BY $order $sort LIMIT $offset, $no_of_records_per_page";
                    $count_pages = "{INDEX_QUERY}";

                    
                    if(!empty($_GET['search'])) {
                        $search = ($_GET['search']);
                        $sql = "SELECT * FROM {TABLE_NAME}
                            WHERE CONCAT ({INDEX_CONCAT_SEARCH_FIELDS})
                            LIKE '%$search%'
                            ORDER BY $order $sort 
                            LIMIT $offset, $no_of_records_per_page";
                        $count_pages = "SELECT * FROM {TABLE_NAME}
                            WHERE CONCAT ({INDEX_CONCAT_SEARCH_FIELDS})
                            LIKE '%$search%'
                            ORDER BY $order $sort";
                    }
                    else {
                        $search = "";
                    }

                    if($result = mysqli_query($link, $sql)){
                        if(mysqli_num_rows($result) > 0){
                            if ($result_count = mysqli_query($link, $count_pages)) {
                               $total_pages = ceil(mysqli_num_rows($result_count) / $no_of_records_per_page);
                           }
                            $number_of_results = mysqli_num_rows($result_count);
                            echo " " . $number_of_results . " results - Page " . $pageno . " of " . $total_pages;

                            echo "<table class='table table-bordered table-striped'>";
                                echo "<thead>";
                                    echo "<tr>";
                                        {INDEX_TABLE_HEADERS}
                                        echo "<th>Action</th>";
                                    echo "</tr>";
                                echo "</thead>";
                                echo "<tbody>";
                                while($row = mysqli_fetch_array($result)){
                                    echo "<tr>";
                                    {INDEX_TABLE_ROWS}
                                        echo "<td>";
                                            echo "<a href='' data-featherlight='{TABLE_NAME}-read.php?{COLUMN_ID}=". $row['{COLUMN_NAME}'] ."' title='View Record' data-toggle='tooltip'><i class='far fa-eye'></i></a>";
                                            echo "<a href='' data-featherlight='{TABLE_NAME}-update.php?{COLUMN_ID}=". $row['{COLUMN_NAME}'] ."' title='Update Record' data-toggle='tooltip'><i class='far fa-edit'></i></a>";
                                            echo "<a href='' data-featherlight='{TABLE_NAME}-delete.php?{COLUMN_ID}=". $row['{COLUMN_NAME}'] ."' title='Delete Record' data-toggle='tooltip'><i class='far fa-trash-alt'></i></a>";
                                        echo "</td>";
                                    echo "</tr>";
                                }
                                echo "</tbody>";
                            echo "</table>";
?>
                                <ul class="pagination" align-right>
                                <?php
                                    $new_url = preg_replace('/&?pageno=[^&]*/', '', $currenturl);
                                 ?> 
                                    <li class="page-item"><a class="page-link" href="<?php echo $new_url .'&pageno=1' ?>">First</a></li>
                                    <li class="page-item <?php if($pageno <= 1){ echo 'disabled'; } ?>">
                                        <a class="page-link" href="<?php if($pageno <= 1){ echo '#'; } else { echo $new_url ."&pageno=".($pageno - 1); } ?>">Prev</a>
                                    </li>
                                    <li class="page-item <?php if($pageno >= $total_pages){ echo 'disabled'; } ?>">
                                        <a class="page-link" href="<?php if($pageno >= $total_pages){ echo '#'; } else { echo $new_url . "&pageno=".($pageno + 1); } ?>">Next</a>
                                    </li>
                                    <li class="page-item <?php if($pageno >= $total_pages){ echo 'disabled'; } ?>">
                                        <a class="page-item"><a class="page-link" href="<?php echo $new_url .'&pageno=' . $total_pages; ?>">Last</a>
                                    </li>
                                </ul>
<?php
                            // Free result set
                            mysqli_free_result($result);
                        } else{
                            echo "<p class='lead'><em>No records were found.</em></p>";
                        }
                    } else{
                        echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
                    }

                    // Close connection
                    mysqli_close($link);
                    ?>
                </div>
            </div>
        </div>
    </section>
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<script src="//cdn.jsdelivr.net/npm/featherlight@1.7.14/release/featherlight.min.js" type="text/javascript" charset="utf-8"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>
EOT;

// ------------------------------------------------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------------------------------------------------

?>
