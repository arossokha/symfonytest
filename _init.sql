CREATE DATABASE jobtest COLLATE utf8_general_ci;
use jobtest;
CREATE USER 'jobtest'@'localhost' IDENTIFIED BY  'jobtest';
GRANT ALL PRIVILEGES ON  `jobtest` . * TO  'jobtest'@'localhost';