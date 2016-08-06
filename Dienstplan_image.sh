#Dieses Script liest die Daten aus einer CSV Datei mittels gnuplot und stellt sie grafisch dar.
#Ich gehe davon aus, dass die Variable $1 den Datums-Parameter enthält.
echo "Dienstplan_image bearbeitet $1."
/usr/local/bin/gnuplot Dienstplan.gnuplot && echo "gnuplot war erfolgreich"
convert -rotate 90 tmp/dienstplan.png images/dienstplan_$1.png && echo "Das Bild wurde gedreht."
#Clean up:
rm tmp/dienstplan.png
rm tmp/Dienstplan.csv
echo "Das Script ist am Ende."
