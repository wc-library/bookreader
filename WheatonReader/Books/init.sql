/*Usage: sqlite3 books.db < init.sql */

CREATE TABLE books (
  Id INTEGER PRIMARY KEY,
  Title VARCHAR(255),
  Author VARCHAR(255),
  Description VARCHAR(255),
  Directory VARCHAR(255),
  Prefix VARCHAR(255),
  Extension VARCHAR(255),
  Width INTEGER,
  Height INTEGER,
  NumPages INTEGER,
  FirstLeft INTEGER,
  Cover VARCHAR(255)
);
