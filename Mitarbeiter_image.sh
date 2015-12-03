#Dieses Script liest die Daten aus einer CSV Datei mittels gnuplot und stellt sie grafisch dar.
#Ich gehe davon aus, dass die Variable $1 den Datums-Parameter enthält.
echo "Wir bearbeiten $1."
pwd
/usr/local/bin/gnuplot Mitarbeiter.gnuplot && echo "gnuplot war erfolgreich" 
convert -rotate 90 mitarbeiter.png mitarbeiter_$1.png && echo "Das Bild wurde gedreht."
echo "Das Script ist am Ende."
