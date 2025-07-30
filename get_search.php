<?php 
require 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search by Hierarchy Level</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        select, button {
            padding: 8px;
            font-size: 16px;
        }

        table {
            border-collapse: collapse;
            margin-top: 20px;
            width: 80%;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ccc;
        }

        th {
            background-color: #f2f2f2;
        }

        h3 {
            margin-top: 40px;
            color: #333;
        }

        .no-data {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<h2>Search Employees from Selected Level to L8</h2>

<form method="POST">
    <label for="level">Select Hierarchy Level:</label>
    <select name="level" id="level" required>
        <option value="">-- Select Level --</option>
        <option value="L2">L2</option>
        <option value="L3">L3</option>
        <option value="L4">L4</option>
        <option value="L5">L5</option>
        <option value="L6">L6</option>
        <option value="L7">L7</option>
        <option value="L8">L8</option>
    </select>

    <button type="submit" name="search">Search</button>
</form>
<?php
require 'config.php'; // Ensure $pdo is available

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['search'])) {
    $selectedLevel = $_POST['level'];

    // Define full level hierarchy from highest to lowest
    $levels = ["L8", "L7", "L6", "L5", "L4", "L3", "L2"];
    $startIndex = array_search($selectedLevel, $levels);

    if ($startIndex !== false && $startIndex > 0) {
        // Get all levels higher than selected (before its index)
        $levels_to_show = array_slice($levels, 0, $startIndex);

        // Build placeholders (?, ?, ?, ...)
        $placeholders = implode(',', array_fill(0, count($levels_to_show), '?'));

        // Prepare SQL with FIELD to enforce custom order
        $sql = "SELECT name, designation, hierarchy_level 
                FROM employees 
                WHERE hierarchy_level IN ($placeholders) 
                ORDER BY FIELD(hierarchy_level, $placeholders)";

        // Merge params twice for IN and FIELD
        $params = array_merge($levels_to_show, $levels_to_show);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            echo "<h3>Employees from levels higher than $selectedLevel (Sorted High → Low)</h3>";
            echo "<table border='1' cellpadding='8'>
                    <tr>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Hierarchy Level</th>
                    </tr>";
            while ($row = $stmt->fetch()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['name']) . "</td>
                        <td>" . htmlspecialchars($row['designation']) . "</td>
                        <td>" . htmlspecialchars($row['hierarchy_level']) . "</td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No employees found for levels higher than $selectedLevel.</p>";
        }
    } else {
        echo "<p>No higher levels exist above $selectedLevel or invalid level selected.</p>";
    }
}
?>

</body>
</html>
