/*Usage: mysql -u '' -p < init.sql */

DROP DATABASE IF EXISTS BookReader;
CREATE DATABASE BookReader;
USE BookReader;
CREATE TABLE books
(
	Id INTEGER PRIMARY KEY AUTO_INCREMENT,
	Title VARCHAR(255),
	Author VARCHAR(255),
	Description VARCHAR(255),
	Width INTEGER,
	Height INTEGER,
	FirstLeft TINYINT,
	Cover VARCHAR(255)
);
