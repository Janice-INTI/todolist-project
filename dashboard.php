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

// Get filter values (if any)
$category = $_GET['category'] ?? '';
$priority = $_GET['priority'] ?? '';
$status   = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'asc';

// Base SQL query
$sql = "SELECT * FROM tasks WHERE 1=1";

// Apply filters dynamically
if (!empty($category)) {
    $sql .= " AND category = '$category'";
}

if (!empty($priority)) {
    $sql .= " AND priority = '$priority'";
}

if (!empty($status)) {
    $sql .= " AND status = '$status'";
}

if ($sort === 'desc') {
    $sql .= " ORDER BY due_date DESC";
} else {
    $sql .= " ORDER BY due_date ASC";
}

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

        .filter-box {
            margin-bottom: 20px;
            padding: 15px;
            background: #fff;
            border: 1px solid #ccc;
        }

        select, button {
            padding: 6px;
            margin-right: 10px;
        }
    </style>
</head>
<body>

<h1>To-Do List Dashboard</h1>

<!-- FILTER FORM -->
<div class="filter-box">
    <form method="GET">
        <label>Category:</label>
        <select name="category">
            <option value="">All</option>
            <option value="Assignment" <?= ($category=='Assignment')?'selected':''; ?>>Assignment</option>
            <option value="Assessment" <?= ($category=='Assessment')?'selected':''; ?>>Assessment</option>
            <option value="Discussion" <?= ($category=='Discussion')?'selected':''; ?>>Discussion</option>
        </select>

        <label>Priority:</label>
        <select name="priority">
            <option value="">All</option>
            <option value="Low" <?= ($priority=='Low')?'selected':''; ?>>Low</option>
            <option value="Medium" <?= ($priority=='Medium')?'selected':''; ?>>Medium</option>
            <option value="High" <?= ($priority=='High')?'selected':''; ?>>High</option>
        </select>

        <label>Status:</label>
        <select name="status">
            <option value="">All</option>
            <option value="Pending" <?= ($status=='Pending')?'selected':''; ?>>Pending</option>
            <option value="On-going" <?= ($status=='On-going')?'selected':''; ?>>On-going</option>
            <option value="Completed" <?= ($status=='Completed')?'selected':''; ?>>Completed</option>
        </select>

        <label>Due Date:</label>
        <select name="sort">
            <option value="asc" <?= ($sort=='asc')?'selected':''; ?>>
                Earliest to Latest
            </option>
            <option value="desc" <?= ($sort=='desc')?'selected':''; ?>>
                Latest to Earliest
            </option>
        </select>

        <button type="submit">Filter</button>
    </form>
</div>

<!-- TASK TABLE -->
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
            echo "<td>".$row['category']."</td>";
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
