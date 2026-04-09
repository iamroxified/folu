-- Create student_payments table with exact columns as provided
CREATE TABLE IF NOT EXISTS student_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_link INT NOT NULL,
    fee_structure_link INT NOT NULL,
    amount_due DECIMAL(10,2) NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    balance DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) NOT NULL,
    due_date DATE,
    payment_date DATE,
    payment_method VARCHAR(50),
    receipt_number VARCHAR(50),
    academic_session_link INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


d 	student_link 	fee_structure_link 	amount_due 	amount_paid 	balance 	status 	due_date 	payment_date 	payment_method 	receipt_number 	academic_session_link 	description 	created_at 	updated_at