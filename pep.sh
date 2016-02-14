#Dieses Script soll die Daten aus dem PEP-Modul von Asys in die Datenbank 'Apotheke' einfügen.

date | tee pep2.log #Das PHP-Script leitet den Output in die Datei "tmp/pep.log" um. Dort können wir dann sehen, welcher Eintrag zu welchem Datum gehört.

#Das Passwort und die sonstigen Datanbank-Daten werden von php an dieses Script übergeben.
database_user=""
database_password=""
database_name=""
database_user="$1"
database_password="$2"
database_name="$3"
if [ "$database_user" != "" ] && [ "$database_password" != "" ] && [ "$database_name" != "" ]
then
	echo "Alle Daten vorhanden." | tee pep2.log
else
	echo "Es fehlen Zugangsdaten zur Datenbank." | tee pep2.log
	exit 1
fi


#Neueste Input Datei vom PEP-Modul:
pepdatei="tmp/pep.txt"
for asydatei in `ls -t upload/I*.asy`
do
	#Das Datum muss gedreht werden von 31.12.2015 auf 2015-12-31. Anschließend werden nur die Spalten Datum, Zeit, Anzahl und Mandant genutzt. Umsatzzahlen gehen uns nichts an.
	sed -e 's/\([0-9]\{2\}\)\.\([0-9]\{2\}\)\.\([0-9]\{4\}\)/\3-\2-\1/' $asydatei | cut -d\; -f 1,2,4,6 > $pepdatei
	#Jetzt können wir die Daten in die Datenbank eintragen. Dafür brauchen wir aber ein Passwort zur Datenbank. debug DEBUG
	mysqlimport \
		--ignore-lines=0 \
		--fields-terminated-by=\; \
		--columns='Datum,Zeit,Anzahl,Mandant' \
		--local -u "$database_user" -p"$database_password" "$database_name" \
		$pepdatei \
	&& rm $pepdatei && rm $asydatei 
done
