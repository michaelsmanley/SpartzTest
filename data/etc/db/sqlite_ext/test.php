<?php

$db = new PDO('sqlite::memory:');
echo "1: ";
var_dump($db);
echo "------\n";

$res1 = $db->query("SELECT load_extension('libsqlitefunctions.dylib');");
echo "1: ";
var_dump($res1);
echo "------\n";

$res2 = $db->query("SELECT cos(radians(45));");
echo "2: ";
var_dump($res2);
echo "------\n";

$db = new SQLite3(':memory:');
$db->loadExtension('libsqlitefunctions.dylib');
$results = $db->query('SELECT cos(radians(45));');
while ($row = $results->fetchArray()) {
    var_dump($row);
}
