<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function sendJson(array $payload, int $statusCode = 200): void
{
	http_response_code($statusCode);
	echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
	exit;
}

function getRequestData(): array
{
	$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

	if (stripos($contentType, 'application/json') === false) {
		return $_POST;
	}

	$body = file_get_contents('php://input');
	if ($body === false || trim($body) === '') {
		return [];
	}

	$decoded = json_decode($body, false, 512, JSON_THROW_ON_ERROR);
	if (!$decoded instanceof stdClass) {
		throw new InvalidArgumentException('The JSON body must be an object.');
	}

	return (array) $decoded;
}

function requireMethod(string $expectedMethod): void
{
	$actualMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

	if ($actualMethod !== $expectedMethod) {
		header('Allow: ' . $expectedMethod);
		sendJson(['error' => 'Method not allowed.'], 405);
	}
}

try {
	$requestData = getRequestData();
	$action = (string) ($_GET['action'] ?? $requestData['action'] ?? '');

	switch ($action) {
		case 'health':
			requireMethod('GET');
			sendJson(['ok' => true]);

		case 'login':
		case 'signup':
			requireMethod('POST');
			sendJson(['error' => 'This handler has not been implemented yet.'], 501);

		default:
			sendJson(['error' => 'Unknown action.'], 404);
	}
} catch (JsonException | InvalidArgumentException $exception) {
	sendJson(['error' => $exception->getMessage()], 400);
} catch (Throwable $exception) {
	error_log($exception->getMessage());
	sendJson(['error' => 'An unexpected server error occurred.'], 500);
}
