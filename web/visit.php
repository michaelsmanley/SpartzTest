<?php
include '../vendor/autoload.php';

ORM::configure('mysql:host=localhost;dbname=spartztest');
ORM::configure('username', 'spartztest');
ORM::configure('password', 'spartztest');

$users = Model::factory('\\MSMP\\Spartz\\User')->find_many();
$states =  ORM::for_table('city')
        ->distinct()
        ->select('state')
        ->order_by_asc('state')
        ->find_many();

?>
<html>
<head>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
</head>
<body>
    <h3>Add a Visit</h3>
    <form id="visit">
        User:
            <select id="uid" name="uid">
                <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u->id; ?>"><?php echo $u->full_name(); ?></option>
                <?php endforeach; ?>
            </select>

        <br><br>State:
            <select id="state" name="state">
                <?php foreach ($states as $s): ?>
                    <option value="<?php echo $s->state; ?>"><?php echo $s->state; ?></option>
                <?php endforeach; ?>
            </select>

        <br><br>City:
            <input id="city" name="city">

        <br><br><input id="visitSubmitButton" type="submit" value="Submit">
    </form>
    <div id="msg_container" style="display:none">
        <span id="msg"></span>
        <a href="#" id="msg_container_clear">[X]</a>
    </div>
    <script>
        $(function() {
            $('#msg_container_clear').click(function() {
               $('#msg_container').hide(); return false;
            });

            $('#visitSubmitButton').click(function() {
                    var set_message = function(message) {
                        var container = $('#msg_container');
                        $(container).find('span#msg').html(message);
                        $(container).show();
                    };

                var city  = $('#city').val();
                var state = $('#state').find(":selected").val();
                var uid   = $('#uid').find(":selected").val();

                $.ajax({
                    type:        'POST',
                    url:         '/v1/users/' + uid + '/visits',
                    data:        JSON.stringify({ "city": city, "state": state }),
                    success:     function(response) { set_message('UPDATE ' + response.status); },
                    error:       function(response) { set_message('UPDATE ERROR'); },
                    contentType: 'application/json',
                    dataType:    'json'
                });
                
                return false;
            });
        });
    </script>
</body>
</html>