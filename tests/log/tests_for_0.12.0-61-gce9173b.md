These things should be done before merging changes into master:

* Login												PASSED
* Change password with										PASSED
  * lost_password.php										PASSED
* Create user											PASSED
  - There is no error message on failure
    - If a user with the given employee_id exists, than only a blank nothing is presented.

* Roster input											PASSED
  * Create a roster from scratch								PASSED
  * Edit the roster										PASSED
    * Exchange an employee with another one.							PASSED
    * Drag and drop										PASSED
      *	drag the duty times									PASSED
      * drag the break times									PASSED
  * Provoke some errors:
    * Not enough employees at starting time							PASSED
    * No pharmacist at starting time								PASSED
    * No goods reciept employee at starting time						PASSED
    * One employee not scheduled								PASSED
    * One employee scheduled although absent							PASSED
  * Add an entry at the end of the roster							PASSED
  * Look at one sunday/empty roster								PASSED
  * Saturday rotation										PASSED
  * Write a mail using the contact form
  * Disapprove roster									FAILED
  * Approve roster									FAILED
*Roster output
  * View roster in read mode									PASSED
    * Navigation with keyboard									PASSED
    * View the print version									PASSED
    * use the datepicker in/for old browsers							PASSED
  * View the roster in read mode weekly table.							PASSED
    * Task rotation										PASSED
    * Keyboard navigation									PASSED
  * View the roster in read mode weekly images.							PASSED
  * View the roster in employee table								PASSED
    * download ICS file										PASSED
    * validate ICS file (https://icalendar.org/validator.html)					PASSED

* Principle roster										PASSED
  * Principle roster day									PASSED
    * Change roster by drag and drop								PASSED
  * Principle roster employee								FAILED
    * Change roster										PASSED
    - It is not possible to enter a new day. It is only possible to change existing data.
* Overtime											PASSED
  * Read											PASSED
  * Write											PASSED
    * Insert new										PASSED
    * Delete old										PASSED

* Absence
  * Create an absence in absence-edit.php							PASSED
    * Edit the absence										PASSED
    * Delete the absence									PASSED
  * View the absence in absence-read.php							PASSED
  * Create an absence in collaborative-vacation-month.php					PASSED
    * Mark it as denied, pending and approved (in that order!)					PASSED
  * Create an absence in collaborative-vacation-year.php					PASSED
    * Delete the absence									PASSED
* Administration
  * Have a look at attendance-list.php								PASSED
  * Have a look at marginal-employment-hours-list.php						PASSED
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

