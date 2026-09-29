CREATE INDEX idx_accounts_token ON accountstable (token);
CREATE INDEX idx_bookings_car_dates ON bookingtable (carID, book_date, return_date);
CREATE INDEX idx_bookings_user ON bookingtable (userID);
CREATE INDEX idx_billing_booking ON billingtable (bookingID);
CREATE INDEX idx_cars_deleted ON carstable (isdeleted);
