These things should be done before merging changes into master:

* Login									__passed__
* Change password with
  * lost_password.php							**FAILED**
    - No reaction to the input at all

* Roster input
  * Create a roster from scratch					__passed__
  * Edit the roster
    * Exchange an employee with another one.				__passed__
    * Drag and drop							__passed__
      *	drag the duty times						__passed__
      * drag the break times						__passed__
  * Provoke some errors:						**FAILED**
    - When nobody is scheduled at 8:00 (starting time) and everybody starts at 9:00, then there is no error thrown. But there must be an error!
    * Not enough employees at starting time				__passed__
    * No pharmacist at starting time					__passed__
    * No goods reciept employee at starting time			__passed__
    * One employee not scheduled					__passed__
    * One employee scheduled although absent				__passed__
  * Add an entry at the end of the roster				__passed__
  * Look at one sunday/empty roster					__passed__
  * Saturday rotation							__passed__
  * Write a mail using the contact form					**FAILED**
    - The from header is malformed. Some of the linebreaks do not work. Probably they are in '' instead of ""
    - Also the mail could be much more beautiful.
  * Disapprove roster							__PASSED__
  * Approve roster							__PASSED__
*Roster output
  * View roster in read mode						__PASSED__
    * Navigation with keyboard						__PASSED__
    * View the print version						__PASSED__
    * use the datepicker in/for old browsers				**FAILED**
      - RangeError: invalid language tag: de_DE
      - RangeError: invalid language tag: en_GB
  * View the roster in read mode weekly table.				**FAILED**
    - the layout is broken. "Hours per week" are inside the table. 
    * Task rotation							**FAILED**
      - It is always PTA_6 that is no rotation.
    * Keyboard navigation						__passed__
  * View the roster in read mode weekly images.
  * View the roster in employee table					__passed__
    * download ICS file							__passed__
    * validate ICS file (https://icalendar.org/validator.html)		__passed__

* Principle roster
  * Principle roster day						__passed__
    * Change roster by drag and drop					__passed__
  * Principle roster employee
    * Change roster							**FAILED**
      - The changes were not saved into the database
* Overtime								**FAILED**
  * Read								__passed__
  * Write								**FAILED**
    - The balance is out of control. It rises with every view!
    * Insert new							__passed__
    * Delete old							__passed__

* Absence
  * Create an absence in absence-edit.php				__passed__
    * Edit the absence							__passed__
    * Delete the absence						**FAILED**
      - TypeError: pdr_translations[locale] is undefined[Weitere Informationen]  javascript.js:5:9
  * View the absence in absence-read.php				__passed__
  * Create an absence in collaborative-vacation-month.php 		__passed__
    * Mark it as denied, pending and approved (in that order!)		__passed__
  * Create an absence in collaborative-vacation-year.php		__passed__
    * Delete the absence						__passed__
* Administration
  * Have a look at attendance-list.php					__passed__
  * Have a look at marginal-employment-hours-list.php			__passed__
  * PEP
    * Upload a PEP file to upload-pep.php				**FAILED**				
      - Sorry, there was an error uploading your file
    * Upload a wrong file						__passed__
  * Alter the values in 
    * human-resource-management.php					__passed__
    * branch-management.php (incl. create and delete) 			__passed__
    * user-management.php						__passed__

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

