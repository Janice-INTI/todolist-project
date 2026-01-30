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

function arrow($col) {
  if (($_GET['sort_by'] ?? '') === $col) {
    return (($_GET['sort_dir'] ?? 'asc') === 'desc' || ($_GET['sort_dir'] ?? 'hl') === 'hl') ? ' ▼' : ' ▲';
  }
  return '';
}
?>
<script>
function sortTable(sortBy) {
  const params = new URLSearchParams(window.location.search);

  const currentSort = params.get('sort_by');
  let currentDir = params.get('sort_dir') || 'asc';

  if (currentSort === sortBy) {
    // toggle direction
    if (sortBy === 'priority') {
      currentDir = currentDir === 'hl' ? 'lh' : 'hl';
    } else {
      currentDir = currentDir === 'asc' ? 'desc' : 'asc';
    }
  } else {
    // default direction
    currentDir = sortBy === 'priority' ? 'hl' : 'asc';
  }

  params.set('sort_by', sortBy);
  params.set('sort_dir', currentDir);

  window.location.search = params.toString();
}
</script>
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

        /* Filter container */
		.filter-box {
		  margin-bottom: 20px;
		  border: 1px solid #d1d5db;
		  border-radius: 8px;
		  background: #fff;
		}

		/* Header */
		.filter-box summary {
		  padding: 12px 16px;
		  cursor: pointer;
		  font-size: 14px;
		  font-weight: 600;
		  color: #111827;
		  list-style: none;
		}

		/* Remove default arrow */
		.filter-box summary::-webkit-details-marker {
		  display: none;
		}

		/* Custom arrow */
		.filter-box summary::after {
		  content: "▾";
		  float: right;
		  transition: transform 0.2s ease;
		}

		/* Rotate arrow when open */
		.filter-box[open] summary::after {
		  transform: rotate(180deg);
		}

		/* Content */
		.filter-content {
		  padding: 15px;
		  border-top: 1px solid #e5e7eb;
		}


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
		
		.btn-success,
		.btn-danger,
		.btn-warning {
		  padding: 8px 14px;
		  border: 1px solid transparent;
		  border-radius: 6px;
		  font-size: 13px;
		  font-weight: 500;
		  color: #fff;
		  cursor: pointer;
		  transition: background-color 0.15s ease, box-shadow 0.15s ease;
		}

		/* Success */
		.btn-success {
		  background-color: #22c55e;
		}
		.btn-success:hover {
		  background-color: #16a34a;
		}

		/* Danger */
		.btn-danger {
		  background-color: #ef4444;
		}
		.btn-danger:hover {
		  background-color: #dc2626;
		}

		/* Warning */
		.btn-warning {
		  background-color: #f59e0b;
		}
		.btn-warning:hover {
		  background-color: #d97706;
		}

		/* Optional subtle focus (accessibility) */
		.btn-success:focus,
		.btn-danger:focus,
		.btn-warning:focus {
		  outline: none;
		  box-shadow: 0 0 0 2px rgba(0,0,0,0.08);
		}

		.select-box {
		  padding: 8px 12px;
		  border: 1px solid #d1d5db;
		  border-radius: 6px;
		  font-size: 13px;
		  background-color: #fff;
		  color: #111827;
		  cursor: pointer;
		  min-width: 160px;
		}

		/* focus state */
		.select-box:focus {
		  outline: none;
		  border-color: #6366f1;
		}


    </style>
</head>
<body>

<h1>To-Do List Dashboard</h1>
    
	
<details class="filter-box" open>
  <summary>🔍 Filters</summary>

  <div class="filter-content">
   <form method="GET">
        <label>Category:</label>
        <select class="select-box" name="category">
            <option value="">All</option>
            <option value="Assignment" <?= ($category=='Assignment')?'selected':''; ?>>Assignment</option>
            <option value="Assessment" <?= ($category=='Assessment')?'selected':''; ?>>Assessment</option>
            <option value="Discussion" <?= ($category=='Discussion')?'selected':''; ?>>Discussion</option>
        </select>

        <label>Priority:</label>
        <select class="select-box" name="priority">
            <option value="">All</option>
            <option value="Low" <?= ($priority=='Low')?'selected':''; ?>>Low</option>
            <option value="Medium" <?= ($priority=='Medium')?'selected':''; ?>>Medium</option>
            <option value="High" <?= ($priority=='High')?'selected':''; ?>>High</option>
        </select>

        <label>Status:</label>
        <select class="select-box" name="status">
            <option value="">All</option>
            <option value="Pending" <?= ($status=='Pending')?'selected':''; ?>>Pending</option>
            <option value="On-going" <?= ($status=='On-going')?'selected':''; ?>>On-going</option>
            <option value="Completed" <?= ($status=='Completed')?'selected':''; ?>>Completed</option>
        </select>

        <label>Due Date:</label>
        <select class="select-box" name="due_date">
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

        <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sort_by) ?>">
        <input type="hidden" name="sort_dir" value="<?= htmlspecialchars($sort_dir) ?>">    

		<button class="btn-success">Filter</button>
		
    </form>
  </div>
</details>


<!-- Table code -->
<table>
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Description</th>
        
        <th>
			<a href="#" onclick="sortTable('category'); return false;">
			Category<?= arrow('category') ?>
		  </a>
        </th>

        <th>
        <a href="#" onclick="sortTable('priority'); return false;">
			Priority<?= arrow('priority') ?>
		</a>
        </th>

        <th>Status</th>

        <th>
         <a href="#" onclick="sortTable('due_date'); return false;">
			Due Date<?= arrow('due_date') ?>
		  </a>
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
        echo "<tr><td colspan='7'>No tasks found. If you filtered by Completed tasks and received this result, please head to the Archive page to view those instead.</td></tr>";
    }
    $conn->close();
    ?>
</table>

</body>
</html>
