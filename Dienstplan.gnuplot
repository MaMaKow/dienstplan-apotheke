#pwd
#cd "/var/www/html/apotheke/dienstplan"
set terminal png size 400, 680 crop enhanced font 'Verdana,12'
set datafile separator ","
set style fill solid 1
set boxwidth 0.8
#set grid
set grid xtics mxtics ytics mytics front lc rgb "black" lw 2

set xtics rotate
set ytics rotate; set y2tics rotate
unset xtics
set y2tics

set ydata time; set y2data time
set timefmt "%H:%M"
#set timefmt "%s"
#set format y "%H:%M"
set yrange ["7:00":"21:00"]; set y2range ["7:00":"21:00"]

#The following line will allow to read different styles from datafile
set style increment user
set style line 1 lc rgb "#73AC22"
set style line 2 lc rgb "#BDE682" 
set style line 3 lc rgb "#B4B4B4" 

set output "tmp/dienstplan.png"
plot 	"tmp/Dienstplan.csv" using 0:5:10 with boxes notitle lc variable,\
	"tmp/Dienstplan.csv" using 0:7 with boxes lc rgb "#FEFEFF"  notitle,\
	"tmp/Dienstplan.csv" using 0:6:10 with boxes notitle lc variable,\
	"tmp/Dienstplan.csv" using 0:4 with boxes lc rgb "white" notitle,\
	"tmp/Dienstplan.csv" using 0:4:1 with labels left rotate notitle,\
	"tmp/Dienstplan.csv" using 0:5:8 with labels left rotate notitle
