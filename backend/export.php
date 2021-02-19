<?php

require_once 'subscription.class.php';

$IdsToExport = $_POST['Id'];
if (!isset($IdsToExport) || !$IdsToExport || !is_array($IdsToExport)) {
    echo 'Nothing to export!';
    return;
}

$subscription = new subscription();
$rows = $subscription->forExport($IdsToExport);

download_send_headers("Email_Subscriptions" . ".csv");
echo array2csv($rows);