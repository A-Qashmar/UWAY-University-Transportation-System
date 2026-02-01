<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

DEFINE('DB_USER', 'root');
DEFINE('DB_PASSWORD', '');
DEFINE('DB_HOST', 'localhost');
DEFINE('DB_NAME', 'uway');

$connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$user_id = $_SESSION['user_id'];

$query  = "SELECT StudentID, FirstName, LastName FROM Student WHERE UserID = $user_id";
$result = $connection->query($query);
$row    = mysqli_fetch_array($result);

$student_id   = $row['StudentID'];
$student_name = $row['FirstName'] . " " . $row['LastName'];

$page = "";
if (isset($_GET['page'])) {
    $page = $_GET['page'];
}

$message = "";

if (isset($_POST['confirm_booking'])) {
    $ride_id = $_POST['ride_id'];
    $seat    = $_POST['seat_number'];

    $query = "SELECT * FROM RideRegistration
                WHERE RideID = $ride_id
                AND StudentID = '$student_id'";
    $result = $connection->query($query);

    if ($result && mysqli_num_rows($result) > 0) {
        $message = "<div class='error'>You are already registered on this trip.</div>";
        $page = "book";
    } else {
        $query = "INSERT INTO RideRegistration (RideID, StudentID, SeatNumber)
                    VALUES ($ride_id, '$student_id', $seat)";

        if ($connection->query($query)) {
            $message = "<div class='success'>Trip booked successfully!</div>";
        } else {
            $message = "<div class='error'>Could not book trip.</div>";
        }

        $page = "book";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UWAY Student Dashboard</title>
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
            <li><a href="student.php"<?php if ($page == "") echo " class='active'"; ?>>Dashboard</a></li>
            <li><a href="student.php?page=book"<?php if ($page == "book") echo " class='active'"; ?>>Book Trip</a></li>
            <li><a href="student.php?page=history"<?php if ($page == "history") echo " class='active'"; ?>>Show Trip History</a></li>
            <li><a href="login.php">Logout</a></li>
        </ul>

        <div class="sidebar-user">
            Welcome, <?php echo $student_name; ?>
        </div>
    </aside>

    <main class="main-content">
        <div class="main-header">
            <div class="main-header-title">Student Dashboard</div>
            <div class="main-header-right">View and book your rides</div>
        </div>

        <?php echo $message; ?>

        <?php if ($page == "") { ?>

            <div class="uway-card">
                <h2>Welcome, <?php echo $student_name; ?></h2>
                <p>Select "Book Trip" to find a ride or view your previous trips in "Show Trip History".</p>
            </div>

        <?php } elseif ($page == "book") { ?>

            <?php if (!isset($_POST['search_trips']) && !isset($_POST['confirm_booking'])) { ?>

                <section class="uway-form">
                    <h2>Search for Trips</h2>

                    <form method="post" action="student.php?page=book">
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
                            <label>Date</label>
                            <input class="uway-input" type="date" name="date" required>
                        </p>

                        <p>
                            <input type="submit" name="search_trips" value="Search Trip">
                        </p>
                    </form>
                </section>

            <?php } else {

                if (isset($_POST['search_trips'])) {
                    $start = $_POST['start'];
                    $end   = $_POST['end'];
                    $date  = $_POST['date'];
                } else {
                    $start = "";
                    $end   = "";
                    $date  = "";
                }

                if (isset($_POST['search_trips'])) {
                    echo "<div class='uway-card'><h2>Available Trips on $date</h2>";

                    if (
                        ($start == 'Sharjah (King Faisel Road)' && $end == 'Sharjah (Al Heira Road)')
                        ||
                        ($start == 'Sharjah (Al Heira Road)' && $end == 'Sharjah (King Faisel Road)')
                        ||
                        ($start == $end)
                    ) 
                    {
                        echo "<p>No trips found (this route is not available in UoS transport).</p>";
                        echo "</div>";

                    } 
                    else 
                    {

                        $query = "SELECT Ride.RideID, Ride.RideDate, Ride.RoadStart, Ride.RoadEnd,
                               Driver.FirstName, Driver.LastName, Driver.BusNumber
                                    FROM Ride, Driver
                                    WHERE Ride.DriverID = Driver.DriverID
                                    AND Ride.Status = 'Scheduled'
                                    AND Ride.RoadStart = '$start'
                                    AND Ride.RoadEnd   = '$end'
                                    AND DATE(Ride.RideDate) = '$date'";
                        $result = $connection->query($query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            echo "</div>";

                            echo "<div class='uway-table-wrapper'>";
                            echo "<div class='uway-table-title'>Trips</div>";
                            echo "<table>";
                            echo "<tr>
                                    <th>ID</th>
                                    <th>Date &amp; Time</th>
                                    <th>Driver</th>
                                    <th>Bus</th>
                                    <th>Route</th>
                                    <th>Seat</th>
                                    <th>Book</th>
                                  </tr>";

                            while ($row = mysqli_fetch_array($result)) {
                                $ride_id = $row['RideID'];
                                $route   = $row['RoadStart'] . " → " . $row['RoadEnd'];
                                $driver  = $row['FirstName'] . " " . $row['LastName'];

                                $taken = array();

                                $query2  = "SELECT SeatNumber FROM RideRegistration WHERE RideID = $ride_id";
                                $result2 = $connection->query($query2);

                                while ($s = mysqli_fetch_array($result2)) {
                                    $taken[] = $s['SeatNumber'];
                                }

                                $all_seats = range(1, 20);
                                $free_seats = array_diff($all_seats, $taken);

                                echo "<tr>";
                                echo "<td>$ride_id</td>";
                                echo "<td>".$row['RideDate']."</td>";
                                echo "<td>$driver</td>";
                                echo "<td>".$row['BusNumber']."</td>";
                                echo "<td>$route</td>";
                                echo "<td>";
                                echo "<form method='post' action='student.php?page=book'>";
                                echo "<input type='hidden' name='ride_id' value='$ride_id'>";
                                echo "<select name='seat_number'>";
                                foreach ($free_seats as $seat) {
                                    echo "<option value='$seat'>$seat</option>";
                                }
                                echo "</select>";
                                echo "</td>";
                                echo "<td>";
                                echo "<input type='submit' name='confirm_booking' value='Book'>";
                                echo "</form>";
                                echo "</td>";
                                echo "</tr>";
                            }

                            echo "</table>";
                            echo "</div>";

                            echo "<div class='seat-map mt-3'>
                                    <img src='seatsmap.png' alt='Bus Seat Map'>
                                  </div>";
                        } else {
                            echo "<p>No trips found.</p>";
                            echo "</div>";
                        }
                    }
                }
            }
        } elseif ($page == "history") { ?>

            <section class="uway-table-wrapper">
                <div class="uway-table-title">My Trip History</div>

                <table>
                    <tr>
                        <th>Ride ID</th>
                        <th>Date &amp; Time</th>
                        <th>Route</th>
                        <th>Seat</th>
                        <th>Driver</th>
                        <th>Bus</th>
                    </tr>

                    <?php
                    $query = "SELECT Ride.RideID, Ride.RideDate, Ride.RoadStart, Ride.RoadEnd,
                                     RideRegistration.SeatNumber, Driver.FirstName,
                                     Driver.LastName, Driver.BusNumber
                                FROM Ride, RideRegistration, Driver
                                WHERE Ride.RideID = RideRegistration.RideID
                                AND Ride.DriverID = Driver.DriverID
                                AND RideRegistration.StudentID = '$student_id'
                                ORDER BY Ride.RideDate DESC
                    ";
                    $result = $connection->query($query);

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_array($result)) {
                            $route  = $row['RoadStart'] . " → " . $row['RoadEnd'];
                            $driver = $row['FirstName'] . " " . $row['LastName'];

                            echo "<tr>";
                            echo "<td>".$row['RideID']."</td>";
                            echo "<td>".$row['RideDate']."</td>";
                            echo "<td>".$route."</td>";
                            echo "<td>".$row['SeatNumber']."</td>";
                            echo "<td>".$driver."</td>";
                            echo "<td>".$row['BusNumber']."</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No trips found.</td></tr>";
                    }
                    ?>
                </table>
            </section>

        <?php } ?>
    </main>
</div>

</body>
</html>