CREATE TABLE IF NOT EXISTS userstable (
    userID INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    contact_no VARCHAR(30) NOT NULL,
    isdeleted TINYINT(1) NOT NULL DEFAULT 0,
    drivers_license VARCHAR(255) NOT NULL,
    PRIMARY KEY (userID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS accountstable (
    id INT NOT NULL AUTO_INCREMENT,
    userID INT NOT NULL,
    user_email VARCHAR(255) NOT NULL,
    user_password VARCHAR(255) NOT NULL,
    token VARCHAR(255) NULL,
    token_expires_at DATETIME NULL,
    vip_points DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    vip_access TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_account_user (userID),
    UNIQUE KEY uq_account_email (user_email),
    CONSTRAINT fk_account_user FOREIGN KEY (userID) REFERENCES userstable (userID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS carstable (
    carID INT NOT NULL AUTO_INCREMENT,
    car_brand VARCHAR(100) NOT NULL,
    car_model VARCHAR(100) NOT NULL,
    manu_year VARCHAR(10) NULL,
    daily_rate DECIMAL(12,2) NOT NULL,
    AC TINYINT(1) NULL,
    seating_capacity INT NULL,
    plate_no VARCHAR(30) NULL,
    isdeleted TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (carID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bookingtable (
    bookingID INT NOT NULL AUTO_INCREMENT,
    carID INT NOT NULL,
    userID INT NOT NULL,
    daily_rate DECIMAL(12,2) NOT NULL,
    book_date DATETIME NOT NULL,
    return_date DATETIME NOT NULL,
    total_cost DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (bookingID),
    CONSTRAINT fk_booking_car FOREIGN KEY (carID) REFERENCES carstable (carID),
    CONSTRAINT fk_booking_user FOREIGN KEY (userID) REFERENCES userstable (userID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS billingtable (
    billingID INT NOT NULL AUTO_INCREMENT,
    bookingID INT NOT NULL,
    carID INT NULL,
    daily_rate DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_cost DECIMAL(12,2) NULL,
    amount_paid DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (billingID),
    CONSTRAINT fk_billing_booking FOREIGN KEY (bookingID) REFERENCES bookingtable (bookingID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
