#system "pwd"
#cd "/var/www/html/apotheke/dienstplan"
set terminal png size 800, 1800 enhanced font 'Verdana,20'
set datafile separator ","
set style fill solid border 0.5
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
#set xrange [-0.0:]
set yrange ["7:00":"21:00"]; set y2range ["7:00":"21:00"]


set output "tmp/mitarbeiter.png"
plot 	"tmp/Mitarbeiter.csv" using ($3):4:4:5:5 with candlesticks lc rgb "#BDE682" notitle whiskerbars,\
	"tmp/Mitarbeiter.csv" using ($3):4:1 with labels left rotate notitle,\
	"tmp/Mitarbeiter.csv" using ($3):5:8 with labels right rotate notitle,\
	"tmp/Mitarbeiter.csv" using ($3+0.5):0 with points notitle,\
	"tmp/Mitarbeiter.csv" using ($3-0.5):0 with points notitle
