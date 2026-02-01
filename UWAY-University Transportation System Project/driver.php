<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Driver') {
    header("Location: login.php");
    exit;
}

DEFINE('DB_USER', 'root');
DEFINE('DB_PASSWORD', '');
DEFINE('DB_HOST', 'localhost');
DEFINE('DB_NAME', 'uway');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$user_id = $_SESSION['user_id'];

$query = "SELECT DriverID, FirstName, LastName FROM Driver WHERE UserID = $user_id";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);

$driver_id   = $row['DriverID'];
$driver_name = $row['FirstName'] . " " . $row['LastName'];

if (isset($_POST['update_status'])) {
    $ride_id = $_POST['ride_id'];
    $new_status = $_POST['new_status'];

    $query = "UPDATE Ride SET Status='$new_status' WHERE RideID=$ride_id";
    $conn->query($query);
}

$today = date("Y-m-d");

$query = "
SELECT RideID, RideDate
FROM Ride
WHERE DriverID = '$driver_id'
AND DATE(RideDate) = '$today'
";

$result = $conn->query($query);

$today_ride_id = "";

if ($result && mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_array($result);
    $today_ride_id = $row['RideID'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UWAY – Driver Dashboard</title>
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
            <li><a href="driver.php" class="active">Dashboard</a></li>
            <li><a href="login.php">Logout</a></li>
        </ul>

        <div class="sidebar-user">
            Welcome, <?php echo $driver_name; ?>
        </div>
    </aside>

    <main class="main-content">
        <div class="main-header">
            <div class="main-header-title">Driver Dashboard</div>
            <div class="main-header-right">View assigned trips and passengers</div>
        </div>

        <section class="uway-table-wrapper">
            <div class="uway-table-title">Your Trips</div>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Route</th>
                    <th>Status</th>
                    <th>Change Status</th>
                </tr>

                <?php
                $query =
                "SELECT RideID, RideDate, RoadStart, RoadEnd, Status
                FROM Ride
                WHERE DriverID = '$driver_id'
                ORDER BY RideDate DESC
                ";

                $result = $conn->query($query);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_array($result)) {
                        $route = $row['RoadStart'] . " -> " . $row['RoadEnd'];

                        echo "<tr>";
                        echo "<td>".$row['RideID']."</td>";
                        echo "<td>".$row['RideDate']."</td>";
                        echo "<td>".$route."</td>";
                        echo "<td>".$row['Status']."</td>";

                        echo "<td>";
                        echo "<form method='post' action='driver.php'>";
                        echo "<input type='hidden' name='ride_id' value='".$row['RideID']."'>";
                        echo "<select name='new_status'>";
                        echo "<option value='Scheduled'>Scheduled</option>";
                        echo "<option value='Completed'>Completed</option>";
                        echo "<option value='Cancelled'>Cancelled</option>";
                        echo "</select> ";
                        echo "<input type='submit' name='update_status' value='Update'>";
                        echo "</form>";
                        echo "</td>";

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No trips found.</td></tr>";
                }
                ?>
            </table>
        </section>

        <section class="uway-table-wrapper mt-3">
            <div class="uway-table-title">Students on Today's Ride</div>

            <?php
            if ($today_ride_id == "") {
                echo "<p>No students today.</p>";
            } else {
                echo "<p>Today's Ride ID: $today_ride_id</p>";

                echo "<table>";
                echo "<tr><th>Student Name</th><th>Seat</th></tr>";

                $query =
                "SELECT Student.FirstName, Student.LastName, RideRegistration.SeatNumber
                FROM RideRegistration, Student
                WHERE RideRegistration.StudentID = Student.StudentID
                AND RideRegistration.RideID = $today_ride_id
                ";

                $result = $conn->query($query);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_array($result)) {
                        $student = $row['FirstName'] . " " . $row['LastName'];
                        echo "<tr>";
                        echo "<td>$student</td>";
                        echo "<td>".$row['SeatNumber']."</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='2'>No students registered.</td></tr>";
                }

                echo "</table>";
            }
            ?>
        </section>
    </main>
</div>

</body>
</html>
