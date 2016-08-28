#Dieses Script liest die Daten aus einer CSV Datei mittels gnuplot und stellt sie grafisch dar.
#Ich gehe davon aus, dass die Variable $1 den Datums-Parameter enthält.
#echo "Dienstplan_image bearbeitet $1."
#ls -la tmp/ 1>&2
PATH=/usr/local/bin:/bin:/usr/bin:/usr/local/sbin:/usr/sbin:/sbin:~/bin
#which gnuplot 2>&1
#whoami 2>&1
#echo $PATH 2>&1
gnuplot Dienstplan.gnuplot #&& echo "gnuplot war erfolgreich"
convert -rotate 90 tmp/dienstplan.png images/dienstplan_$1.png #&& echo "Das Bild wurde gedreht."
#Clean up:
rm tmp/dienstplan.png
rm tmp/Dienstplan.csv
#echo "Das Script ist am Ende."
