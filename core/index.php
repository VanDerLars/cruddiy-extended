<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Connect to database</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">

    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/rollups/aes.js" integrity="sha256-/H4YS+7aYb9kJ5OKhFYPUjSJdrtV6AeyJOtTkw6X72o=" crossorigin="anonymous"></script>

    <style type="text/css">
                table tr td:last-child a{
            margin-right: 15px;
        }
    </style>
    <script>
        $('document').ready(function(){

            loadFromURL();
            getConfiguration();

            $('input').bind('input', function() {
                getConfiguration();
            });
            $('#configuration').bind('blur', function() {
                let conf = $('#configuration').val();
                setConfiguration(JSON.parse(conf));
            });
        });

        function getConfiguration(){
            let obj = {};
            let pwEncrypt = CryptoJS.AES.encrypt($("#password").val(), '51mple_encrypt10n_5ecret_n0t_very_5ecure_but_better_than_n0th1ng');
            // pwEncrypt = encodeURIComponent(pwEncrypt);

            obj.server = $("#server").val();
            obj.database = $("#database").val();
            obj.username = $("#username").val();
            obj.password = pwEncrypt.toString();
            obj.numrecordsperpage = $("#numrecordsperpage").val();
            $("#configuration").val(JSON.stringify(obj));
            setURLParam(JSON.stringify(obj));
        }

        function loadFromURL(){
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
            const q = urlParams.get('q')
            let J = JSON.parse(q);
            if(J!= undefined){
                setConfiguration(J);
            }
        }

        function setURLParam(q){
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('q', q);
            $("#url_text").val(window.location.hostname + window.location.pathname + "?" + urlParams);
        }
        function setConfiguration(obj){
            console.log(obj);
            var decrPw = CryptoJS.AES.decrypt(obj.password, '51mple_encrypt10n_5ecret_n0t_very_5ecure_but_better_than_n0th1ng');
            var pwDecrypt = decrPw.toString(CryptoJS.enc.Utf8);

            $("#server").val(obj.server);
            $("#database").val(obj.database);
            $("#username").val(obj.username);
            $("#password").val(pwDecrypt);
            $("#numrecordsperpage").val(obj.numrecordsperpage);
        }

    </script>
</head>
<div class="container">
    <div class="row">
        <div class="col-md-4 mx-auto">
            <form class="form-group-row" action="relations.php" method="post" >
                <fieldset>

                    <!-- Form Name -->
                    <div class="text-center pt-5">
                        <h4>Enter database information</h4>
                    </div>

                    <!-- Text input-->
                    <div class="form-group">
                        <label class="col-form-label" for="textinput" onblur="getConfiguration()">Server</label>
                            <input id="server" name="server" type="text" placeholder="localhost" class="form-control ">
                    </div>

                    <!-- Text input-->
                    <div class="form-group">
                        <label class="col-form-label" for="textinput" onblur="getConfiguration()">Database</label>
                            <input id="database" name="database" type="text" placeholder="" class="form-control input-md">
                    </div>
                    <!-- Text input-->
                    <div class="form-group">
                        <label class="col-form-label" for="textinput" onblur="getConfiguration()">Username</label>
                            <input id="username" name="username" type="text" placeholder="" class="form-control input-md">
                    </div>

                    <!-- Password input-->
                    <div class="form-group">
                        <label class="col-form-label" for="passwordinput" onblur="getConfiguration()">Password</label>
                            <input id="password" name="password" type="password" placeholder="" class="form-control input-md">
                    </div>

                    <!-- Number records per page-->
                    <div class="form-group">
                            <label class="col-form-label" for="recordsperpage">Items per generated page</label>
                            <input id="numrecordsperpage" name="numrecordsperpage" type="number" min="1" max="1000" placeholder="Number of items per page" class="form-control input-md" value="10">
                    </div>


                    <div class="form-group">
                        <label class="col-form-label" for="index"></label>
                            <button id="index" name="index" class="btn btn-primary">Submit</button>
                    </div>
                </fieldset>
            </form>
            
            <fieldset>
                <div class="text-center pt-5">
                    <h4>Save and Load Configuration</h4>
                </div>
                <div class="form-group">
                    <label class="col-form-label" for="configuration">Copy and paste the config here:</label>
                        <textarea id="configuration" name="configuration" class="form-control input-md"></textarea>
                </div>
                <div class="form-group">
                    <label class="col-form-label" for="url_text">Or save the URL:</label>
                        <input id="url_text" name="userurl_textname" type="text" placeholder="" class="form-control input-md">
                </div>
            </fieldset>
        </div>
    </div>
</div>
</body>
</html>
