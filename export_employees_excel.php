<?php
require 'config.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=employee_list.xls");

echo "Name\tEmployee ID\tDesignation\tEmail\tPhone\tdate_of_birth\tdate_of_join\tManager\tLocation\n";

$stmt = $conn->query("SELECT * FROM employees");
while ($row = $stmt->fetch(mode: PDO::FETCH_ASSOC)) {
    echo "{$row['name']}\t{$row['employee_id']}\t{$row['designation']}\t{$row['email']}\t{$row['phone']}\t{$row['date_of_birth']}\t{$row['date_of_join']}\t{$row['manager_name']}\t{$row['location']}\n";
}
?>