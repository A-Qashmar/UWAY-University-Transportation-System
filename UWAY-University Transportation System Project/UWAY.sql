DROP DATABASE IF EXISTS UWAY;
CREATE DATABASE UWAY;
USE UWAY;

-- USER TABLE
CREATE TABLE `User`
(
    UserID INT PRIMARY KEY AUTO_INCREMENT,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Role VARCHAR(20) NOT NULL CHECK (Role IN ('Admin','Driver','Student'))
);

-- ADMIN TABLE
CREATE TABLE Administrator
(
    AdminID VARCHAR(9) PRIMARY KEY,
    FullName VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    ContactNumber VARCHAR(15),
    UserID INT NOT NULL,
    FOREIGN KEY (Email) REFERENCES `User`(Email),
    FOREIGN KEY (UserID) REFERENCES `User`(UserID)
);

-- DRIVER TABLE
CREATE TABLE Driver
(
    DriverID VARCHAR(9) PRIMARY KEY,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    LicenseNumber VARCHAR(50) NOT NULL UNIQUE,
    ContactNumber VARCHAR(15),
    BusNumber VARCHAR(10) NOT NULL,
    UserID INT NOT NULL,
    FOREIGN KEY (Email) REFERENCES `User`(Email),
    FOREIGN KEY (UserID) REFERENCES `User`(UserID)
);

-- STUDENT TABLE
CREATE TABLE Student
(
    StudentID VARCHAR(9) PRIMARY KEY,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    ContactNumber VARCHAR(15),
    Department VARCHAR(100) NOT NULL,
    UserID INT NOT NULL,
    FOREIGN KEY (Email) REFERENCES `User`(Email),
    FOREIGN KEY (UserID) REFERENCES `User`(UserID)
);

-- RIDE TABLE
CREATE TABLE Ride (
    RideID INT PRIMARY KEY AUTO_INCREMENT,
    DriverID VARCHAR(9) NOT NULL,
    RideDate DATETIME NOT NULL,
    RoadStart VARCHAR(100) NOT NULL,
    RoadEnd VARCHAR(100) NOT NULL,
    Status VARCHAR(20) NOT NULL CHECK (Status IN ('Scheduled', 'Completed', 'Cancelled')),
    FOREIGN KEY (DriverID) REFERENCES Driver(DriverID)
);

-- RIDE REGISTRATION
CREATE TABLE RideRegistration (
    RideID INT NOT NULL,
    StudentID VARCHAR(9) NOT NULL,
    SeatNumber INT,
    PRIMARY KEY (RideID, StudentID),
    FOREIGN KEY (RideID) REFERENCES Ride(RideID),
    FOREIGN KEY (StudentID) REFERENCES Student(StudentID)
);

-- USERS
INSERT INTO `User` (Email, Password, Role) VALUES
('admin1@uway.com', 'A1', 'Admin'),
('admin2@uway.com', 'A2', 'Admin'),
('driver1@uway.com', 'D1', 'Driver'),
('driver2@uway.com', 'D2', 'Driver'),
('driver3@uway.com', 'D3', 'Driver'),
('U22200508@sharjah.ac.ae', '508', 'Student'),
('U22200519@sharjah.ac.ae', '519', 'Student'),
('U22200529@sharjah.ac.ae', '529', 'Student'),
('U21200816@sharjah.ac.ae', '816', 'Student');

-- ADMINS (FIXED)
INSERT INTO Administrator (AdminID, FullName, Email, ContactNumber, UserID) VALUES
('A001', 'Khaled Rashed', 'admin1@uway.com', '0501111111', 1),
('A002', 'Sami Kreem', 'admin2@uway.com', '0502222222', 2);

-- DRIVERS (FIXED)
INSERT INTO Driver (DriverID, FirstName, LastName, Email, LicenseNumber, ContactNumber, BusNumber, UserID) VALUES
('D001', 'Ali', 'Saleh', 'driver1@uway.com', 'LIC111', '0503333333', 'Bus1', 3),
('D002', 'Hassan', 'Saeed', 'driver2@uway.com', 'LIC222', '0504444444', 'Bus2', 4),
('D003', 'Omar', 'Nasser', 'driver3@uway.com', 'LIC333', '0505555555', 'Bus3', 5);

-- STUDENTS
INSERT INTO Student (StudentID, FirstName, LastName, Email, ContactNumber, Department, UserID) VALUES
('U22200508', 'ABDULQADER', 'ALI', 'U22200508@sharjah.ac.ae', '0501405979', 'Computing', 6),
('U22200519', 'ABDULQADER', 'SALAH', 'U22200519@sharjah.ac.ae', '0501234567', 'Computing', 7),
('U22200529', 'YOUSEF', 'EBRAHIM', 'U22200529@sharjah.ac.ae', '0509876543', 'Computing', 8),
('U21200816', 'Sadiq', 'Lawal', 'U21200816@sharjah.ac.ae', '0501234568', 'Computing', 9);

-- RIDES
INSERT INTO Ride (DriverID, RideDate, RoadStart, RoadEnd, Status) VALUES
('D001', '2025-11-25 08:00:00', 'Sharjah University', 'Sharjah (King Faisel Road)', 'Scheduled'),
('D002', '2025-11-26 09:00:00', 'Sharjah University', 'Sharjah (Al Heira Road)', 'Scheduled'),
('D003', '2025-11-26 10:00:00', 'Sharjah (King Faisel Road)', 'Sharjah University', 'Scheduled'),
('D001', '2025-11-27 11:00:00', 'Sharjah (Al Heira Road)', 'Sharjah University', 'Scheduled'),
('D001', '2025-11-29 08:00:00', 'Sharjah University', 'Sharjah (King Faisel Road)', 'Scheduled'),
('D002', '2025-11-29 09:00:00', 'Sharjah University', 'Sharjah (Al Heira Road)', 'Scheduled'),
('D003', '2025-12-03 10:00:00', 'Sharjah (King Faisel Road)', 'Sharjah University', 'Scheduled'),
('D001', '2025-12-04 11:00:00', 'Sharjah (Al Heira Road)', 'Sharjah University', 'Scheduled');

-- REGISTRATIONS
INSERT INTO RideRegistration VALUES
(1, 'U22200508', 5),
(1, 'U22200519', 12),
(2, 'U22200529', 7),
(3, 'U21200816', 3);
