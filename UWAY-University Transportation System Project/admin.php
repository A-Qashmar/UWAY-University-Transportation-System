<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

DEFINE('DB_USER', 'root');
DEFINE('DB_PASSWORD', '');
DEFINE('DB_HOST', 'localhost');
DEFINE('DB_NAME', 'uway');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$admin_name = "Admin";

$user_id = $_SESSION['user_id'];

$query = "SELECT FullName FROM Administrator WHERE UserID = $user_id";
$result = $conn->query($query);

if ($result && mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_array($result);
    $admin_name = $row['FullName'];
}

$page = "";
if (isset($_GET['page'])) {
    $page = $_GET['page'];
}

$message = "";

if (isset($_POST['add_trip'])) {

    $driver = $_POST['driver'];
    $date   = $_POST['date'];
    $start  = $_POST['start'];
    $end    = $_POST['end'];

    if ($start == $end) {
        $message = "<div class='error'>Start and end cannot be the same.</div>";
        $page = "add";
    }
    elseif (
        ($start == 'Sharjah (King Faisel Road)' && $end == 'Sharjah (Al Heira Road)')
         ||
        ($start == 'Sharjah (Al Heira Road)' && $end == 'Sharjah (King Faisel Road)')
    ) {
        $message = "<div class='error'>This route does not exist in UoS transportation.</div>";
        $page = "add";
    }
    else {
        $query = "INSERT INTO Ride (DriverID, RideDate, RoadStart, RoadEnd, Status)
                  VALUES ('$driver', '$date', '$start', '$end', 'Scheduled')";

        if ($conn->query($query)) {
            $message = "<div class='success'>Trip added!</div>";
        } else {
            $message = "<div class='error'>Error adding trip.</div>";
        }

        $page = "add";
    }
}

$filter_date = "";
if (isset($_POST['filter_date'])) {
    $filter_date = $_POST['filter_date'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UWAY – Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="dashboard-layout">

    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="uway-logo.png" alt="UWAY">
            <div class="sidebar-logo-title">UWAY</div>
        </div>

        <ul class="sidebar-menu">
            <li><a href="admin.php"<?php if ($page == "") echo " class='active'"; ?>>Dashboard</a></li>
            <li><a href="admin.php?page=add"<?php if ($page == "add") echo " class='active'"; ?>>Add Trip</a></li>
            <li><a href="admin.php?page=rides"<?php if ($page == "rides") echo " class='active'"; ?>>Ride List</a></li>
            <li><a href="login.php">Logout</a></li>
        </ul>

        <div class="sidebar-user">
            Welcome, <?php echo $admin_name; ?>
        </div>
    </aside>

    <main class="main-content">
        <div class="main-header">
            <div class="main-header-title">Admin Dashboard</div>
            <div class="main-header-right">UWAY – University Transportation System</div>
        </div>

        <?php
        echo $message;

        if ($page == "") {
            ?>
            <div class="uway-card">
                <h2>Welcome, <?php echo $admin_name; ?></h2>
                <p>Select an option from the menu to manage trips and view ride information.</p>
            </div>
            <?php
        } elseif ($page == "add") {
            ?>
            <section class="uway-form">
                <h2>Add New Trip</h2>

                <form method="post" action="admin.php?page=add">
                    <p>
                        <label>Driver</label>
                        <select class="uway-select" name="driver">
                            <?php
                            $query = "SELECT DriverID, FirstName, LastName FROM Driver";
                            $result = $conn->query($query);

                            while ($row = mysqli_fetch_array($result)) {
                                $name = $row['FirstName'] . " " . $row['LastName'];
                                echo "<option value='".$row['DriverID']."'>$name</option>";
                            }
                            ?>
                        </select>
                    </p>

                    <p>
                        <label>Date &amp; Time</label>
                        <input class="uway-input" type="datetime-local" name="date" required>
                    </p>

                    <p>
                        <label>Start Location</label>
                        <select class="uway-select" name="start">
                            <option value="Sharjah University">Sharjah University</option>
                            <option value="Sharjah (King Faisel Road)">Sharjah (King Faisel Road)</option>
                            <option value="Sharjah (Al Heira Road)">Sharjah (Al Heira Road)</option>
                        </select>
                    </p>

                    <p>
                        <label>End Location</label>
                        <select class="uway-select" name="end">
                            <option value="Sharjah University">Sharjah University</option>
                            <option value="Sharjah (King Faisel Road)">Sharjah (King Faisel Road)</option>
                            <option value="Sharjah (Al Heira Road)">Sharjah (Al Heira Road)</option>
                        </select>
                    </p>

                    <p>
                        <input type="submit" name="add_trip" value="Add Trip">
                    </p>
                </form>
            </section>
            <?php
        } elseif ($page == "rides") {
            ?>
            <div class="uway-card">
                <h2>Filter Rides by Date</h2>
                <form method="post" action="admin.php?page=rides">
                    <p>
                        <label>Date</label>
                        <input class="uway-input" type="date" name="filter_date" value="<?php echo $filter_date; ?>">
                    </p>
                    <p>
                        <input type="submit" value="Filter">
                    </p>
                </form>
            </div>

            <section class="uway-table-wrapper">
                <div class="uway-table-title">Ride List</div>

                <table>
                    <tr>
                        <th>Ride ID</th>
                        <th>Date</th>
                        <th>Driver</th>
                        <th>Bus</th>
                        <th>Route</th>
                    </tr>

                    <?php
                    $query =
                    "SELECT Ride.RideID, Ride.RideDate, Ride.RoadStart, Ride.RoadEnd,
                           Driver.FirstName, Driver.LastName, Driver.BusNumber
                    FROM Ride, Driver
                    WHERE Ride.DriverID = Driver.DriverID
                    ";

                    if ($filter_date != "") {
                        $query .= " AND DATE(Ride.RideDate) = '$filter_date' ";
                    }

                    $query .= " ORDER BY Ride.RideDate DESC ";

                    $result = $conn->query($query);

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_array($result)) {
                            $driver = $row['FirstName'] . " " . $row['LastName'];
                            $route  = $row['RoadStart'] . " → " . $row['RoadEnd'];

                            echo "<tr>";
                            echo "<td>".$row['RideID']."</td>";
                            echo "<td>".$row['RideDate']."</td>";
                            echo "<td>".$driver."</td>";
                            echo "<td>".$row['BusNumber']."</td>";
                            echo "<td>".$route."</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No rides found.</td></tr>";
                    }
                    ?>
                </table>
            </section>
            <?php
        }
        ?>
    </main>
</div>

</body>
</html>
