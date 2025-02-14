Use bhxh;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    dob DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    id_number VARCHAR(20) NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    card_number VARCHAR(10) UNIQUE,
    status ENUM('active', 'inactive') DEFAULT 'inactive',
    last_payment_date DATE
);

CREATE TABLE medical_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    card_number VARCHAR(10) NOT NULL,
    hospital_name VARCHAR(255) NOT NULL, -- Tên bệnh viện
    disease VARCHAR(255) NOT NULL,       -- Tên bệnh
    hospital_fee DECIMAL(10, 0) NOT NULL, -- Viện phí
    is_paid BOOLEAN DEFAULT FALSE,       -- Đã tự thanh toán chưa (TRUE/FALSE)
    is_reimbursed BOOLEAN DEFAULT FALSE, -- Đã được BHXH hoàn tiền chưa (TRUE/FALSE)
    admission_date DATE NOT NULL,        -- Ngày vào viện
    discharge_date DATE NOT NULL,        -- Ngày ra viện
    FOREIGN KEY (card_number) REFERENCES users(card_number) ON DELETE CASCADE
);

    -- INSERT INTO medical_records (card_number, hospital_name, disease, hospital_fee, is_paid, is_reimbursed, admission_date, discharge_date)
    -- VALUES (5627716764, 'Bệnh viện Bạch Mai', 'Viêm phổi', 5000000, 1, 0, '2023-10-01', '2023-10-10');

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id)
);
