CREATE TRIGGER `backup_employee_data` AFTER UPDATE ON `employees`
 FOR EACH ROW INSERT INTO employees_backup SELECT * FROM employees WHERE employees.primary_key = NEW.primary_key
