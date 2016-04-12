set terminal png size 680, 350 crop enhanced font 'Verdana,12'
set datafile separator ","
#set style fill solid 0.5
#set boxwidth 0.9
#set grid
set grid xtics mxtics ytics mytics front lc rgb "black" lw 2
set style fill transparent solid .5 noborder


#set xtics rotate
#set ytics rotate; set y2tics rotate
#unset xtics
set ytics nomirror
set y2tics
unset ytics
unset y2tics

set xdata time; #set y2data time
set timefmt "%H:%M"
#set timefmt "%s"
#set format y "%H:%M"
set xrange ["7:00":"21:00"]; #set y2range ["7:00":"21:00"]
set yrange [1:]
set link y2 via y-1*6 inverse y/6-1  #Dies legt fest, wie viele Packungen ein Mitarbeiter pro 30 Minuten abarbeiten kann.

set output "tmp/histogramm.png"
plot 	"tmp/Histogramm.csv" 	using 1:2 with steps lc rgb "#BDE682" 		lw 5 notitle,\
	"tmp/Erwartung.csv"		using 1:2 with filledcurves x1 lc rgb "red" 	lw 6 notitle axes x1y2
