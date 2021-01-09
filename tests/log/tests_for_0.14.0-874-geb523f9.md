These things should be done before merging changes into master:

* Login								passed
* Change password with						passed, but haveibeenpwnd has to be added!
  * lost_password.php						passed, but has to be changed for security reasons, translation seems to be defective					
* Create user							passed

* Roster input							passed
  * Create a roster from scratch 				passed
  * Edit the roster					
    * Exchange an employee with another one.			passed
    * Drag and drop						passed
      *	drag the duty times					passed
      * drag the break times					passed
  * Provoke some errors:					
    * Not enough employees at starting time			passed
    * No pharmacist at starting time				passed
    * No goods reciept employee at starting time		passed
    * One employee not scheduled				passed
    * One employee scheduled although absent			passed
  * Add an entry at the end of the roster			passed, but not focussed
  * Look at one sunday/empty roster				passed
  * Saturday rotation						passed
  * Write a mail using the contact form				works, but with error
								Error: Notice: Undefined variable: trace in /var/www/html/apotheke/dienstplan-test/src/php/classes/class.user_dialog.php on line 189

  * Disapprove roster						passed
  * Approve roster						passed
*Roster output
  * View roster in read mode					passed
    * Navigation with keyboard					passed
    * View the print version					passed
  * View the roster in read mode weekly table.			passed
    * Task rotation						passed
    * Keyboard navigation					passed
  * View the roster in read mode weekly images.			passed
  * View the roster in employee table				passed
    * download ICS file						passed
    * validate ICS file (https://icalendar.org/validator.html)	passed

* Principle roster						
  * Principle roster day
    * Change roster by drag and drop				ERROR
	
Notice: Undefined index: List_of_deletions in /var/www/html/apotheke/dienstplan-test/src/php/pages/principle-roster-day.php on line 57

Fatal error: Uncaught TypeError: Argument 1 passed to principle_roster::invalidate_removed_entries_in_database() must be of the type array, null given, called in /var/www/html/apotheke/dienstplan-test/src/php/pages/principle-roster-day.php on line 73 and defined in /var/www/html/apotheke/dienstplan-test/src/php/classes/class.principle_roster.php:344 Stack trace: #0 /var/www/html/apotheke/dienstplan-test/src/php/pages/principle-roster-day.php(73): principle_roster::invalidate_removed_entries_in_database(NULL, '2023-01-09') #1 {main} thrown in /var/www/html/apotheke/dienstplan-test/src/php/classes/class.principle_roster.php on line 344

  * Principle roster employee					
    * Change roster						passed
* Overtime							passed
  * Read							passed
  * Write							passed
    * Insert new						passed
    * Delete old						passed

* Absence
  * Create an absence in absence-edit.php			passed, but bad UI
				Sa 09.01.2021 ist kein Arbeitstag für MaMakow und wird nicht gezählt.
				Of cause saturday is not a working day. This should be silenced.
    * Edit the absence						passed, but should provoke an error when the end is before the start.
									also the javascript should count the days in the edit mode just like in the count mode, when the dates are changed.
    * Delete the absence					passed
  * View the absence in absence-read.php			passed
  * Create an absence in collaborative-vacation-month.php	failed
									
Fatal error: Uncaught TypeError: Argument 7 passed to absence::insert_absence() must be of the type string, null given, called in /var/www/html/apotheke/dienstplan-test/src/php/classes/class.collaborative_vacation.php on line 137 and defined in /var/www/html/apotheke/dienstplan-test/src/php/classes/class.absence.php:90 Stack trace: #0 /var/www/html/apotheke/dienstplan-test/src/php/classes/class.collaborative_vacation.php(137): absence::insert_absence(5, '2021-01-11', '2021-01-11', 1, 1, '', NULL) #1 /var/www/html/apotheke/dienstplan-test/src/php/classes/class.collaborative_vacation.php(39): collaborative_vacation->write_user_input_to_database(Object(sessions)) #2 /var/www/html/apotheke/dienstplan-test/src/php/pages/collaborative-vacation-month.php(27): collaborative_vacation->handle_user_data_input(Object(sessions)) #3 {main} thrown in /var/www/html/apotheke/dienstplan-test/src/php/classes/class.absence.php on line 90

    * Mark it as denied, pending and approved (in that order!)
  * Create an absence in collaborative-vacation-year.php	failed
										Fatal error: Uncaught TypeError: Argument 1 passed to workforce::get_employee_last_name() must be of the type integer, null given, called in /var/www/html/apotheke/dienstplan-test/src/php/classes/class.collaborative_vacation.php on line 323 and defined in /var/www/html/apotheke/dienstplan-test/src/php/classes/class.workforce.php:109 Stack trace: #0 /var/www/html/apotheke/dienstplan-test/src/php/classes/class.collaborative_vacation.php(323): workforce->get_employee_last_name(NULL) #1 /var/www/html/apotheke/dienstplan-test/src/php/classes/class.collaborative_vacation.php(296): collaborative_vacation->build_absence_month_paragraph_add_emergency_service(Object(DateTime), 'year') #2 /var/www/html/apotheke/dienstplan-test/src/php/classes/class.collaborative_vacation.php(272): collaborative_vacation->build_absence_month_paragraph_content(Object(DateTime), Array, false, 'year') #3 /var/www/html/apotheke/dienstplan-test/src/php/classes/class.collaborative_vacation.php(204): collaborative_vacation->build_absence_month_paragraph(Obj in /var/www/html/apotheke/dienstplan-test/src/php/classes/class.workforce.php on line 109
    * Delete the absence
* Administration
  * Have a look at attendance-list.php				passed, but could have a title mouseover with the full names
  * Have a look at marginal-employment-hours-list.php		passed, but bad UI, the table should be more beautiful, minimalistic
  * PEP
    * Upload a PEP file to upload-pep.php			passed, but UI is really bad
    * Upload a wrong file					passed
  * Alter the values in
    * human-resource-management.php				passed
    * branch-management.php (incl. create and delete)		passed
    * user-management.php					passed

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

