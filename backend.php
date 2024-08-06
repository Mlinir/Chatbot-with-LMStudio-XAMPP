<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require 'vendor/autoload.php';
require 'config.php';

$input = json_decode(file_get_contents('php://input'), true);
$conversation = $input['conversation'];
$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['conversation'])) {
    $_SESSION['conversation'] = [];
}
$_SESSION['conversation'] = $conversation;

$yourApiKey = getenv('lm-studio');

$client = OpenAI::factory()
    ->withApiKey($yourApiKey)
    ->withBaseUri('http://localhost:1234/v1')
    ->withHttpClient(new \GuzzleHttp\Client(['timeout' => 120]))
    ->make();

try {
    $result = $client->chat()->create([
        'model' => 'SanctumAI/Meta-Llama-3-8B-Instruct-GGUF',
        'messages' => $_SESSION['conversation'],
    ]);

    $response = $result->choices[0]->message->content;
    $_SESSION['conversation'][] = ['role' => 'assistant', 'content' => $response];
    $id = $_SESSION['id'];
    $content = json_encode($_SESSION['conversation']);

    if (!($id)) {
        $stmt = $conn->prepare("INSERT INTO log_entries (user_id, content, created_date) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $user_id, $content);
        $stmt->execute();
        $_SESSION['id'] = $stmt->insert_id;
    } else {
        $stmt = $conn->prepare("UPDATE log_entries SET content = ?, created_date = NOW() WHERE id = ?");
        $stmt->bind_param("si", $content, $id);
        $stmt->execute();
    }

    $stmt->close();

    echo json_encode(['response' => $response]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>