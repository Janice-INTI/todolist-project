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

// Filter values (if they exist)
$category = $_GET['category'] ?? '';
$priority = $_GET['priority'] ?? '';
$status   = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'asc';
$due_date = $_GET['due_date'] ?? '';

// Foundation of SQL query
$sql = "SELECT * FROM tasks WHERE 1=1";

/* Filtering logic */
// Category Filtering
if (!empty($category)) {
    $sql .= " AND category = '$category'";
}
// Priority Filtering
if (!empty($priority)) {
    $sql .= " AND priority = '$priority'";
}
// Status Filtering
if (!empty($status)) {
    $sql .= " AND status = '$status'";
}
// Due Date Filtering
if (!empty($due_date)) {
    if ($due_date === 'overdue') {
        $sql .= " AND due_date < CURDATE()";
    } elseif ($due_date === 'next_day') {
        $sql .= " AND due_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
    } elseif ($due_date === 'next_week') {
        $sql .= " AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($due_date === 'next_month') {
        $sql .= " AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
    }
}

// Sorting function
$sort_by  = $_GET['sort_by'] ?? '';
$sort_dir = $_GET['sort_dir'] ?? 'asc';

/* Sorting logic */
// Category Sorting
if ($sort_by === 'category') {
    $sql .= ($sort_dir === 'desc')
        ? " ORDER BY category DESC"
        : " ORDER BY category ASC";
} 
//Priority Sorting
elseif ($sort_by === 'priority') {
    // Custom priority order
    if ($sort_dir === 'hl') {
        $sql .= " ORDER BY FIELD(priority, 'High','Medium','Low')";
    } else {
        $sql .= " ORDER BY FIELD(priority, 'Low','Medium','High')";
    }
} 
//Due Date Sorting
elseif ($sort_by === 'due_date') {
    $sql .= ($sort_dir === 'desc')
        ? " ORDER BY due_date DESC"
        : " ORDER BY due_date ASC";
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

        /*Table appearance*/
        table { border-collapse: collapse; width: 100%; background-color: #fff; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }

        /*Filter box appearance*/
        .filter-box { margin-bottom: 20px; padding: 15px; background: #fff; border: 1px solid #ccc; }

        /*Status indicators*/
        .Pending { color: orange; font-weight: bold; }
        .On-going { color: blue; font-weight: bold; }
        .Completed { color: green; font-weight: bold; }

        /*Priority indicators*/
        .High { font-weight: bold; color: red; }
        .Medium { color: orange; }
        .Low { color: gray; }

        /*Button appearance*/
        select, button {
            padding: 6px;
            margin-right: 10px;
        }
    </style>
</head>
<body>

<h1>To-Do List Dashboard</h1>
    
<!-- Filter code -->
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
        <select name="due_date">
            <option value="">All</option>
            <option style="color: red;" value="overdue" <?= ($due_date=='overdue')?'selected':''; ?>>
                Overdue
            </option>
            <option style="color: gold;" value="next_day" <?= ($due_date=='next_day')?'selected':''; ?>>
                Due in the next day
            </option>
            <option value="next_week" <?= ($due_date=='next_week')?'selected':''; ?>>
                Due in the next week
            </option>
            <option value="next_month" <?= ($due_date=='next_month')?'selected':''; ?>>
                Due in the next month
            </option>
        </select>

        <button type="submit">Filter</button>
    </form>
</div>

<!-- Table code -->
<table>
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Description</th>
        
        <th>
        Category
            <form method="GET" style="display:inline">
                <input type="hidden" name="sort_by" value="category">
                <select name="sort_dir" onchange="this.form.submit()">
                    <option value="">Sort</option>
                    <option value="asc">A – Z</option>
                    <option value="desc">Z – A</option>
                </select>
            </form>
        </th>

        <th>
        Priority
            <form method="GET" style="display:inline">
                <input type="hidden" name="sort_by" value="priority">
                <select name="sort_dir" onchange="this.form.submit()">
                    <option value="">Sort</option>
                    <option value="hl">High → Medium → Low</option>
                    <option value="lh">Low → Medium → High</option>
                </select>
            </form>
        </th>

        <th>Status</th>

        <th>
        Due Date
            <form method="GET" style="display:inline">
                <input type="hidden" name="sort_by" value="due_date">
                <select name="sort_dir" onchange="this.form.submit()">
                    <option value="">Sort</option>
                    <option value="asc">Sort Ascending</option>
                    <option value="desc">Sort Descending</option>
                </select>
            </form>
        </th>
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
