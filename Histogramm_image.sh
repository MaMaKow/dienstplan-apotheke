#Dieses Script liest die Daten aus einer CSV Datei mittels gnuplot und stellt sie grafisch dar.
#Ich gehe davon aus, dass die Variable $1 den Datums-Parameter enthält.
echo "Wir bearbeiten $1."
/usr/local/bin/gnuplot Histogramm.gnuplot && echo "gnuplot war erfolgreich" 
#convert -rotate 90 histogramm.png histogramm_$1.png && echo "Das Bild wurde gedreht."
mv tmp/histogramm.png images/histogramm_$1.png && echo "Das Bild wurde umbenannt."
echo "Das Script ist am Ende."
