<?php

require_once 'config.php';
require_once 'functions.php';

/* @var $config array */

header('Content-Type: application/json');

$error = false;
$errorMessages = [];

$email = filter_input(INPUT_POST, 'email');
$tos_agreed = filter_input(INPUT_POST, 'tos_agreed', FILTER_VALIDATE_BOOLEAN);

if (!$email) {
    $error = true;
    $errorMessages[] = 'Email address is required';
} else {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $error = true;
        $errorMessages[] = 'Please provide a valid e-mail address';
    } else {
        if (endsWith(strtolower($email), '.co')) {
            $error = true;
            $errorMessages[] = 'We are not accepting subscriptions from Colombia emails';
        }
    }
}

if (!$tos_agreed) {
    $error = true;
    $errorMessages[] = 'You must accept the terms and conditions';
}

if ($error) {
    echo json_encode([
        'error' => $error,
        'errorMessages' => $errorMessages
    ]);
    return;
}

$provider = get_string_between($email, '@', '.');

$dbh = new PDO('mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'], $config['user'], $config['password']);
$sql = 'insert into subscriptions (Email, Provider, AddedAt) values (:email, :provider, NOW())';
$sth = $dbh->prepare($sql);
$success = $sth->execute([
    ':email' => $email,
    ':provider' => $provider
]);

if (!$success) {
    $error = true;
    $errorMessages[] = 'Failed to insert record to database';
}

echo json_encode([
    'error' => $error,
    'errorMessages' => $errorMessages
]);