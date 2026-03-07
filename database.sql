CREATE DATABASE IF NOT EXISTS HMS;
USE HMS;

-- =========================
-- ROLES
-- =========================
CREATE TABLE Roles (
    RoleID INT AUTO_INCREMENT PRIMARY KEY,
    RoleName VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO Roles (RoleName) VALUES
('admin'),
('staff'),
('guest');

-- =========================
-- USERS
-- =========================
CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    RoleID INT NOT NULL,
    FirstName VARCHAR(100) NOT NULL,
    LastName VARCHAR(100) NOT NULL,
    Email VARCHAR(150) NOT NULL UNIQUE,
    PasswordHash VARCHAR(255) NOT NULL,
    PhoneContact VARCHAR(20),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (RoleID) REFERENCES Roles(RoleID)
);

-- =========================
-- BED TYPES
-- =========================
CREATE TABLE BedTypes (
    BedTypeID INT AUTO_INCREMENT PRIMARY KEY,
    BedName VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO BedTypes (BedName) VALUES
('Single'),
('Double'),
('Queen'),
('King');

-- =========================
-- ROOM TYPES
-- =========================
CREATE TABLE RoomTypes (
    RoomTypeID INT AUTO_INCREMENT PRIMARY KEY,
    RoomTypeName VARCHAR(100) NOT NULL,
    BasePrice DECIMAL(10,2) NOT NULL,
    BedTypeID INT,
    BedCount INT DEFAULT 1,
    MaxOccupancy INT NOT NULL,

    FOREIGN KEY (BedTypeID) REFERENCES BedTypes(BedTypeID)
);

-- =========================
-- FLOORS
-- =========================
CREATE TABLE Floors (
    FloorID INT AUTO_INCREMENT PRIMARY KEY,
    FloorNumber INT NOT NULL UNIQUE
);

-- =========================
-- ROOMS
-- =========================
CREATE TABLE Rooms (
    RoomID INT AUTO_INCREMENT PRIMARY KEY,
    RoomNumber VARCHAR(10) NOT NULL UNIQUE,
    FloorID INT NOT NULL,
    RoomTypeID INT NOT NULL,
    Status ENUM('available','occupied','maintenance') DEFAULT 'available',

    FOREIGN KEY (FloorID) REFERENCES Floors(FloorID),
    FOREIGN KEY (RoomTypeID) REFERENCES RoomTypes(RoomTypeID)
);

-- =========================
-- RESERVATION STATUS
-- =========================
CREATE TABLE ReservationStatus (
    StatusID INT AUTO_INCREMENT PRIMARY KEY,
    StatusName VARCHAR(50) UNIQUE
);

INSERT INTO ReservationStatus (StatusName) VALUES
('pending'),
('confirmed'),
('checked_in'),
('checked_out'),
('cancelled');

-- =========================
-- RESERVATIONS
-- =========================
CREATE TABLE Reservations (
    ReservationID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    StatusID INT NOT NULL,
    CheckInDate DATE NOT NULL,
    CheckOutDate DATE NOT NULL,
    NumAdults INT DEFAULT 1,
    NumChildren INT DEFAULT 0,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (UserID) REFERENCES Users(UserID),
    FOREIGN KEY (StatusID) REFERENCES ReservationStatus(StatusID)
);

-- =========================
-- RESERVED ROOMS
-- (supports multi-room bookings)
-- =========================
CREATE TABLE ReservationRooms (
    ReservationRoomID INT AUTO_INCREMENT PRIMARY KEY,
    ReservationID INT NOT NULL,
    RoomID INT NOT NULL,

    FOREIGN KEY (ReservationID) REFERENCES Reservations(ReservationID),
    FOREIGN KEY (RoomID) REFERENCES Rooms(RoomID)
);

-- =========================
-- PAYMENT METHODS
-- =========================
CREATE TABLE PaymentMethods (
    MethodID INT AUTO_INCREMENT PRIMARY KEY,
    MethodName VARCHAR(50) UNIQUE
);

INSERT INTO PaymentMethods (MethodName) VALUES
('cash'),
('credit_card'),
('debit_card'),
('online_payment');

-- =========================
-- PAYMENTS
-- =========================
CREATE TABLE Payments (
    PaymentID INT AUTO_INCREMENT PRIMARY KEY,
    ReservationID INT NOT NULL,
    MethodID INT NOT NULL,
    Amount DECIMAL(10,2) NOT NULL,
    PaymentStatus ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
    PaymentDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    TransactionReference VARCHAR(255),

    FOREIGN KEY (ReservationID) REFERENCES Reservations(ReservationID),
    FOREIGN KEY (MethodID) REFERENCES PaymentMethods(MethodID)
);

-- =========================
-- INDEXES
-- =========================
CREATE INDEX idx_reservation_dates
ON Reservations(CheckInDate, CheckOutDate);

CREATE INDEX idx_room_type
ON Rooms(RoomTypeID);