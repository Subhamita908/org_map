<?php
/**
 * Fetches the most recent custom message for a given occasion type.
 * Prioritizes employee-specific messages and falls back to general messages.
 *
 * @param PDO $pdo             The PDO database connection
 * @param string $type         The type of message ('birthday' or 'anniversary')
 * @param int|null $employee_id Optional employee ID for personalized messages
 * @return string|null         The message content or null if not found
 */
function getCustomMessage(PDO $pdo, string $type, ?int $employee_id = null): ?string {
    // Define queries and parameters
    $queries = [
        'employee_specific' => [
            'sql' => "SELECT message FROM custom_messages WHERE type = ? AND employee_id = ? ORDER BY id DESC LIMIT 1",
            'params' => [$type, $employee_id]
        ],
        'general' => [
            'sql' => "SELECT message FROM custom_messages WHERE type = ? AND employee_id IS NULL ORDER BY id DESC LIMIT 1",
            'params' => [$type]
        ]
    ];

    foreach ($queries as $query) {
        try {
            // Prepare the SQL statement
            $stmt = $pdo->prepare($query['sql']);
            if (!$stmt) {
                error_log("Failed to prepare SQL: {$query['sql']}");
                continue;
            }

            // Execute with parameters
            $stmt->execute($query['params']);

            // Fetch the result
            $msg = $stmt->fetchColumn(); // Gets the first column (message)
            if ($msg !== false && !empty($msg)) {
                return $msg; // Return on first match
            }
        } catch (PDOException $e) {
            error_log("PDO Query Error: " . $e->getMessage());
            continue;
        }
    }

    return null; // No message found
}
?>