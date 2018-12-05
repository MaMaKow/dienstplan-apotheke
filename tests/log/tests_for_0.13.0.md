These things should be done before merging changes into master:

* Login
* Change password with
  * lost_password.php
* Create user

* Roster input
  * Create a roster from scratch
  * Edit the roster
    * Exchange an employee with another one.
    * Drag and drop
      *	drag the duty times
      * drag the break times
  * Provoke some errors:
    * Not enough employees at starting time
    * No pharmacist at starting time
    * No goods reciept employee at starting time
    * One employee not scheduled
    * One employee scheduled although absent
  * Add an entry at the end of the roster
  * Look at one sunday/empty roster
  * Saturday rotation
  * Write a mail using the contact form
  * Disapprove roster
  * Approve roster
*Roster output
  * View roster in read mode
    * Navigation with keyboard
    * View the print version
    * use the datepicker in/for old browsers
  * View the roster in read mode weekly table.
    * Task rotation
    * Keyboard navigation
  * View the roster in read mode weekly images.
  * View the roster in employee table
    * download ICS file
    * validate ICS file (https://icalendar.org/validator.html)

* Principle roster
  * Principle roster day
    * Change roster by drag and drop
  * Principle roster employee
    * Change roster
* Overtime
  * Read
  * Write
    * Insert new
    * Delete old

* Absence
  * Create an absence in absence-edit.php
    * Edit the absence
    * Delete the absence
  * View the absence in absence-read.php
  * Create an absence in collaborative-vacation-month.php
    * Mark it as denied, pending and approved (in that order!)
  * Create an absence in collaborative-vacation-year.php
    * Delete the absence
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

