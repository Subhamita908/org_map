<?php
require 'config.php';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;
$message_data = null;
$error = null;

// Fetch message data by ID
if ($id > 0) {
    $stmt = $conn->prepare("SELECT id, type, employee_id, message FROM custom_messages WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $message_data = $result->fetch_assoc();
    $stmt->close();

    if (!$message_data) {
        $error = "❌ No message found with ID #{$id}.";
    }
} else {
    $error = "❌ Invalid or missing message ID.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $type = $_POST['type'];
    $employee_id = isset($_POST['employee_id']) && is_numeric($_POST['employee_id']) ? intval($_POST['employee_id']) : null;
    $message = trim($_POST['message']);
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("UPDATE custom_messages SET type = ?, employee_id = ?, message = ? WHERE id = ?");
    $stmt->bind_param("sisi", $type, $employee_id, $message, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: custom_messages.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Custom Message</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            padding: 40px;
        }
        .form-container {
            background: #fff;
            padding: 25px;
            border: 1px solid #ddd;
            border-radius: 6px;
            max-width: 600px;
            margin: auto;
        }
        h2 {
            color: red;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }
        input[type="number"],
        textarea {
            width: 100%;
            padding: 8px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            margin-top: 20px;
            background: red;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: red;
        }
        .error {
            color: red;
            font-weight: bold;
            margin-bottom: 20px;
            background: #ffe5e5;
            padding: 10px;
            border: 1px solid red;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Custom Message</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($message_data): ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $message_data['id'] ?>">

            <label>Occasion Type:</label>
            <input type="radio" name="type" value="birthday" <?= ($message_data['type'] === 'birthday') ? 'checked' : '' ?>> Birthday
            <input type="radio" name="type" value="anniversary" <?= ($message_data['type'] === 'anniversary') ? 'checked' : '' ?>> Work Anniversary

            <label>Employee ID (optional):</label>
            <input type="number" name="employee_id" value="<?= htmlspecialchars($message_data['employee_id'] ?? '') ?>">

            <label>Custom Message:</label>
            <textarea name="message" rows="5" required><?= htmlspecialchars($message_data['message']) ?></textarea>

            <input type="submit" value="Update Message">
        </form>
    <?php endif; ?>
</div>

</body>
</html>
