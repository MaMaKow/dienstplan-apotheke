These things should be done before merging changes into master:

* Login
works
* Change password with
  + works
  * lost_password.php
* Create user
  + works

* Roster input
  * Create a roster from scratch
    + works
  * Edit the roster
    * Exchange an employee with another one.
      + works
    * Drag and drop
      + works
      *	drag the duty times
      * drag the break times
  * Provoke some errors:
    + works
    + Not enough employees at starting time
    + No pharmacist at starting time
    + No goods reciept employee at starting time
    + One employee not scheduled
    + One employee scheduled although absent
  * Add an entry at the end of the roster
    +- works, but it does not react to drag and drop!
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
*Roster output
  + works
  * View roster in read mode
    + Navigation with keyboard
    + View the print version
    * use the datepicker in/for old browsers
      - internet explorer should die
  * View the roster in read mode weekly table.
    + works
    + Task rotation
    + Keyboard navigation
  * View the roster in read mode weekly images.
    + works, prints
  * View the roster in employee table
    + works
    + download ICS file
    + validate ICS file (https://icalendar.org/validator.html)

* Principle roster
  * Principle roster day
    - I am not sure, which data it uses for the comparison. It certainly is not the one from principle-roster-day.php
    - fragment.principle-roster-day-history.php MUST use COOKIE, SESSION or GET!
      There are weird effects when moving between POST versions: "Dokument erloschen" after using the back option.

    * Change roster by drag and drop

  * Principle roster employee
    * Change roster
      - fragment.prompt_before_safe.php does not show changes from principle-roster-employee.php
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

