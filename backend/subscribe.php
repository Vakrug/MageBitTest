<?php

require_once 'subscription.class.php';

header('Content-Type: application/json');

$email = filter_input(INPUT_POST, 'email');
$tos_agreed = filter_input(INPUT_POST, 'tos_agreed', FILTER_VALIDATE_BOOLEAN);

$subscription = new subscription($email, $tos_agreed);
if (!$subscription->validate()) {
    echo json_encode([
        'error' => true,
        'errorMessages' => $subscription->errorMessages
    ]);
    return;
}

$success = $subscription->save();

echo json_encode([
    'error' => !$success,
    'errorMessages' => $subscription->errorMessages
]);