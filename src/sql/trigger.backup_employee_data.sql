CREATE TRIGGER `backup_employee_data` AFTER INSERT ON `employees`
 FOR EACH ROW INSERT INTO employees_backup SELECT * FROM employees WHERE employees.id = NEW.id