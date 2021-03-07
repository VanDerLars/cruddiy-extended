<!doctype html>
<html lang="en">
<head>
    <title>Select Columns</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">

    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>


    <script>
        $('document').ready(function(){

            $('input').bind('input', function() {
                getConfig();
            });

            $('#configuration').bind('blur', function() {
                let conf = $('#configuration').val();
                setConfiguration(JSON.parse(conf));
            });
        });

        function setConfiguration(obj){
            console.log(obj);
            obj.forEach(function(item){

                var input = $("[name='" + item.name + "']");
                console.log(input);

                if ($(input).prop('type') == 'checkbox'){
                    $(input).prop('checked', item.value);
                }else{
                    $(input).val(item.value); 
                }

            })
        }

        function getConfig(){
            var inputs = $('input');
            var validInputs = [];
            inputs.each(function(index){
                if (this.id.length > 1 ){
                    validInputs.push(this);
                }else{
                    //nothing
                }
            });
            let obj = [];

            validInputs.forEach(function(item){
                var name = item.name;
                var val = "";
                if ($(item).prop('type') == 'checkbox'){
                    val = $(item).prop('checked');
                    console.log(val);
                }else{
                    val = $(item).val(); 
                }
                
            
                var itm = {
                "name" : name,
                "value" : val
                };
                obj.push(itm);
            });
            console.log(obj);

            var blkstr = $.map(obj, function(val,index) {                    
                var str = JSON.stringify(val);
                return str;
            }).join(", ");  
            $('#configuration').val("[" + blkstr + "]");
        }
    </script>
</head>
<body>
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-12 mx-auto">
                <div class="text-center">
                    <h4 class="mb-0">All available columns</h4>
                </div>
                <form class="form-horizontal" action="generate.php" method="post">
                    <fieldset>
                        <?php

                        include "app/config.php";

                        function get_primary_keys($table){
                            global $link;
                            $sql = "SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'";
                            $result = mysqli_query($link,$sql);
							$primary_keys = Array();
                            while($row = mysqli_fetch_assoc($result))
                            {
                                $primary_keys[] = $row['Column_name'];
                            }
                            return $primary_keys;
                        }

                        function get_autoincrement_cols($table){
                            global $link;
                            $sql = "DESCRIBE $table";
                            $result = mysqli_query($link,$sql);
							$auto_keys = Array();
                            while($row = mysqli_fetch_assoc($result))
                            {
                                if ($row['Extra'] == 'auto_increment') {
                                    $auto_keys[] = $row['Field'];
                                }
                            }
                            return $auto_keys;
                        }

                        function get_col_types($table,$column){
                            global $link; 
                            $sql = "SHOW FIELDS FROM $table where FIELD ="."'".$column."'";
                            $result = mysqli_query($link,$sql);
                            $row = mysqli_fetch_assoc($result);
                            return $row['Type'] ;
                            mysqli_free_result($result);
                        }

                        function get_foreign_keys($table){
                            global $link;
                            global $db_name;
                            $fks[] = "";
                            $sql = "SELECT k.COLUMN_NAME as 'Foreign Key'
                                    FROM information_schema.TABLE_CONSTRAINTS i
                                    LEFT JOIN information_schema.KEY_COLUMN_USAGE k ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME
                                    WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY' AND i.TABLE_NAME = '$table'";
                            $result = mysqli_query($link,$sql);
                            while($row = mysqli_fetch_assoc($result))
                            {
                                $fks[] = $row['Foreign Key'];
                            }
                            return $fks;
                            mysqli_free_result($result);
                        }

                        if ( isset( $_POST['table'] ) )
                        {
                            foreach ( $_POST['table'] as $table )
                            {
                                $i=0;
                                if (isset($table['tablecheckbox']) && $table['tablecheckbox'] == 1) {
                                    $tablename = $table['tablename'];
                                    $tabledisplay = $table['tabledisplay'];
                                    echo "<div class='text-center my-4'><b>Table: " . $tabledisplay . " (". $tablename .")</b></div>";
                                    $sql = "SHOW columns FROM $tablename";
                                    $primary_keys = get_primary_keys($tablename);
                                    $auto_keys = get_autoincrement_cols($tablename);
                                    $foreign_keys = get_foreign_keys($tablename);

                                    $result = mysqli_query($link,$sql);
                                    while ($column = mysqli_fetch_array($result)) {

                                        $column_type = get_col_types($tablename,$column[0]);

                                        if (in_array ("$column[0]", $primary_keys)) {
                                            $primary = "🔑";
                                            echo '<input type="hidden" name="'.$tablename.'columns['.$i.'][primary]" value="'.$primary.'"/>';
                                        }
                                        else {
                                            $primary = "";
                                        }

                                        if (in_array ("$column[0]", $auto_keys)) {
                                            $auto = "🔒";
                                            echo '<input type="hidden" name="'.$tablename.'columns['.$i.'][auto]" value="'.$auto.'"/>';
                                        }
                                        else {
                                            $auto = "";
                                        }

                                        if (in_array ("$column[0]", $foreign_keys)) {
                                            $fk = "🛅";
                                            echo '<input type="hidden" name="'.$tablename.'columns['.$i.'][fk]" value="'.$fk.'"/>';
                                        }
                                        else {
                                            $fk = "";
                                        }

                                        echo '<div class="row align-items-center mb-2">
                                    <div class="col-2 text-right"
                                        <label class="col-form-label" for="'.$tablename.'">'. $primary . $auto . $fk . $column[0] . ' </label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="hidden" name="'.$tablename.'columns['.$i.'][tablename]" value="'.$tablename.'"/>
                                        <input type="hidden" name="'.$tablename.'columns['.$i.'][tabledisplay]" value="'.$tabledisplay.'"/>
                                        <input type="hidden" name="'.$tablename.'columns['.$i.'][columnname]" value="'.$column[0].'"/>
                                        <input type="hidden" name="'.$tablename.'columns['.$i.'][columntype]" value="'.$column_type.'"/>
                                        <input id="textinput_'.$tablename. '"name="'. $tablename. 'columns['.$i.'][columndisplay]" type="text" placeholder="Display field name in frontend" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="checkbox"  name="'.$tablename.'columns['.$i.'][columnvisible]" id="checkboxes-0" value="1" checked="True">
                                Visible in overview?</div>
                     </div>';
                                        $i++;
                                    }
                                }
                            }
                        }
                        ?>

                        <div class="row">
                            <div class="col-12 offset-5">
                                <label class="col-form-label mt-3" for="singlebutton"></label>
                                <button type="submit" id="singlebutton" name="singlebutton" class="btn btn-primary">Generate pages</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <hr>
                <div class="text-center pt-5">
                    <h4>Save and Load Configuration</h4>
                </div>
                <div class="form-group">
                    <label class="col-form-label" for="configuration">Copy and paste the config here:</label>
                        <textarea id="configuration" name="configuration" class="form-control input-md"></textarea>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
</html>
