#system "pwd"
#cd "/var/www/html/apotheke/dienstplan"
set terminal png size 800, 1800 enhanced font 'Verdana,20'
set datafile separator ","
set style fill solid 0.5
set boxwidth 0.9 
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


set output "mitarbeiter.png"
plot 	"Mitarbeiter.csv" using 0:5 with boxes lc rgb "green" notitle,\
	"Mitarbeiter.csv" using 0:7 with boxes lc rgb "grey"  notitle,\
	"Mitarbeiter.csv" using 0:6 with boxes lc rgb "green" notitle,\
	"Mitarbeiter.csv" using 0:4 with boxes lc rgb "white" notitle,\
	"Mitarbeiter.csv" using 0:4:1 with labels left rotate notitle,\
	"Mitarbeiter.csv" using 0:5:8 with labels left rotate notitle
