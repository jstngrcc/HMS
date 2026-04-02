USE HMS;

-- =========================
-- USERS
-- =========================

DELIMITER $$

CREATE PROCEDURE CreateGuestUser(
    IN pEmailUser VARCHAR(150),
    IN pEmailGuest VARCHAR(150),
    IN pPasswordHash VARCHAR(255),
    IN pFirstName VARCHAR(100),
    IN pLastName VARCHAR(100),
    IN pPhone VARCHAR(20),
    IN pBirthDate DATE
)
BEGIN

    DECLARE newGuestID INT;

    START TRANSACTION;

    -- Step 1: Create Guest FIRST
    INSERT INTO Guests
    (Email, FirstName, LastName, PhoneContact, BirthDate)
    VALUES
    (pEmailGuest, pFirstName, pLastName, pPhone, pBirthDate);

    SET newGuestID = LAST_INSERT_ID();

    -- Step 2: Create User linked to Guest
    INSERT INTO Users
    (GuestID, RoleID, Email, PasswordHash)
    VALUES
    (newGuestID, 2, pEmailUser, pPasswordHash);

    COMMIT;

END$$

DELIMITER ;

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE CreateUser(
    IN pGuestID INT,
    IN pRoleID INT,
    IN pEmail VARCHAR(150),
    IN pPasswordHash VARCHAR(255)
)
BEGIN

    INSERT INTO Users
    (GuestID, RoleID, Email, PasswordHash)
    VALUES
    (pGuestID, pRoleID, pEmail, pPasswordHash);

END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE CreateGuest(
    IN pEmail VARCHAR(150),
    IN pFirstName VARCHAR(100),
    IN pLastName VARCHAR(100),
    IN pPhone VARCHAR(20),
    IN pBirthDate DATE,
    OUT newGuestID INT
)
BEGIN
    INSERT INTO Guests (Email, FirstName, LastName, PhoneContact, BirthDate)
    VALUES (pEmail, pFirstName, pLastName, pPhone, pBirthDate);

    SET newGuestID = LAST_INSERT_ID();
END$$

DELIMITER ;

-- =========================
-- ROOMS
-- =========================

DELIMITER $$

CREATE PROCEDURE GetRoomPrice(
    IN pRoomID INT,
    OUT pBasePrice DECIMAL(10,2)
)
BEGIN
    SELECT rt.BasePrice
    INTO pBasePrice
    FROM Rooms r
    JOIN RoomTypes rt ON r.RoomTypeID = rt.RoomTypeID
    WHERE r.RoomID = pRoomID;
END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE GetRoomName(
    IN pRoomID INT,
    OUT pRoomTypeName VARCHAR(100)
)
BEGIN
    SELECT rt.RoomTypeName
    INTO pRoomTypeName
    FROM Rooms r
    JOIN RoomTypes rt ON r.RoomTypeID = rt.RoomTypeID
    WHERE r.RoomID = pRoomID;
END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE GetAvailableRooms(
    IN pCheckIn DATE,
    IN pCheckOut DATE
)
BEGIN

    IF pCheckIn >= pCheckOut THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Check-out must be after check-in';
    END IF;

    SELECT r.RoomID, r.RoomNumber, rt.RoomTypeName, rt.BasePrice
    FROM Rooms r
    JOIN RoomTypes rt ON r.RoomTypeID = rt.RoomTypeID
    WHERE r.Status = 'available'
    AND r.RoomID NOT IN (

        SELECT rr.RoomID
        FROM ReservationRooms rr
        JOIN Reservations res ON rr.ReservationID = res.ReservationID
        WHERE res.StatusID NOT IN (4,5)
        AND rr.CheckInDate < pCheckOut
        AND rr.CheckOutDate > pCheckIn

    );

END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE CheckRoomAvailability(
    IN pRoomID INT,
    IN pCheckIn DATE,
    IN pCheckOut DATE,
    OUT isAvailable BOOLEAN
)
BEGIN
    DECLARE overlapCount INT;

    SELECT COUNT(*) INTO overlapCount
    FROM ReservationRooms rr
    JOIN Reservations r ON rr.ReservationID = r.ReservationID
    WHERE rr.RoomID = pRoomID
      AND r.StatusID IN (1,2,3)
      AND NOT (rr.CheckOutDate <= pCheckIn OR rr.CheckInDate >= pCheckOut);

    IF overlapCount = 0 THEN
        SET isAvailable = TRUE;
    ELSE
        SET isAvailable = FALSE;
    END IF;
END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE SearchAvailableRooms(
    IN pCheckIn DATE,
    IN pCheckOut DATE,
    IN pAdults INT,
    IN pChildren INT,
    IN pRoom VARCHAR(50),
    IN pRoomType VARCHAR(50)  -- now a string like 'Standard', 'Deluxe', 'Suite'
)
BEGIN
    SELECT r.RoomNumber, rt.RoomTypeName, rt.BasePrice,
           rt.MaxOccupancy, rt.BedCount, bt.BedName
    FROM Rooms r
    JOIN RoomTypes rt ON r.RoomTypeID = rt.RoomTypeID
    LEFT JOIN BedTypes bt ON rt.BedTypeID = bt.BedTypeID
    WHERE r.Status = 'available'

    -- Adults + Children (total occupancy)
    AND (
        pAdults IS NULL OR
        rt.MaxOccupancy >= (pAdults + IFNULL(pChildren, 0))
    )

    -- Room type (dropdown)
    AND (pRoomType IS NULL OR rt.RoomTypeName LIKE CONCAT(pRoomType, '%'))

    -- Room radio (single/double)
    AND (
        pRoom IS NULL OR
        (pRoom = 'single' AND rt.BedCount = 1) OR
        (pRoom = 'double' AND rt.BedCount >= 2)
    )

    -- Availability
    AND (
        pCheckIn IS NULL OR pCheckOut IS NULL OR
        NOT EXISTS (
            SELECT 1
            FROM ReservationRooms rr
            JOIN Reservations res ON rr.ReservationID = res.ReservationID
            WHERE rr.RoomID = r.RoomID
            AND rr.CheckInDate < pCheckOut
            AND rr.CheckOutDate > pCheckIn
        )
    )

    ORDER BY rt.BasePrice ASC;
END$$

DELIMITER ;

-- =========================
-- CARTS
-- =========================
DELIMITER $$

CREATE PROCEDURE AddRoomToCart(
    IN pCartID INT,
    IN pRoomID INT,
    IN pCheckIn DATE,
    IN pCheckOut DATE,
    IN pNumAdults INT
)
BEGIN
    DECLARE isAvailable BOOLEAN;

    -- Validate dates
    IF pCheckIn >= pCheckOut THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Check-out must be after check-in';
    END IF;

    -- Validate number of guests
    IF pNumAdults <= 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Must have at least 1 guest';
    END IF;

    -- Check if room is already booked
    CALL CheckRoomAvailability(pRoomID, pCheckIn, pCheckOut, isAvailable);

    IF isAvailable = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Room not available for these dates';
    END IF;

    -- Add room to cart
    INSERT INTO CartRooms (CartID, RoomID, CheckInDate, CheckOutDate, NumAdults)
    VALUES (pCartID, pRoomID, pCheckIn, pCheckOut, pNumAdults);
END$$

DELIMITER ;

-- =========================
-- RESERVATION
-- =========================

DELIMITER $$

CREATE PROCEDURE CreateReservation(
    IN pGuestID INT,
    IN pPaymentMethodID INT,
    IN pAmount DECIMAL(10,2),
    OUT pReservationID INT,
    OUT pBookingToken CHAR(36)
)
BEGIN
    DECLARE isAvailable BOOLEAN;
    DECLARE token CHAR(36);

    START TRANSACTION;
    -- Generate unique booking token
    SET token = UUID();

    -- Insert reservation
    INSERT INTO Reservations (GuestID, StatusID, BookingToken)
    VALUES (pGuestID, 1, token);

    SET pReservationID = LAST_INSERT_ID();
    SET pBookingToken = token;

    -- Insert payment
    INSERT INTO Payments (ReservationID, MethodID, Amount, PaymentStatus)
    VALUES (pReservationID, pPaymentMethodID, pAmount, 'pending');

    COMMIT;

    SELECT pReservationID AS ReservationID, pBookingToken AS BookingToken;
END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE AddRoomToReservation(
    IN pReservationID INT,
    IN pRoomID INT,
    IN pCheckIn DATE,
    IN pCheckOut DATE,
    IN pNumAdults INT,
    OUT pSuccess BOOLEAN,
    OUT pMessage VARCHAR(255)
)
BEGIN
    DECLARE isAvailable BOOLEAN;

    -- Lock room row to prevent concurrent booking
    SELECT RoomID
    FROM Rooms
    WHERE RoomID = pRoomID
    FOR UPDATE;

    -- Check availability
    CALL CheckRoomAvailability(pRoomID, pCheckIn, pCheckOut, isAvailable);

    IF isAvailable = 0 THEN
        SET pSuccess = FALSE;
        SET pMessage = 'Room already booked for these dates';
    ELSE
        INSERT INTO ReservationRooms
        (ReservationID, CheckInDate, CheckOutDate, NumAdults, RoomID)
        VALUES (pReservationID, pCheckIn, pCheckOut, pNumAdults, pRoomID);

        SET pSuccess = TRUE;
        SET pMessage = 'Room added successfully';
    END IF;
END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE CancelReservation(
    IN pReservationID INT
)
BEGIN
    DECLARE roomID INT;
    DECLARE done INT DEFAULT 0;

    START TRANSACTION;

    UPDATE Rooms
    SET Status = 'available'
    WHERE RoomID IN (
        SELECT RoomID 
        FROM ReservationRooms 
        WHERE ReservationID = pReservationID
    );

    UPDATE Reservations
    SET StatusID = 5
    WHERE ReservationID = pReservationID;

    UPDATE Payments
    SET PaymentStatus = 'refunded'
    WHERE ReservationID = pReservationID;

    COMMIT;
END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE CancelReservationGuest(
    IN pBookingToken CHAR(36)
)
BEGIN
    DECLARE reservationID INT;
    DECLARE roomID INT;
    DECLARE done INT DEFAULT 0;

    -- Cursor to free rooms
    DECLARE roomCursor CURSOR FOR
        SELECT RoomID FROM ReservationRooms WHERE ReservationID = reservationID;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    -- Find reservation by token
    SELECT ReservationID INTO reservationID
    FROM Reservations
    WHERE BookingToken = pBookingToken
      AND TokenExpiresAt IS NULL OR TokenExpiresAt > NOW()
    FOR UPDATE;

    IF reservationID IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid or expired booking token';
    END IF;


    START TRANSACTION;

    OPEN roomCursor;
    read_loop: LOOP
        FETCH roomCursor INTO roomID;
        IF done THEN LEAVE read_loop; END IF;

        UPDATE Rooms
        SET Status = 'available'
        WHERE RoomID = roomID;
    END LOOP;
    CLOSE roomCursor;

    UPDATE Reservations
    SET StatusID = 5
    WHERE ReservationID = reservationID;

    UPDATE Payments
    SET PaymentStatus = 'refunded'
    WHERE ReservationID = reservationID;

    COMMIT;
END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE CheckInGuest(
    IN pReservationID INT
)
BEGIN

    DECLARE roomID INT;
    DECLARE done INT DEFAULT 0;
    DECLARE currentStatus INT;

    DECLARE roomCursor CURSOR FOR
        SELECT RoomID 
        FROM ReservationRooms 
        WHERE ReservationID = pReservationID;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    SELECT StatusID INTO currentStatus
    FROM Reservations
    WHERE ReservationID = pReservationID;

    IF currentStatus != 2 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "Only confirmed reservations can be checked in";
    ELSE

    START TRANSACTION;

    UPDATE Reservations
    SET StatusID = 3
    WHERE ReservationID = pReservationID;

    OPEN roomCursor;
    read_loop: LOOP
        FETCH roomCursor INTO roomID;
        IF done THEN
            LEAVE read_loop;
        END IF;

        UPDATE Rooms
        SET Status = 'occupied'
        WHERE RoomID = roomID;
    END LOOP;
    CLOSE roomCursor;

    COMMIT;
    END IF;

END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE CheckOutGuest(
    IN pReservationID INT
)
BEGIN

    DECLARE roomID INT;
    DECLARE done INT DEFAULT 0;
    DECLARE currentStatus INT;

    DECLARE roomCursor CURSOR FOR
        SELECT RoomID FROM ReservationRooms WHERE ReservationID = pReservationID;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    SELECT StatusID INTO currentStatus
    FROM Reservations
    WHERE ReservationID = pReservationID;

    IF currentStatus != 3 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "Only checked-in reservations can be checked out";
    ELSE

    START TRANSACTION;

    UPDATE Reservations
    SET StatusID = 4
    WHERE ReservationID = pReservationID;

    OPEN roomCursor;
    read_loop: LOOP
        FETCH roomCursor INTO roomID;
        IF done THEN
            LEAVE read_loop;
        END IF;

        UPDATE Rooms
        SET Status = 'available'
        WHERE RoomID = roomID;
    END LOOP;
    CLOSE roomCursor;

    COMMIT;
    END IF;

END$$

DELIMITER ;

-- COMPLETE PAYMENT

DELIMITER $$

CREATE PROCEDURE CompletePayment(
    IN pReservationID INT,
    IN pTransactionRef VARCHAR(255)
)
BEGIN

    START TRANSACTION;

    UPDATE Payments
    SET PaymentStatus = 'completed',
        TransactionReference = pTransactionRef,
        PaymentDate = CURRENT_TIMESTAMP
    WHERE ReservationID = pReservationID;

    UPDATE Reservations
    SET StatusID = 2
    WHERE ReservationID = pReservationID;

    COMMIT;

END$$

DELIMITER ;

-- FAILED PAYMENT

DELIMITER $$

CREATE PROCEDURE FailPayment(
    IN pReservationID INT
)
BEGIN

    START TRANSACTION;

    UPDATE Payments
    SET PaymentStatus = 'failed'
    WHERE ReservationID = pReservationID;

    UPDATE Reservations
    SET StatusID = 5
    WHERE ReservationID = pReservationID;

    COMMIT;

END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE BookRoomsAtomic(
    IN pGuestID INT,
    IN pPaymentMethodID INT,
    IN pAmount DECIMAL(10,2),
    IN pCartJSON TEXT,
    IN pDiscountType VARCHAR(50),
    IN pDiscountValue DECIMAL(10,2),
    IN pDiscountCardNumber VARCHAR(50),
    OUT pReservationID INT,
    OUT pBookingToken CHAR(36),
    OUT pSuccess BOOLEAN,
    OUT pMessage VARCHAR(255)
)
BEGIN
    DECLARE room JSON;
    DECLARE i INT DEFAULT 0;
    DECLARE n INT;
    DECLARE isAvailable BOOLEAN;
    DECLARE token CHAR(36);
    DECLARE roomNumber VARCHAR(20);

    START TRANSACTION;

    -- Create reservation
    SET token = UUID();
    INSERT INTO Reservations (GuestID, StatusID, BookingToken)
    VALUES (pGuestID, 1, token);

    SET pReservationID = LAST_INSERT_ID();
    SET pBookingToken = token;

    -- Insert payment
    INSERT INTO Payments (ReservationID, MethodID, Amount, PaymentStatus)
    VALUES (pReservationID, pPaymentMethodID, pAmount, 'pending');

    -- Insert discount info if any
    IF pDiscountType IS NOT NULL AND pDiscountValue > 0 THEN
        INSERT INTO ReservationDiscounts
        (ReservationID, DiscountType, DiscountValue, CardNumber)
        VALUES (pReservationID, pDiscountType, pDiscountValue, pDiscountCardNumber);
    END IF;

    -- Get number of rooms
    SET n = JSON_LENGTH(pCartJSON);

    room_loop: WHILE i < n DO
        SET room = JSON_EXTRACT(pCartJSON, CONCAT('$[', i, ']'));

        -- Lock the room row
        SELECT RoomID INTO @rid
        FROM Rooms
        WHERE RoomID = JSON_UNQUOTE(JSON_EXTRACT(room, '$.RoomID'))
        FOR UPDATE;

        -- Check availability
        CALL CheckRoomAvailability(
            JSON_UNQUOTE(JSON_EXTRACT(room, '$.RoomID')),
            JSON_UNQUOTE(JSON_EXTRACT(room, '$.CheckInDate')),
            JSON_UNQUOTE(JSON_EXTRACT(room, '$.CheckOutDate')),
            @isAvailable
        );

        SET isAvailable = @isAvailable;

        IF isAvailable = 0 THEN
            SELECT RoomNumber INTO roomNumber
            FROM Rooms
            WHERE RoomID = CAST(JSON_UNQUOTE(JSON_EXTRACT(room, '$.RoomID')) AS UNSIGNED);

            SET pSuccess = FALSE;
            SET pMessage = CONCAT('Room ', roomNumber, ' is already booked');
            ROLLBACK;
            LEAVE room_loop;
        ELSE
            INSERT INTO ReservationRooms
            (ReservationID, CheckInDate, CheckOutDate, NumAdults, RoomID)
            VALUES (
                pReservationID,
                JSON_UNQUOTE(JSON_EXTRACT(room, '$.CheckInDate')),
                JSON_UNQUOTE(JSON_EXTRACT(room, '$.CheckOutDate')),
                JSON_UNQUOTE(JSON_EXTRACT(room, '$.NumAdults')),
                JSON_UNQUOTE(JSON_EXTRACT(room, '$.RoomID'))
            );
        END IF;

        SET i = i + 1;
    END WHILE;

    -- Commit if all rooms added successfully
    IF i = n THEN
        SET pSuccess = TRUE;
        SET pMessage = 'Reservation successful';
        COMMIT;
    END IF;

END$$

DELIMITER ;