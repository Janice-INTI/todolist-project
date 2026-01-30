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


// Foundation of SQL query
$sql = "SELECT * FROM tasks WHERE status = 'Completed'";

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
		.btn-warning,
		.btn-primary {  /* added primary here */
		  padding: 8px 14px;
		  border: 1px solid transparent;
		  border-radius: 6px;
		  font-size: 13px;
		  font-weight: 500;
		  color: #fff;
		  cursor: pointer;
		  transition: background-color 0.15s ease, box-shadow 0.15s ease;
		}

		/* Primary */
		.btn-primary {
		  background-color: #4f46e5; /* Indigo */
		}
		.btn-primary:hover {
		  background-color: #4338ca;
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
		.btn-warning:focus,
		.btn-primary:focus {  /* added primary here */
		  outline: none;
		  box-shadow: 0 0 0 2px rgba(0,0,0,0.08);
		}

		/* Select box */
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
		
		.header {
		  display: flex;
		  align-items: center;      /* vertically center heading & button */
		  justify-content: space-between; /* push items to edges */
		}
		
		a {
		  text-decoration: none; /* removes underline */
		  color:#4f46e5;        /* optional: inherit text color */
		}

		/* Optional: hover effect */
		a:hover {
		  text-decoration: none; /* still no underline on hover */
		  color: white;        /* optional: color change on hover */
		}
    </style>
</head>
<body>

<div class="header">
  <h1>To-Do List Dashboard > Archive / Completed Tasks</h1>
  
  
  <div>
  <a href="dashboard.php" class="btn-danger">
    Go Back
  </a>
  </div>
</div>

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
        echo "<tr><td colspan='7'>No archive or completed tasks found.</td></tr>";
    }
    $conn->close();
    ?>
</table>

</body>
</html>
