#Dieses Script liest die Daten aus einer CSV Datei mittels gnuplot und stellt sie grafisch dar.
#Ich gehe davon aus, dass die Variable $1 den Datums-Parameter enthält.
#echo "Histogramm_image bearbeitet $1. Ganz besonders gut."
PATH=/usr/local/bin:/bin:/usr/bin:/usr/local/sbin:/usr/sbin:/sbin:~/bin
gnuplot Histogramm.gnuplot #&& echo "gnuplot war erfolgreich"
#convert -rotate 90 histogramm.png histogramm_$1.png && echo "Das Bild wurde gedreht."
mv tmp/histogramm.png images/histogramm_$1.png #&& echo "Das Bild wurde umbenannt."

#Clean up:
rm tmp/Histogramm.csv
#rm tmp/Erwartung.csv
#echo "Das Script ist am Ende."
