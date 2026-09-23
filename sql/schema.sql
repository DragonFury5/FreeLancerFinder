USE flf;

SELECT id, name, email, role FROM users WHERE email = 'admin@example.com';

SHOW COLUMNS FROM users LIKE 'role';