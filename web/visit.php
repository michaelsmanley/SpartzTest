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
    <script>
    
    $.ajax({
        type: 'POST',
        url: '/form/',
        data: '{"name":"jonas"}', // or JSON.stringify ({name: 'jonas'}),
        success: function(data) { alert('data: ' + data); },
        contentType: "application/json",
        dataType: 'json'
    });
    
    </script>
</head>
<body>
    <h3>Add a Visit</h3>
    <form id="visit">
        User:
            <select name="user">
                <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u->id; ?>"><?php echo $u->full_name(); ?></option>
                <?php endforeach; ?>
            </select>
        
        <br><br>State:
            <select name="state">
                <?php foreach ($states as $s): ?>
                    <option value="<?php echo $s->state; ?>"><?php echo $s->state; ?></option>
                <?php endforeach; ?>
            </select>
        
        <br><br>City:
            <input name="city">
            
        <input type="submit" value="Submit">
    </form>
<span id="status"></span>
</body>
</html>