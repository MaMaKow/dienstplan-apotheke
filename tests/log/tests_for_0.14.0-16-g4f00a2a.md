These things should be done before merging changes into master:

* Login
  + Wenn das Passwort falsch war, bitte den eingegebenen Benutzernamen wieder vorgeben. Das ersparrt Tipparbeit.
* Change password with
  * lost_password.php
    + Sieht nicht so gut aus, wenn kein vernünftiger Input kommt.
      Dann wird es einfach weiß.
   * reset_lost_password.php
     + Invalid token, Invalid token, Invalid token das sieht auch nicht so besonders gut aus, wenn ein ungültiger Token verwendet wird.
* Create user
  - <br /><b>Notice</b>:  Undefined variable: user_name in <b>/var/www/html/apotheke/dienstplan-development/src/php/register.php</b> on line <b>95</b><br />
  - <br /><b>Notice</b>:  Undefined variable: employee_id in <b>/var/www/html/apotheke/dienstplan-development/src/php/register.php</b> on line <b>96</b><br />
  - <br /><b>Notice</b>:  Undefined variable: email in <b>/var/www/html/apotheke/dienstplan-development/src/php/register.php</b> on line <b>97</b><br />

* Roster input
  * Create a roster from scratch
    + works
  * Edit the roster
    * Exchange an employee with another one.
      + works
    * Drag and drop
      + works
      *	drag the duty times
        + works
      * drag the break times
        + works
  * Provoke some errors:
    + work all
    * Not enough employees at starting time
    * No pharmacist at starting time
    * No goods reciept employee at starting time
    * One employee not scheduled
    * One employee scheduled although absent
  * Add an entry at the end of the roster
  * Look at one sunday/empty roster
    + works
  * Saturday rotation
    + works
  * Write a mail using the contact form
    + works
  * Disapprove roster
    + works
  * Approve roster
    + works
* Roster output
    
  * View roster in read mode
    * Navigation with keyboard
    * View the print version
      + works
    * use the datepicker in/for old browsers
      - does not work in iexplore 11
  * View the roster in read mode weekly table.
    * Task rotation
      + works
    * Keyboard navigation
      + works
  * View the roster in read mode weekly images.
      + works
  * View the roster in employee table
    * download ICS file
    * validate ICS file (https://icalendar.org/validator.html)
      + works
* Principle roster
  * Principle roster day
    - There was an error while querying the database. Please see the error log for more details!
    array (
      0 => '23000',
      1 => 1062,
      2 => 'Duplicate entry \'0-12-1-1\' for key \'PRIMARY\'',
    ),
  )),
[29-Jul-2019 15:17:36 Europe/Berlin] in file: /var/www/html/apotheke/dienstplan-development/src/php/classes/class.database_wrapper.php
 on line: 257
 variable: $exception
 value:
 array (
  0 => 
  PDOException::__set_state(array(
     'message' => 'SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry \'0-12-1-1\' for key \'PRIMARY\'',
     'string' => '',
     'code' => '23000',
     'file' => '/var/www/html/apotheke/dienstplan-development/src/php/classes/class.database_wrapper.php',
     'line' => 121,
     'trace' => 
    array (
      0 => 
      array (
        'file' => '/var/www/html/apotheke/dienstplan-development/src/php/classes/class.database_wrapper.php',
        'line' => 121,
        'function' => 'execute',
        'class' => 'PDOStatement',
        'type' => '->',
        'args' => 
        array (
          0 => 
          array (
            'employee_id' => 12,
            'weekday' => '1',
            'alternation_id' => 0,
            'duty_start' => '12:00',
            'duty_end' => '20:00',
            'break_start' => '11:30',
            'break_end' => '12:00',
            'working_hours' => 7.5,
            'branch_id' => 1,
            'valid_from' => '2019-07-29',
            'comment' => NULL,
          ),
        ),
      ),
      1 => 
      array (
        'file' => '/var/www/html/apotheke/dienstplan-development/src/php/classes/class.principle_roster.php',
        'line' => 456,
        'function' => 'run',
        'class' => 'database_wrapper',
        'type' => '->',
        'args' => 
        array (
          0 => 'INSERT INTO `principle_roster`  SET `employee_id` = :employee_id,  `branch_id` = :branch_id,  `weekday` = :weekday,  `alternation_id` = :alternation_id,  `duty_start` = :duty_start, `duty_end` = :duty_end, `break_start` = :break_start, `break_end` = :break_end, `working_hours` = :working_hours,  `valid_from` = :valid_from,  `comment` = :comment;',
          1 => 
          array (
            'employee_id' => 12,
            'weekday' => '1',
            'alternation_id' => 0,
            'duty_start' => '12:00',
            'duty_end' => '20:00',
            'break_start' => '11:30',
            'break_end' => '12:00',
            'working_hours' => 7.5,
            'branch_id' => 1,
            'valid_from' => '2019-07-29',
            'comment' => NULL,
          ),
        ),
      ),
      2 => 
      array (
        'file' => '/var/www/html/apotheke/dienstplan-development/src/php/classes/class.principle_roster.php',
        'line' => 339,
        'function' => 'insert_new_entry_into_db',
        'class' => 'principle_roster',
        'type' => '::',
        'args' => 
        array (
          0 => 
          roster_item::__set_state(array(
             'date_sql' => '2019-07-29',
             'date_unix' => 1564351200,
             'date_object' => 
            DateTime::__set_state(array(
               'date' => '2019-07-29 00:00:00.000000',
               'timezone_type' => 3,
               'timezone' => 'Europe/Berlin',
            )),
             'employee_id' => 12,
             'branch_id' => 1,
             'comment' => NULL,
             'duty_start_int' => 43200,
             'duty_start_sql' => '12:00',
             'duty_end_int' => 72000,
             'duty_end_sql' => '20:00',
             'break_start_int' => 41400,
             'break_start_sql' => '11:30',
             'break_end_int' => 43200,
             'break_end_sql' => '12:00',
             'working_hours' => 7.5,
             'break_duration' => 1800,
             'duty_duration' => 28800,
             'working_seconds' => 27000,
             'weekday' => '1',
          )),
          1 => 0,
          2 => '2019-07-29',
        ),
      ),
      3 => 
      array (
        'file' => '/var/www/html/apotheke/dienstplan-development/src/php/pages/principle-roster-day.php',
        'line' => 66,
        'function' => 'insert_changed_entries_into_database',
        'class' => 'principle_roster',
        'type' => '::',
        'args' => 
        array (
          0 => 
          array (
            1564351200 => 
            array (
              0 => 
              roster_item::__set_state(array(
                 'date_sql' => '2019-07-29',
                 'date_unix' => 1564351200,
                 'date_object' => 
                DateTime::__set_state(array(
                   'date' => '2019-07-29 00:00:00.000000',
                   'timezone_type' => 3,
                   'timezone' => 'Europe/Berlin',
                )),
                 'employee_id' => 12,
                 'branch_id' => 1,
                 'comment' => NULL,
                 'duty_start_int' => 43200,
                 'duty_start_sql' => '12:00',
                 'duty_end_int' => 72000,
                 'duty_end_sql' => '20:00',
                 'break_start_int' => 41400,
                 'break_start_sql' => '11:30',
                 'break_end_int' => 43200,
                 'break_end_sql' => '12:00',
                 'working_hours' => 7.5,
                 'break_duration' => 1800,
                 'duty_duration' => 28800,
                 'working_seconds' => 27000,
                 'weekday' => '1',
              )),
              1 => 
              roster_item::__set_state(array(
                 'date_sql' => '2019-07-29',
                 'date_unix' => 1564351200,
                 'date_object' => 
                DateTime::__set_state(array(
                   'date' => '2019-07-29 00:00:00.000000',
                   'timezone_type' => 3,
                   'timezone' => 'Europe/Berlin',
                )),
                 'employee_id' => 2,
                 'branch_id' => 1,
                 'comment' => NULL,
                 'duty_start_int' => 30600,
                 'duty_start_sql' => '08:30',
                 'duty_end_int' => 57600,
                 'duty_end_sql' => '16:00',
                 'break_start_int' => 45000,
                 'break_start_sql' => '12:30',
                 'break_end_int' => 46800,
                 'break_end_sql' => '13:00',
                 'working_hours' => 7.0,
                 'break_duration' => 1800,
                 'duty_duration' => 27000,
                 'working_seconds' => 25200,
                 'weekday' => '1',
              )),
              2 => 
              roster_item::__set_state(array(
                 'date_sql' => '2019-07-29',
                 'date_unix' => 1564351200,
                 'date_object' => 
                DateTime::__set_state(array(
                   'date' => '2019-07-29 00:00:00.000000',
                   'timezone_type' => 3,
                   'timezone' => 'Europe/Berlin',
                )),
                 'employee_id' => 21,
                 'branch_id' => 1,
                 'comment' => NULL,
                 'duty_start_int' => 34200,
                 'duty_start_sql' => '09:30',
                 'duty_end_int' => 54000,
                 'duty_end_sql' => '15:00',
                 'break_start_int' => 48600,
                 'break_start_sql' => '13:30',
                 'break_end_int' => 50400,
                 'break_end_sql' => '14:00',
                 'working_hours' => 5.0,
                 'break_duration' => 1800,
                 'duty_duration' => 19800,
                 'working_seconds' => 18000,
                 'weekday' => '1',
              )),
              3 => 
              roster_item::__set_state(array(
                 'date_sql' => '2019-07-29',
                 'date_unix' => 1564351200,
                 'date_object' => 
                DateTime::__set_state(array(
                   'date' => '2019-07-29 00:00:00.000000',
                   'timezone_type' => 3,
                   'timezone' => 'Europe/Berlin',
                )),
                 'employee_id' => 13,
                 'branch_id' => 1,
                 'comment' => NULL,
                 'duty_start_int' => 28800,
                 'duty_start_sql' => '08:00',
                 'duty_end_int' => 61200,
                 'duty_end_sql' => '17:00',
                 'break_start_int' => 43200,
                 'break_start_sql' => '12:00',
                 'break_end_int' => 45000,
                 'break_end_sql' => '12:30',
                 'working_hours' => 8.5,
                 'break_duration' => 1800,
                 'duty_duration' => 32400,
                 'working_seconds' => 30600,
                 'weekday' => '1',
              )),
              4 => 
              roster_item::__set_state(array(
                 'date_sql' => '2019-07-29',
                 'date_unix' => 1564351200,
                 'date_object' => 
                DateTime::__set_state(array(
                   'date' => '2019-07-29 00:00:00.000000',
                   'timezone_type' => 3,
                   'timezone' => 'Europe/Berlin',
                )),
                 'employee_id' => 6,
                 'branch_id' => 1,
                 'comment' => NULL,
                 'duty_start_int' => 30600,
                 'duty_start_sql' => '08:30',
                 'duty_end_int' => 63000,
                 'duty_end_sql' => '17:30',
                 'break_start_int' => 43200,
                 'break_start_sql' => '12:00',
                 'break_end_int' => 46800,
                 'break_end_sql' => '13:00',
                 'working_hours' => 8.0,
                 'break_duration' => 3600,
                 'duty_duration' => 32400,
                 'working_seconds' => 28800,
                 'weekday' => '1',
              )),
              5 => 
              roster_item::__set_state(array(
                 'date_sql' => '2019-07-29',
                 'date_unix' => 1564351200,
                 'date_object' => 
                DateTime::__set_state(array(
                   'date' => '2019-07-29 00:00:00.000000',
                   'timezone_type' => 3,
                   'timezone' => 'Europe/Berlin',
                )),
                 'employee_id' => 18,
                 'branch_id' => 1,
                 'comment' => NULL,
                 'duty_start_int' => 32400,
                 'duty_start_sql' => '09:00',
                 'duty_end_int' => 66600,
                 'duty_end_sql' => '18:30',
                 'break_start_int' => 46800,
                 'break_start_sql' => '13:00',
                 'break_end_int' => 48600,
                 'break_end_sql' => '13:30',
                 'working_hours' => 9.0,
                 'break_duration' => 1800,
                 'duty_duration' => 34200,
                 'working_seconds' => 32400,
                 'weekday' => '1',
              )),
              6 => 
              roster_item::__set_state(array(
                 'date_sql' => '2019-07-29',
                 'date_unix' => 1564351200,
                 'date_object' => 
                DateTime::__set_state(array(
                   'date' => '2019-07-29 00:00:00.000000',
                   'timezone_type' => 3,
                   'timezone' => 'Europe/Berlin',
                )),
                 'employee_id' => 7,
                 'branch_id' => 1,
                 'comment' => NULL,
                 'duty_start_int' => 36000,
                 'duty_start_sql' => '10:00',
                 'duty_end_int' => 66600,
                 'duty_end_sql' => '18:30',
                 'break_start_int' => 48600,
                 'break_start_sql' => '13:30',
                 'break_end_int' => 50400,
                 'break_end_sql' => '14:00',
                 'working_hours' => 8.0,
                 'break_duration' => 1800,
                 'duty_duration' => 30600,
                 'working_seconds' => 28800,
                 'weekday' => '1',
              )),
              7 => 
              roster_item::__set_state(array(
                 'date_sql' => '2019-07-29',
                 'date_unix' => 1564351200,
                 'date_object' => 
                DateTime::__set_state(array(
                   'date' => '2019-07-29 00:00:00.000000',
                   'timezone_type' => 3,
                   'timezone' => 'Europe/Berlin',
                )),
                 'employee_id' => 16,
                 'branch_id' => 1,
                 'comment' => NULL,
                 'duty_start_int' => 39600,
                 'duty_start_sql' => '11:00',
                 'duty_end_int' => 72000,
                 'duty_end_sql' => '20:00',
                 'break_start_int' => 52200,
                 'break_start_sql' => '14:30',
                 'break_end_int' => 54000,
                 'break_end_sql' => '15:00',
                 'working_hours' => 8.5,
                 'break_duration' => 1800,
                 'duty_duration' => 32400,
                 'working_seconds' => 30600,
                 'weekday' => '1',
              )),
              8 => 
              roster_item::__set_state(array(
                 'date_sql' => '2019-07-29',
                 'date_unix' => 1564351200,
                 'date_object' => 
                DateTime::__set_state(array(
                   'date' => '2019-07-29 00:00:00.000000',
                   'timezone_type' => 3,
                   'timezone' => 'Europe/Berlin',
                )),
                 'employee_id' => 5,
                 'branch_id' => 1,
                 'comment' => NULL,
                 'duty_start_int' => 41400,
                 'duty_start_sql' => '11:30',
                 'duty_end_int' => 72000,
                 'duty_end_sql' => '20:00',
                 'break_start_int' => 50400,
                 'break_start_sql' => '14:00',
                 'break_end_int' => 52200,
                 'break_end_sql' => '14:30',
                 'working_hours' => 8.0,
                 'break_duration' => 1800,
                 'duty_duration' => 30600,
                 'working_seconds' => 28800,
                 'weekday' => '1',
              )),
              9 => 
              roster_item::__set_state(array(
                 'date_sql' => '2019-07-29',
                 'date_unix' => 1564351200,
                 'date_object' => 
                DateTime::__set_state(array(
                   'date' => '2019-07-29 00:00:00.000000',
                   'timezone_type' => 3,
                   'timezone' => 'Europe/Berlin',
                )),
                 'employee_id' => 1,
                 'branch_id' => 1,
                 'comment' => NULL,
                 'duty_start_int' => 28800,
                 'duty_start_sql' => '08:00',
                 'duty_end_int' => 72000,
                 'duty_end_sql' => '20:00',
                 'break_start_int' => NULL,
                 'break_start_sql' => NULL,
                 'break_end_int' => NULL,
                 'break_end_sql' => NULL,
                 'working_hours' => 12.0,
                 'break_duration' => 0,
                 'duty_duration' => 43200,
                 'working_seconds' => 43200,
                 'weekday' => '1',
              )),
            ),
          ),
          1 => 
          array (
            1564351200 => 
            array (
              0 => 12,
              1 => 1,
            ),
          ),
          2 => '2019-07-29',
        ),
      ),
    ),
     'previous' => NULL,
     'errorInfo' => 
    array (
      0 => '23000',
      1 => 1062,
      2 => 'Duplicate entry \'0-12-1-1\' for key \'PRIMARY\'',
    ),
  )),
)

    * Change roster by drag and drop
      + works, but
        - the layout is not working beautiful
  * Principle roster employee
    * Change roster
There was an error while querying the database. Please see the error log for more details!
[29-Jul-2019 15:26:21 Europe/Berlin] in file: /var/www/html/apotheke/dienstplan-development/src/php/classes/class.database_wrapper.php
 on line: 257
 variable: $exception
 value:
 array (
  0 => 
  PDOException::__set_state(array(
     'message' => 'SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry \'0-5-1-1\' for key \'PRIMARY\'',
     'string' => '',
     'code' => '23000',
     'file' => '/var/www/html/apotheke/dienstplan-development/src/php/classes/class.database_wrapper.php',
     'line' => 121,
     'trace' => 
    array (
      0 => 
      array (
        'file' => '/var/www/html/apotheke/dienstplan-development/src/php/classes/class.database_wrapper.php',
        'line' => 121,
        'function' => 'execute',
        'class' => 'PDOStatement',
        'type' => '->',
        'args' => 
        array (
          0 => 
          array (
            'employee_id' => 5,
            'weekday' => '1',
            'alternation_id' => 0,
            'duty_start' => '12:30',
            'duty_end' => '21:00',
            'break_start' => '14:00',
            'break_end' => '14:30',
            'working_hours' => 8.0,
            'branch_id' => 1,
            'valid_from' => '2014-12-29',
            'comment' => NULL,
          ),
        ),
      ),
      1 => 
      array (
        'file' => '/var/www/html/apotheke/dienstplan-development/src/php/classes/class.principle_roster.php',
        'line' => 456,
        'function' => 'run',
        'class' => 'database_wrapper',
        'type' => '->',
        'args' => 
        array (
          0 => 'INSERT INTO `principle_roster`  SET `employee_id` = :employee_id,  `branch_id` = :branch_id,  `weekday` = :weekday,  `alternation_id` = :alternation_id,  `duty_start` = :duty_start, `duty_end` = :duty_end, `break_start` = :break_start, `break_end` = :break_end, `working_hours` = :working_hours,  `valid_from` = :valid_from,  `comment` = :comment;',
          1 => 
          array (
            'employee_id' => 5,
            'weekday' => '1',
            'alternation_id' => 0,
            'duty_start' => '12:30',
            'duty_end' => '21:00',
            'break_start' => '14:00',
            'break_end' => '14:30',
            'working_hours' => 8.0,
            'branch_id' => 1,
            'valid_from' => '2014-12-29',
            'comment' => NULL,
          ),
        ),
      ),
      2 => 
      array (
        'file' => '/var/www/html/apotheke/dienstplan-development/src/php/classes/class.principle_roster.php',
        'line' => 339,
        'function' => 'insert_new_entry_into_db',
        'class' => 'principle_roster',
        'type' => '::',
        'args' => 
        array (
          0 => 
          roster_item::__set_state(array(
             'date_sql' => '2014-12-29',
             'date_unix' => 1419807600,
             'date_object' => 
            DateTime::__set_state(array(
               'date' => '2014-12-29 00:00:00.000000',
               'timezone_type' => 3,
               'timezone' => 'Europe/Berlin',
            )),
             'employee_id' => 5,
             'branch_id' => 1,
             'comment' => NULL,
             'duty_start_int' => 45000,
             'duty_start_sql' => '12:30',
             'duty_end_int' => 75600,
             'duty_end_sql' => '21:00',
             'break_start_int' => 50400,
             'break_start_sql' => '14:00',
             'break_end_int' => 52200,
             'break_end_sql' => '14:30',
             'working_hours' => 8.0,
             'break_duration' => 1800,
             'duty_duration' => 30600,
             'working_seconds' => 28800,
             'weekday' => '1',
          )),
          1 => 0,
          2 => '2014-12-29',
        ),
      ),
      3 => 
      array (
        'file' => '/var/www/html/apotheke/dienstplan-development/src/php/pages/principle-roster-employee.php',
        'line' => 43,
        'function' => 'insert_changed_entries_into_database',
        'class' => 'principle_roster',
        'type' => '::',
        'args' => 
        array (
          0 => 
          array (
            1419807600 => 
            array (
              0 => 
              roster_item::__set_state(array(
                 'date_sql' => '2014-12-29',
                 'date_unix' => 1419807600,
                 'date_object' => 
                DateTime::__set_state(array(
                   'date' => '2014-12-29 00:00:00.000000',
                   'timezone_type' => 3,
                   'timezone' => 'Europe/Berlin',
                )),
                 'employee_id' => 5,
                 'branch_id' => 1,
                 'comment' => NULL,
                 'duty_start_int' => 45000,
                 'duty_start_sql' => '12:30',
                 'duty_end_int' => 75600,
                 'duty_end_sql' => '21:00',
                 'break_start_int' => 50400,
                 'break_start_sql' => '14:00',
                 'break_end_int' => 52200,
                 'break_end_sql' => '14:30',
                 'working_hours' => 8.0,
                 'break_duration' => 1800,
                 'duty_duration' => 30600,
                 'working_seconds' => 28800,
                 'weekday' => '1',
              )),
            ),
            1419894000 => 
            array (
              0 => 
              roster_item::__set_state(array(
                 'date_sql' => '2014-12-30',
                 'date_unix' => 1419894000,
                 'date_object' => 
                DateTime::__set_state(array(
                   'date' => '2014-12-30 00:00:00.000000',
                   'timezone_type' => 3,
                   'timezone' => 'Europe/Berlin',
                )),
                 'employee_id' => 5,
                 'branch_id' => 1,
                 'comment' => NULL,
                 'duty_start_int' => 36000,
                 'duty_start_sql' => '10:00',
                 'duty_end_int' => 66600,
                 'duty_end_sql' => '18:30',
                 'break_start_int' => 46800,
                 'break_start_sql' => '13:00',
                 'break_end_int' => 48600,
                 'break_end_sql' => '13:30',
                 'working_hours' => 8.0,
                 'break_duration' => 1800,
                 'duty_duration' => 30600,
                 'working_seconds' => 28800,
                 'weekday' => '2',
              )),
            ),
            1419980400 => 
            array (
              0 => 
              roster_item::__set_state(array(
                 'date_sql' => '2014-12-31',
                 'date_unix' => 1419980400,
                 'date_object' => 
                DateTime::__set_state(array(
                   'date' => '2014-12-31 00:00:00.000000',
                   'timezone_type' => 3,
                   'timezone' => 'Europe/Berlin',
                )),
                 'employee_id' => 5,
                 'branch_id' => 1,
                 'comment' => NULL,
                 'duty_start_int' => 28800,
                 'duty_start_sql' => '08:00',
                 'duty_end_int' => 54000,
                 'duty_end_sql' => '15:00',
                 'break_start_int' => 41400,
                 'break_start_sql' => '11:30',
                 'break_end_int' => 43200,
                 'break_end_sql' => '12:00',
                 'working_hours' => 6.5,
                 'break_duration' => 1800,
                 'duty_duration' => 25200,
                 'working_seconds' => 23400,
                 'weekday' => '3',
              )),
            ),
            1420066800 => 
            array (
              0 => 
              roster_item::__set_state(array(
                 'date_sql' => '2015-01-01',
                 'date_unix' => 1420066800,
                 'date_object' => 
                DateTime::__set_state(array(
                   'date' => '2015-01-01 00:00:00.000000',
                   'timezone_type' => 3,
                   'timezone' => 'Europe/Berlin',
                )),
                 'employee_id' => 5,
                 'branch_id' => 1,
                 'comment' => NULL,
                 'duty_start_int' => 30600,
                 'duty_start_sql' => '08:30',
                 'duty_end_int' => 61200,
                 'duty_end_sql' => '17:00',
                 'break_start_int' => 45000,
                 'break_start_sql' => '12:30',
                 'break_end_int' => 46800,
                 'break_end_sql' => '13:00',
                 'working_hours' => 8.0,
                 'break_duration' => 1800,
                 'duty_duration' => 30600,
                 'working_seconds' => 28800,
                 'weekday' => '4',
              )),
            ),
            1420153200 => 
            array (
              0 => 
              roster_item::__set_state(array(
                 'date_sql' => '2015-01-02',
                 'date_unix' => 1420153200,
                 'date_object' => 
                DateTime::__set_state(array(
                   'date' => '2015-01-02 00:00:00.000000',
                   'timezone_type' => 3,
                   'timezone' => 'Europe/Berlin',
                )),
                 'employee_id' => 5,
                 'branch_id' => 1,
                 'comment' => NULL,
                 'duty_start_int' => 32400,
                 'duty_start_sql' => '09:00',
                 'duty_end_int' => 63000,
                 'duty_end_sql' => '17:30',
                 'break_start_int' => 46800,
                 'break_start_sql' => '13:00',
                 'break_end_int' => 48600,
                 'break_end_sql' => '13:30',
                 'working_hours' => 8.0,
                 'break_duration' => 1800,
                 'duty_duration' => 30600,
                 'working_seconds' => 28800,
                 'weekday' => '5',
              )),
            ),
            1420239600 => 
            array (
              0 => 
              roster_item_empty::__set_state(array(
                 'date_sql' => '2015-01-03',
                 'date_unix' => 1420239600,
                 'employee_id' => NULL,
                 'branch_id' => 1,
                 'comment' => NULL,
                 'duty_start_int' => NULL,
                 'duty_start_sql' => NULL,
                 'duty_end_int' => NULL,
                 'duty_end_sql' => NULL,
                 'break_start_int' => NULL,
                 'break_start_sql' => NULL,
                 'break_end_int' => NULL,
                 'break_end_sql' => NULL,
                 'working_hours' => NULL,
                 'break_duration' => NULL,
                 'duty_duration' => NULL,
                 'working_seconds' => NULL,
                 'empty' => true,
                 'date_object' => 
                DateTime::__set_state(array(
                   'date' => '2015-01-03 00:00:00.000000',
                   'timezone_type' => 3,
                   'timezone' => 'Europe/Berlin',
                )),
                 'weekday' => NULL,
              )),
            ),
            1420326000 => 
            array (
              0 => 
              roster_item_empty::__set_state(array(
                 'date_sql' => '2015-01-04',
                 'date_unix' => 1420326000,
                 'employee_id' => NULL,
                 'branch_id' => 1,
                 'comment' => NULL,
                 'duty_start_int' => NULL,
                 'duty_start_sql' => NULL,
                 'duty_end_int' => NULL,
                 'duty_end_sql' => NULL,
                 'break_start_int' => NULL,
                 'break_start_sql' => NULL,
                 'break_end_int' => NULL,
                 'break_end_sql' => NULL,
                 'working_hours' => NULL,
                 'break_duration' => NULL,
                 'duty_duration' => NULL,
                 'working_seconds' => NULL,
                 'empty' => true,
                 'date_object' => 
                DateTime::__set_state(array(
                   'date' => '2015-01-04 00:00:00.000000',
                   'timezone_type' => 3,
                   'timezone' => 'Europe/Berlin',
                )),
                 'weekday' => NULL,
              )),
            ),
          ),
          1 => 
          array (
            1419807600 => 
            array (
              0 => 5,
            ),
          ),
          2 => '2014-12-29',
        ),
      ),
    ),
     'previous' => NULL,
     'errorInfo' => 
    array (
      0 => '23000',
      1 => 1062,
      2 => 'Duplicate entry \'0-5-1-1\' for key \'PRIMARY\'',
    ),
  )),
)

* Overtime
  + works
  * Read
  * Write
    * Insert new
    * Delete old
      + works

* Absence
  + works
  * Create an absence in absence-edit.php
    * Edit the absence
    * Delete the absence
      + works
  * View the absence in absence-read.php
    + works
  * Create an absence in collaborative-vacation-month.php
    * Mark it as denied, pending and approved (in that order!)
-

Fatal error: Uncaught TypeError: Argument 1 passed to absence::calculate_employee_absence_days() must be an instance of DateTime, string given, called in /var/www/html/apotheke/dienstplan-development/src/php/classes/class.collaborative_vacation.php on line 130 and defined in /var/www/html/apotheke/dienstplan-development/src/php/classes/class.absence.php:258 Stack trace: #0 /var/www/html/apotheke/dienstplan-development/src/php/classes/class.collaborative_vacation.php(130): absence::calculate_employee_absence_days('2019-02-12', '2019-03-15', Object(employee)) #1 /var/www/html/apotheke/dienstplan-development/src/php/classes/class.collaborative_vacation.php(39): collaborative_vacation->write_user_input_to_database(Object(sessions)) #2 /var/www/html/apotheke/dienstplan-development/src/php/pages/collaborative-vacation-year.php(27): collaborative_vacation->handle_user_data_input(Object(sessions)) #3 {main} thrown in /var/www/html/apotheke/dienstplan-development/src/php/classes/class.absence.php on line 258
  * Create an absence in collaborative-vacation-year.php
    * Delete the absence
------------------------------------------------------------------------------------------------------------------------------------
* Administration
  * Have a look at attendance-list.php
  * Have a look at marginal-employment-hours-list.php
  * PEP
    * Upload a PEP file to upload-pep.php
    * Upload a wrong file
  * Alter the values in
    * human-resource-management.php
    * branch-management.php (incl. create and delete)
    * user-management.php

* Check the site on the following browsers:
  * Internet Explorer,
  * Firefox,
  * Safari,
  * Chrome,
  * iPhone

* Click all the links
* Validate all the pages
  * https://validator.w3.org/#validate_by_input
* http://webdevchecklist.com/
* https://www.sk89q.com/content/2010/04/phpsec_cheatsheet.pdf

