These things should be done before merging changes into master:

* Login								__PASSED__	
* Change password with						__PASSED__
  * lost_password.php						__PASSED__	

* Roster input							__PASSED__
  * Create a roster from scratch				__PASSED__	
  * Edit the roster						__PASSED__
    * Exchange an employee with another one.			__PASSED__
    * Drag and drop						__PASSED__
      *	drag the duty times					__PASSED__
      * drag the break times					__PASSED__
  * Provoke some errors:					__PASSED__
    * Not enough employees at starting time			__PASSED__	
    * No pharmacist at starting time				__PASSED__
    * No goods reciept employee at starting time		__PASSED__	
    * One employee not scheduled				__PASSED__
    * One employee scheduled although absent			__PASSED__
  * Add an entry at the end of the roster			__PASSED__
  * Look at one sunday/empty roster				__PASSED__
  * Saturday rotation						__PASSED__
  * Write a mail using the contact form				__PASSED__
  * Disapprove roster						__PASSED__
  * Approve roster						__PASSED__
*Roster output
  * View roster in read mode					__PASSED__
    * Navigation with keyboard					__PASSED__
    * View the print version					__PASSED__
      - The layout can still be optimized.
      - The histogram plot floats into the table.
    * use the datepicker in/for old browsers				__PASSED__
  * View the roster in read mode weekly table.				__PASSED__
    * Task rotation							__PASSED__
    * Keyboard navigation						__PASSED__
  * View the roster in read mode weekly images.				__PASSED__
  * View the roster in employee table					__PASSED__
    * download ICS file							__PASSED__
    * validate ICS file (https://icalendar.org/validator.html)		__PASSED__

* Principle roster							__PASSED__
  * Principle roster day						__PASSED__	
    * Change roster by drag and drop					__PASSED__
  * Principle roster employee						__PASSED__
    * Change roster							__PASSED__
    - Change the style of the day-bar
    - Create a button to add another roster row.
    - Inserting data into unused cells does not work.
* Overtime								
  * Read								__PASSED__
  * Write								__PASSED__
    * Insert new							__PASSED__
    * Delete old							__PASSED__

* Absence
  * Create an absence in absence-edit.php				__PASSED__
    - The javascript to calculate the number of days does not seem to work.
    * Edit the absence							__PASSED__
    * Delete the absence						__PASSED__
  * View the absence in absence-read.php				__PASSED__
  * Create an absence in collaborative-vacation-month.php 		__PASSED__
    * Mark it as denied, pending and approved (in that order!)		__PASSED__
  * Create an absence in collaborative-vacation-year.php		__PASSED__
    * Delete the absence						__PASSED__
* Administration
  * Have a look at attendance-list.php					__PASSED__
  * Have a look at saturday-list.php					__PASSED__
  * Have a look at marginal-employment-hours-list.php			__PASSED__
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

