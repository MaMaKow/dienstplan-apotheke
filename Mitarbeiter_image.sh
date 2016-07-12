#Dieses Script liest die Daten aus einer CSV Datei mittels gnuplot und stellt sie grafisch dar.
#Ich gehe davon aus, dass die Variable $1 den Datums-Parameter enthält.
echo "Mitarbeiter_image bearbeitet $1."
pwd
/usr/local/bin/gnuplot Mitarbeiter.gnuplot && echo "gnuplot war erfolgreich"
convert -rotate 90 tmp/mitarbeiter.png images/mitarbeiter_$1.png && echo "Das Bild wurde gedreht."
#Clean up:
rm tmp/mitarbeiter.png
rm tmp/Mitarbeiter.csv
echo "Das Script ist am Ende."
