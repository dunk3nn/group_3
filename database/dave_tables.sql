-- Dave's tables for the Border Entry Payment and Personnel Tracking System

CREATE TABLE Departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL
);

CREATE TABLE Personnel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(50),
    department_id INT,
    contact_number VARCHAR(15),
    FOREIGN KEY (department_id) REFERENCES Departments(id)
);

CREATE TABLE Shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shift_name VARCHAR(50) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL
);

CREATE TABLE BorderStations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(100)
);

CREATE TABLE Checkpoint_Assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personnel_id INT NOT NULL,
    shift_id INT NOT NULL,
    border_station_id INT NOT NULL,
    assignment_date DATE NOT NULL,
    FOREIGN KEY (personnel_id) REFERENCES Personnel(id),
    FOREIGN KEY (shift_id) REFERENCES Shifts(id),
    FOREIGN KEY (border_station_id) REFERENCES BorderStations(id)
);

