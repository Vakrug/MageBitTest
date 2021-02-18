<?php

require_once 'config.php';
require_once 'functions.php';

/* @var $config array */

$dbh = new PDO('mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'], $config['user'], $config['password']);

$IdsToExport = $_POST['Id'];
if (!isset($IdsToExport) || !$IdsToExport || !is_array($IdsToExport)) {
    echo 'Nothing to export!';
    return;
}

$sql = 'select Email, AddedAt from subscriptions where Id in (' . implode(',', $IdsToExport) . ')';
$sth = $dbh->prepare($sql);
$sth->execute();
$rows = $sth->fetchAll(PDO::FETCH_ASSOC);

download_send_headers("Email_Subscriptions" . ".csv");
echo array2csv($rows);