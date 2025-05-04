These things should be done before merging changes into master:

* Login									__PASSED__
* Change password with							--FAILED--
  * lost_password.php							--FAILED--
    - The mail could be a bit more personal. Use Dear $username instead of Dear User.
    - No mail seems to be sent. No message about any mail is visible. Perhaps just remove that form on success and only display success?
* Roster input								--FAILED--
  * Create a roster from scratch					__PASSED__
  * Edit the roster							__PASSED__
    * Exchange an employee with another one.				__PASSED__
    * Drag and drop							__PASSED__
      *	drag the duty times						__PASSED__
      * drag the break times						__PASSED__
  * Provoke some errors:						__PASSED__
    * Not enough employees at starting time				__PASSED__
    * No pharmacist at starting time					__PASSED__
    * No goods reciept employee at starting time			__PASSED__
    * One employee not scheduled					__PASSED__
    * One employee scheduled although absent				__PASSED__
  * Add an entry at the end of the roster				__PASSED__
  * Look at one sunday/empty roster					__PASSED__
  * Saturday rotation							--FAILED--
    - seems to be buggy. There needs to be one year backup in the database. The dates have to be saved on viewing. Or they can be rerendered each time. There has to be a warning as long as the data is not saved.
  * Write a mail using the contact form					__PASSED__
  * Disapprove roster							__PASSED__
  * Approve roster							__PASSED__
*Roster output								__PASSED__
  * View roster in read mode						__PASSED__
    * Navigation with keyboard						__PASSED__
    * View the print version						--FAILED--
      - use css: box-shadow: inset 0 0 0 1000px gold; makes the color appear in the print.
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

* Overtime								__PASSED__
  * Read								__PASSED__
  * Write								__PASSED__
    * Insert new							__PASSED__
    * Delete old							__PASSED__

* Absence								__PASSED__
  * Create an absence in absence-edit.php				__PASSED__
    * Edit the absence							__PASSED__
    * Delete the absence						--FAILED--
      - There was no question if the entry should be removed. This was because the l8n coud not be loaded.
  * View the absence in absence-read.php				__PASSED__
  * Create an absence in collaborative-vacation-month.php 		__PASSED__
    * Mark it as denied, pending and approved (in that order!)		__PASSED__
  * Create an absence in collaborative-vacation-year.php		__PASSED__
    * Delete the absence						__PASSED__

* Administration							--FAILED--
  * Have a look at attendance-list.php					__PASSED__
  * Have a look at marginal-employment-hours-list.php			__PASSED__
  * PEP									--FAILED--
    * Upload a PEP file to upload-pep.php				--FAILED--
      - Sorry, there was an error uploading your file
    * Upload a wrong file						__PASSED__
  * Alter the values in 						__PASSED__
    * human-resource-management.php					__PASSED__
    * branch-management.php (incl. create and delete) 			__PASSED__
    * user-management.php						__PASSED__

* Check the site on the following browsers:
  * Internet Explorer,
  * Firefox,
  * Safari,
  * Chrome,
  * iPhone

* Click all the links
* Validate all the pages						--FAILED--
  * https://validator.w3.org/#validate_by_input
    * roster-week-table.php
      - Error: Bad value en_GB for attribute lang on element head: The language subtag en_gb is not a valid language subtag.
      - Error: Stray end tag tbody. </tbody>↩</tbody>
      - Error: Duplicate ID svgimg.
      - Error: Duplicate ID work_box_0.
      - Error: Duplicate ID break_box_0.
      - Error: Stray end tag div. </div><!--class='main-area no-print'-->
    * roster-day-edit.php
      - Error: Bad value 0 for attribute colspan on element th: Zero is not a positive integer. From line 330, column 43; to line 330, column 56 title_tr><th colspan=0>Marien
      - Error: Unclosed element form. From line 615, column 51; to line 615, column 56 zeptur</p><form><input
* http://webdevchecklist.com/
* https://www.sk89q.com/content/2010/04/phpsec_cheatsheet.pdf

