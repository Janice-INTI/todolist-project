<?php
// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "todolist_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all tasks that are not archived/ Completed
$sql = "SELECT * FROM tasks WHERE status != 'Completed' ORDER BY due_date ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>To-Do List Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f7f7f7; }
        h1 { color: #333; }
        table { border-collapse: collapse; width: 100%; background-color: #fff; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .Pending { color: orange; font-weight: bold; }
        .On-going { color: blue; font-weight: bold; }
        .Completed { color: green; font-weight: bold; }
        .High { font-weight: bold; color: red; }
        .Medium { color: orange; }
        .Low { color: gray; }
    </style>
</head>
<body>

<h1>To-Do List Dashboard</h1>

<label> Category: </label>

    
    
<table>
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Description</th>
        <th>Category</th>
        <th>Priority</th>
        <th>Status</th>
        <th>Due Date</th>
    </tr>

    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>".$row['id']."</td>";
            echo "<td>".$row['title']."</td>";
            echo "<td>".$row['description']."</td>";
            echo "<td class='".$row['category']."'>".$row['category']."</td>";
            echo "<td class='".$row['priority']."'>".$row['priority']."</td>";
            echo "<td class='".$row['status']."'>".$row['status']."</td>";
            echo "<td>".$row['due_date']."</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='7'>No tasks found</td></tr>";
    }
    $conn->close();
    ?>
</table>

</body>
</html>
