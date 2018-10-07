��    O      �  k         �     �  X   �  R        m     �     �     �  ?  �  q  �  J  f  �   �     b  
   h     s     �     �     �     �  
   �     �  d   �  
  T     _  	   h     r     �     �     �  N   �  �     �   �  �   j  �   A  .     3  O  �   �     	  8    &  U     |     �     �  ~   �     '     4     T      n     �     �     �     �     �  %     *   :     e     �     �  "   �  %   �            &   9     `     |     �     �     �     �     �          #     8  L   Q  :   �  0   �  /   
  &   :  E   a  �  �     0   b   <   ~   �       !  
   ?!     J!  $   e!  �  �!  �  #  �  �%  �   u'     C(     I(     U(     h(      �(  )   �(     �(  
   �(     �(  �   �(  :  x)     �*     �*     �*     �*     �*     +  T   5+  �   �+  �   ,  �   �,    �-  ]  �.  t  ?0  �   �1     o2  �  �2  M  h4     �5     �5     �5  �   �5     �6  "   �6     �6  "   �6     7     )7     I7      c7      �7  !   �7  4   �7      �7      8  #   >8  "   b8  !   �8     �8     �8     �8     �8     9     09     K9     b9     z9     �9     �9     �9     �9  Z   �9  B   W:  ;   �:  >   �:  '   ;  P   =;         %      *   I              <      "                 9          '   N   G   7      :       8   4   ?      O         3   J               6            -   )   0   A         F   @                    (         L               ,   E   
              K   1         2       B   $       #   .      D          =   >                  ;   +   &       !           C   /   	           H   5   M              Absence By default, the PDR web interface opens a menu containing 5 tiles.  You can navigate to: Choose a user name, enter your employee id and your email. Pick a secure password. Create new user account Edit Employee view edit Employee view readonly If an employee, that is primarily scheduled in the chosen branch, works in an other branch, then this entry is shown in the table at the bottom.  An employee may have more than one entrie per day. This allows divided working time to be stored.  If employees are absent, these absences are displayed in the table footer. In the \emph{Employee view readonly} there is a select element to choose the employee to view. There is a button to switch to the edit view.  And there is a table containing the absence data. The columns are start and end of the absence, reason of absence and number of days.  There is a distinct list of possible reasons ( vacation, remaining holiday, sickness, sickness of child, unpaid leave of absence, paid leave of absence, parental leave and maternity leave).  The number of days of absence is calculated for a 5 day week. Absences on saturdays and sundays are registered but not counted. The same applys for holidays. In the daily roster view there is a table, a bar plot and a histogram reflecting the roster.  The roster table lists all the employees scheduled in the chosen branch on the one chosen day. For every entry there is the employee id and last name, the working hours, the start and end of duty and the time of the lunch break, if any. In the top there is a navigation bar containing hyperlinks to nearly all the pages of PDR. Hover the mouse over an entry to open the submenus (Figure \ref{img_navigation_bar}). Login Login page Lost password Lost password page Lost password recovery Lost password recovery page Monthly table Navigation Navigation bar New users can only be created for existing employees. New employees are created by an administrator. Only one break can be inserted per entry. If more breaks have to be assigned, then it is possible to enter multiple entries for the same employee.  \subsection{Roster employee view} \subsection{Overtime} \subsection{Absence} There are four views to the absence data. Overtime Read only Register new user page Roster daily view Roster employee view Roster week table view Roster week table view, excerpt without task rotation and weekly working hours The account will be inactive until an administrator activates it. The main administrator is informed via email regarding the registration. The date can be chosen by direct input. It can also be shifted by one week backwards or forwards by pressing \keys{\ctrl + \shift + $\rightarrow$} or \keys{\ctrl + \shift + $\leftarrow$} respectively. The edit page looks quite similar to the read only view.  The roster is examined for errors. If any issues occur, then errors, warnings or information will be shown in the top right area.  The examination includes: The histogram plot shows a red area and a green line. The red area shows the expected amount of work (measured in packages per 15 minutes), while the green line represents the amount of working employees to any given time. The login page shows the name of the application. You are prompted to enter your username and password.  If you do not have an account yet, you can \menu{Create a new user account}.  If you have an account, but forgot about your password, or want to change it, you can click on \menu{Forgot password?}. The lost password page shows the name of the application. You are prompted to enter either your username, id or your email-address at your option.  After you submit the form, an email is sent to your stored email address.  In that email you will find a link, which will lead you to the password change page. The lost password recovery page shows the name of the application and your user name. You are prompted to enter a new password twice. The navigation bar The roster bar plot shows the flow of employees coming and going. Each bar represents one entry. It reaches from the start of duty to its end. A white rectangle on the bar shows the time of the lunch break. The color of the bars is dependent on the profession of the employee. Pharmacists and Pharmazieingenieure \footnote{specific eastern german profession, see \url{https://de.wikipedia.org/wiki/Pharmazieingenieur}} are colored in dark green, while Pharmacy technicians are colored in light green. Other employees (non-pharmaceutical personnel) are colored in grey. The roster week table view shows the roster of a chosen week and branch (Figure~\ref{img_roster_week_table_view}). If employees of the branch are working in an other branch, then those are shown below.  The table foot contains the information about absent employees and their reason of absence. The web interface User manual Year overview You can connect to your PDR instance using any web browser. Just navigate to your server and enter your username and password. \columnbreak \menu{.. > Absence annual plan} \menu{.. > Absence input} \menu{.. > Absence monthly plan} \menu{.. > Attendance list} \menu{.. > Branch managament} \menu{.. > Configuration} \menu{.. > Daily input} \menu{.. > Daily output} \menu{.. > Human resource managament} \menu{.. > Marginal employment hours list} \menu{.. > Overtime input} \menu{.. > Overtime output} \menu{.. > Overtime overview} \menu{.. > Principle roster daily} \menu{.. > Principle roster employee} \menu{.. > Roster employee} \menu{.. > Saturday list} \menu{.. > Upload deployment planning} \menu{.. > User managament} \menu{.. > Weekly images} \menu{.. > Weekly table} \menu{.. > phpMyAdmin} \menu{Absence > ..} \menu{Administration > ..} \menu{Daily view > .. } \menu{Employee > .. } \menu{Overtime > ..} \menu{Weekly view > .. } attendance of at least one person able to carry out goods receipt (Warning). attendence of at least one pharmacist at any time (Error). non-scheduling of non-absent employees (Warning) overlap of shifts for the same employee (Error) scheduling of absent employees (Error) sufficient employee count (Warning, hardcoded at least two employees) Project-Id-Version: dienstplan documentation
POT-Creation-Date: 2018-10-06 21:14+0200
PO-Revision-Date: 2018-10-07 13:15+0100
Last-Translator: Dr. Martin Mandelkow <poedit-debian@martin-mandelkow.de>
Language-Team: 
Language: de_DE
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
Plural-Forms: nplurals=2; plural=(n != 1);
X-Generator: Poedit 1.6.10
 Abwesenheit Standardmäßig öffnet die PDR-Weboberfläche ein Menü mit 5 Kacheln. Sie können navigieren zu: Wählen Sie einen Benutzernamen, geben Sie Ihre Mitarbeiter-ID und Ihre E-Mail-Adresse ein. Wählen Sie ein sicheres Passwort. Neuen Benutzer-Account erstellen Bearbeiten Mitarbeiteransicht Eingabe Mitarbeiteransicht schreibgeschützt Wenn ein Mitarbeiter, der in erster Linie in der ausgewählten Filiale eingeplant ist, in einer anderen Filiale arbeitet, wird dieser Eintrag in der Tabelle unten angezeigt. Ein Mitarbeiter kann mehr als einen Eintrag pro Tag haben. Dadurch kann eine geteilte Arbeitszeit gespeichert werden. Wenn Mitarbeiter abwesend sind, werden diese Abwesenheiten in der Tabellenfußzeile angezeigt. In der \emph{schreibgeschützten Mitarbeitersicht} gibt es ein Select-Element, um den anzuzeigenden Mitarbeiter auszuwählen. Es gibt eine Schaltfläche, um zur Bearbeitungsansicht zu wechseln. Und es gibt eine Tabelle mit den Abwesenheitsdaten. Die Spalten sind Beginn und Ende der Abwesenheit, Abwesenheitsgrund und Anzahl der Tage. Es gibt eine eindeutige Liste möglicher Gründe (Urlaub, Resturlaub, Krankheit, Krankheit des Kindes, unbezahlte Freistellung, bezahlte Freistellung, Elternzeit und Mutterschutz). Die Anzahl der Abwesenheitstage wird für eine 5-Tage-Woche berechnet. Abwesenheiten an Samstagen und Sonntagen werden registriert, aber nicht gezählt. Das Gleiche gilt für Feiertage. In der täglichen Dienstplanansicht gibt es eine Tabelle, ein Balkendiagramm und ein Histogramm, welche den Dienstplan widerspiegeln. Die Dienstplantabelle listet alle Mitarbeiter auf, die in dem ausgewählten Zweig an dem ausgewählten Tag geplant sind. Jeder Eintrag enthält die ID und den Nachnamen des Mitarbeiters, die Arbeitsstunden, den Beginn und das Ende des Dienstes und die Zeit der Mittagspause, falls vorhanden. Im oberen Bereich befindet sich eine Navigationsleiste mit Hyperlinks zu fast allen PDR-Seiten. Bewegen Sie die Maus über einen Eintrag, um die Untermenüs zu öffnen (Abbildung \ref{img_navigation_bar}). Login Login Seite Passwort vergessen Passwort vergessen Seite Wiederherstellung des Passwortes Wiederherstellungs-Seite für Passwörter Monatstabelle Navigation Navigationsleiste Neue Benutzer können nur für vorhandene Mitarbeiter erstellt werden. Neue Mitarbeiter werden von einem Administrator erstellt. Pro Eintrag kann nur eine Pause eingefügt werden. Wenn mehr Pausen zugewiesen werden müssen, können mehrere Einträge für denselben Mitarbeiter eingegeben werden. \subsection{Mitarbeiterliste des Dienstplans} \subsection{Überstunden} \subsection{Abwesenheit} Es gibt vier Ansichten für die Abwesenheitsdaten. Überstunden Schreibgeschützt Registrierungsseite Dienstplan Tagesansicht Dienstplan Mitarbeiteransicht Dienstplan Wochenansicht Dienstplan Wochenansicht, Auszug ohne Aufgabenrotation und wöchentliche Arbeitszeit Das Konto ist inaktiv, bis ein Administrator es aktiviert. Der Hauptadministrator wird per E-Mail über die Registrierung informiert. Das Datum kann durch direkte Eingabe ausgewählt werden. Es kann auch um eine Woche vor oder zurück verschoben werden, indem man \keys{\ctrl + \shift + \$rightarrow$} oder \keys {\ctrl + \shift + \$leftarrow$} drückt. Die Bearbeitungsseite ähnelt der schreibgeschützten Ansicht. Der Dienstplan wird auf Fehler überprüft. Wenn Probleme auftreten, werden Fehler, Warnungen oder Informationen im oberen rechten Bereich angezeigt. Die Prüfung beinhaltet: Das Histogramm zeigt einen roten Bereich und eine grüne Linie. Der rote Bereich zeigt den erwarteten Arbeitsaufwand (gemessen in Packungen pro 15 Minuten), während die grüne Linie die Anzahl der arbeitenden Mitarbeiter zu einem bestimmten Zeitpunkt darstellt. Die Anmeldeseite zeigt den Namen der Anwendung an. Sie werden aufgefordert, Ihren Benutzernamen und Ihr Passwort einzugeben. Wenn Sie noch keinen Account haben, können Sie \menu{Create a new account} erstellen. Wenn Sie ein Konto haben, aber Ihr Passwort vergessen haben oder es ändern möchten, können Sie auf \menu{Passwort vergessen?} Klicken. Auf der Seite "Passwort vergessen" wird der Name der Anwendung angezeigt. Sie werden aufgefordert, entweder Ihren Benutzernamen, Ihre ID oder Ihre E-Mail-Adresse einzugeben. Nachdem Sie das Formular abgeschickt haben, wird eine E-Mail an Ihre gespeicherte E-Mail-Adresse gesendet. In dieser E-Mail finden Sie einen Link, der Sie zur Seite zum Ändern des Passworts führt. Auf der Seite zur Wiederherstellung des verlorenen Passworts werden der Name der Anwendung und Ihr Benutzername angezeigt. Sie werden aufgefordert, ein neues Passwort zweimal einzugeben. Die Navigationsleiste Das Dienstplan-Balken-Diagramm zeigt das Kommen und Gehen von Mitarbeitern. Jeder Balken repräsentiert einen Eintrag. Er reicht vom Beginn des Dienstes bis zu seinem Ende. Ein weißes Rechteck auf dem Balken zeigt die Zeit der Mittagspause an. Die Farbe der Balken hängt vom Beruf des Mitarbeiters ab. Apotheker und Pharmazieingenieure sind in dunkelgrün gefärbt, während PTA in hellgrün gefärbt sind. Andere Mitarbeiter (nichtpharmazeutisches Personal) sind grau hinterlegt. Die Dienstplan-Wochenansicht zeigt die Liste einer ausgewählten Woche und Zweigstelle (Abbildung~\ref{img_roster_week_table_view}). Wenn Mitarbeiter der Zweigstelle in einer anderen Zweigstelle arbeiten, werden diese unten angezeigt. Der Tabellenfuß enthält Informationen über abwesende Mitarbeiter und deren Abwesenheitsgründe. Das Web-Interface Benutzerhandbuch Jahrestabelle Sie können sich mit einem beliebigen Webbrowser mit Ihrer PDR-Instanz verbinden. Navigieren Sie einfach zu Ihrem Server und geben Sie Ihren Benutzernamen und Ihr Passwort ein. \columnbreak \menu{.. > Abwesenheit Jahresplan} \menu{.. > Abwesenheit Eingabe} \menu{.. > Abwesenheit Monatsplan} \menu{.. > Anwesenheitsliste} \menu{.. > Mandantenverwaltung} \menu{.. > Konfiguration} \menu{.. > Tagesansicht Eingabe} \menu{.. > Tagesansicht Ausgabe} \menu{.. > Mitarbeiterverwaltung} \menu{.. > Stundenzettel geringfügig Beschäftigte} \menu{.. > Überstunden Eingabe} \menu{.. > Überstunden Ausgabe} \menu{.. > Überstunden Übersicht} \menu{.. > Grundplan Tagesansicht} \menu{.. > Grundplan Mitarbeiter} \menu{.. > Mitarbeiteransicht} \menu{.. > Samstagsliste} \menu{.. > PEP-Upload} \menu{.. > Benutzerverwaltung} \menu{.. > Wochen-Bilder} \menu{.. > Wochen-Tabelle} \menu{.. > phpMyAdmin} \menu{Abwesenheit > ..} \menu{Administration > ..} \menu{Tagesansicht > .. } \menu{Mitarbeiter > .. } \menu{Überstunden > ..} \menu{Wochenansicht > .. } Anwesenheit von mindestens einer Person, die den Wareneingang durchführen kann (Warnung). Anwesenheit von mindestens einem Apotheker zu jeder Zeit (Fehler). Nichteinplanung von nicht abwesenden Mitarbeitern (Warnung) Überlappung von Schichten für denselben Mitarbeiter (Fehler) Einsatz abwesender Mitarbeiter (Fehler) ausreichende Mitarbeiterzahl (Warnung, fest codiert mindestens zwei Mitarbeiter) 