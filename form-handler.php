<?php

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

function get_post(string $key): string
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : '';
}

$formType = get_post('form_type');

$to = 'sales@landsindiaproperties.com';
$subjectPrefix = 'Raavan Realty Website';

$fullName = get_post('full_name');
$firstName = get_post('first_name');
$lastName = get_post('last_name');
$email = get_post('email');
$phone = get_post('phone');
$helpTopic = get_post('help_topic');
$message = get_post('message');
$plot = get_post('plot');

if ($formType === 'contact') {
    $allowedPlots = [
        'Subbammal Nagar, Perungattur',
        'Sunset Heights Estate',
        'Coastal Serenity Cottage',
    ];

    if ($firstName === '' || $lastName === '' || $email === '' || $phone === '' || $message === '' || $plot === '') {
        http_response_code(400);
        echo 'Please fill all required fields.';
        exit;
    }

    if (!in_array($plot, $allowedPlots, true)) {
        http_response_code(400);
        echo 'Invalid plot selection.';
        exit;
    }

    $fromName = $firstName . ' ' . $lastName;
    $subject = $subjectPrefix . ' - Contact Form';
} elseif ($formType === 'callback') {
    if ($fullName === '' || $email === '' || $phone === '') {
        http_response_code(400);
        echo 'Please fill all required fields.';
        exit;
    }
    $fromName = $fullName;
    $subject = $subjectPrefix . ' - Call Back Request';
} else {
    http_response_code(400);
    echo 'Invalid form submission.';
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo 'Invalid email address.';
    exit;
}

$lines = [];
$lines[] = 'Form Type: ' . $formType;
$lines[] = 'Name: ' . $fromName;
$lines[] = 'Email: ' . $email;
$lines[] = 'Phone: ' . $phone;
if ($plot !== '') {
    $lines[] = 'Plot: ' . $plot;
}
if ($helpTopic !== '') {
    $lines[] = 'Help Topic: ' . $helpTopic;
}
if ($message !== '') {
    $lines[] = '';
    $lines[] = 'Message:';
    $lines[] = $message;
}

$body = implode("\r\n", $lines);

$headers = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';
$headers[] = 'From: ' . $subjectPrefix . ' <no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '>';
$headers[] = 'Reply-To: ' . $fromName . ' <' . $email . '>';

$sent = @mail($to, $subject, $body, implode("\r\n", $headers));

$redirectTo = $_SERVER['HTTP_REFERER'] ?? 'index.html';
$redirectTo .= (strpos($redirectTo, '?') === false ? '?' : '&') . ($sent ? 'sent=1' : 'sent=0');

header('Location: ' . $redirectTo);
exit;
