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

        .section-heading {
            margin-top: 40px;
            font-size: 18px;
            color: #333;
        }
    </style>
</head>
<body>

<h2>Search Employees by Hierarchy Level</h2>

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
// Define level mapping: selected => higher level
$hierarchy_map = [
    "L2" => "L3",
    "L3" => "L4",
    "L4" => "L5",
    "L5" => "L6",
    "L6" => "L7",
    "L7" => "L8",
    "L8" => null
];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['search'])) {
    $level = $_POST['level'];
    $higher_level = $hierarchy_map[$level] ?? null;

    if (!empty($level)) {
        // Show selected level results
        $stmt = $pdo->prepare("SELECT name, designation FROM employees WHERE hierarchy_level = ?");
        $stmt->execute([$level]);

        echo "<div class='section-heading'>Employees at Level: $level</div>";

        if ($stmt->rowCount() > 0) {
            echo "<table>
                    <tr>
                        <th>Name</th>
                        <th>Designation</th>
                    </tr>";
            while ($row = $stmt->fetch()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['name']) . "</td>
                        <td>" . htmlspecialchars($row['designation']) . "</td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No employees found for level $level.</p>";
        }

        // Show higher level results (if any)
        if ($higher_level) {
            $stmt2 = $pdo->prepare("SELECT name, designation FROM employees WHERE hierarchy_level = ?");
            $stmt2->execute([$higher_level]);

            echo "<div class='section-heading'>Employees at Higher Level: $higher_level</div>";

            if ($stmt2->rowCount() > 0) {
                echo "<table>
                        <tr>
                            <th>Name</th>
                            <th>Designation</th>
                        </tr>";
                while ($row = $stmt2->fetch()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['name']) . "</td>
                            <td>" . htmlspecialchars($row['designation']) . "</td>
                          </tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No employees found for higher level $higher_level.</p>";
            }
        } else {
            echo "<p>No higher level exists for $level.</p>";
        }

    } else {
        echo "<p>Please select a hierarchy level.</p>";
    }
}
?>

</body>
</html>
