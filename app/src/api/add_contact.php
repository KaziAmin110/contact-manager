<?php
require_once '../vendor/autoload.php';
require_once './internal/database.php';
require_once './internal/contact_manager.php';
require_once './internal/jwt.php';
require_once './internal/types.php';

$loggedInUser = extractLoggedInUser();

$input = file_get_contents('php://input');

$mapper = (new \JsonMapper\JsonMapperFactory())->bestFit();

$get_contact_payload = $mapper->mapToClassFromString($input, AddContactPayload::class);

// Validate the mapped data
if (!isset($get_contact_payload->contact)) {
    http_response_code(400);
    echo json_encode(['error' => 'The contact field is required.']);
    exit;
}

if (!isset($get_contact_payload->contact->first_name)) {
    http_response_code(400);
    echo json_encode(['error' => 'The first_name field is required.']);
    exit;
}

try {
    $database = new Database();
    $contact_manager = new ContactManager($database);

    $contact = $contact_manager->createContact($loggedInUser->user_id, $get_contact_payload->contact);
} finally {
    $database->closeConnection();
}

http_response_code(200);
echo json_encode(['contact' => $contact]);

?>