CREATE TABLE Checkpoints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    checkpoint_name VARCHAR(100) NOT NULL,
    location VARCHAR(100)
);

CREATE TABLE Lanes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    checkpoint_id INT NOT NULL,
    lane_number INT NOT NULL,
    FOREIGN KEY (checkpoint_id) REFERENCES Checkpoints(id)
);

CREATE TABLE Vehicle_Types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL,
    base_fee DECIMAL(10,2) NOT NULL
);

INSERT INTO Checkpoints (checkpoint_name, location) VALUES
('Main Entry Checkpoint', 'Border Entry Point');

INSERT INTO Lanes (checkpoint_id, lane_number) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4);

INSERT INTO Vehicle_Types (type_name, base_fee) VALUES
('Motorcycle', 20.00),
('Car', 50.00),
('Van', 80.00),
('Bus', 100.00),
('Truck', 150.00);





INSERT INTO Checkpoints (checkpoint_name, location) VALUES (?, ?);
INSERT INTO Lanes (checkpoint_id, lane_number) VALUES (?, ?);
INSERT INTO Vehicle_Types (type_name, base_fee) VALUES (?, ?);


UPDATE Checkpoints SET checkpoint_name = ?, location = ? WHERE id = ?;
UPDATE Lanes SET checkpoint_id = ?, lane_number = ? WHERE id = ?;
UPDATE Vehicle_Types SET type_name = ?, base_fee = ? WHERE id = ?;


DELETE FROM Checkpoints WHERE id = ?;
DELETE FROM Lanes WHERE id = ?;
DELETE FROM Vehicle_Types WHERE id = ?;


SELECT 
    Lanes.id AS lane_id,
    Lanes.lane_number AS lane_identifier,
    Checkpoints.checkpoint_name AS active_checkpoint,
    Checkpoints.location AS coordinate_location
FROM Lanes
INNER JOIN Checkpoints 
    ON Lanes.checkpoint_id = Checkpoints.id
ORDER BY Lanes.id ASC;
