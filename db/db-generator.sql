-- dev. @flasapp
-- SINGLE TABLE GENERATOR

DROP TABLE IF EXISTS users;

CREATE TABLE users(
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(50),
	email VARCHAR(50),
	password VARCHAR(300),
	role VARCHAR(20),
  googleId VARCHAR(100),
  facebookId VARCHAR(100),
	createdAt DATETIME,
	updateAt DATETIME,
	deleted TINYINT(1),
  image VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS images (
  id INT AUTO_INCREMENT PRIMARY KEY
);

CREATE TABLE reservations(
  id INT AUTO_INCREMENT PRIMARY KEY,
  reservationDate VARCHAR(50),
  userId VARCHAR(10),
  adminId VARCHAR(10),
  createdAt DATETIME,
  updateAt DATETIME,
  deleted TINYINT(1)
);
