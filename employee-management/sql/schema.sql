-- Employee Management System Database Schema
-- Run this script in MySQL to create the database and table

CREATE DATABASE IF NOT EXISTS employee_management_db;
USE employee_management_db;

CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    department VARCHAR(100) NOT NULL,
    designation VARCHAR(100) NOT NULL,
    salary DECIMAL(10, 2) NOT NULL,
    hire_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample data
INSERT INTO employees (name, email, phone, department, designation, salary, hire_date) VALUES
('Rahul Sharma', 'rahul.sharma@company.com', '9876543210', 'Engineering', 'Software Engineer', 75000.00, '2023-06-15'),
('Priya Patel', 'priya.patel@company.com', '9876543211', 'Marketing', 'Marketing Manager', 85000.00, '2022-03-20'),
('Amit Kumar', 'amit.kumar@company.com', '9876543212', 'Human Resources', 'HR Executive', 55000.00, '2024-01-10'),
('Sneha Reddy', 'sneha.reddy@company.com', '9876543213', 'Finance', 'Financial Analyst', 70000.00, '2023-09-05'),
('Vikram Singh', 'vikram.singh@company.com', '9876543214', 'Engineering', 'Senior Developer', 95000.00, '2021-11-25');
