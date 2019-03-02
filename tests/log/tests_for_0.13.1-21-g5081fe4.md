These things should be done before merging changes into master:

* Login								failed - there is a problem with empty referrers

* Change password with						passed	
  * lost_password.php						passed
* Create user							failed - the user is created. but there is no email to the admin

* Roster input							passed
  * Create a roster from scratch				passed
  * Edit the roster						passed
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
  * Add an entry at the end of the roster			passed
  * Look at one sunday/empty roster				passed
  * Saturday rotation						passed
  * Write a mail using the contact form				
  * Disapprove roster						failed - nothing happens
  * Approve roster						failed - nothing happens
*Roster output
  * View roster in read mode					passed
    * Navigation with keyboard					passed
    * View the print version					passed
    * use the datepicker in/for old browsers			not tested
  * View the roster in read mode weekly table.			passed
    * Task rotation						passed
    * Keyboard navigation					passed
  * View the roster in read mode weekly images.			passed
  * View the roster in employee table				failed - ics not working
    * download ICS file						failed - the file is empty
    * validate ICS file (https://icalendar.org/validator.html)	failed - no data in file to test

* Principle roster						passed
  * Principle roster day					passed
    * Change roster by drag and drop				passed
  * Principle roster employee					failed
    * Change roster						failed - it is not possible to add data on days, which have not been filles before, also it is not possible to add a second schedule for another branch
* Overtime							passed, but the javascript does not work correctly in fresh years. the balance is only correct AFTER submitting
  * Read							passed
  * Write							passed
    * Insert new						passed
    * Delete old						passed

* Absence
  * Create an absence in absence-edit.php			passed
    * Edit the absence						passed
    * Delete the absence					passed
  * View the absence in absence-read.php			passed
  * Create an absence in collaborative-vacation-month.php	passed
    * Mark it as denied, pending and approved (in that order!)	passed
  * Create an absence in collaborative-vacation-year.php	passed
    * Delete the absence					passed
* Administration						
  * Have a look at attendance-list.php				passed
  * Have a look at marginal-employment-hours-list.php		failed - there seems to be a problem with the input variables
  * PEP								not tested
    * Upload a PEP file to upload-pep.php			
    * Upload a wrong file
  * Alter the values in						
    * human-resource-management.php				passed - but missing a feature to delete (archive) an employee
    * branch-management.php (incl. create and delete)		passed
    * user-management.php					passed

* Check the site on the following browsers:			not tested
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

