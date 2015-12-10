set terminal png size 1800, 800 enhanced font 'Verdana,20'
set datafile separator ","
#set style fill solid 0.5
#set boxwidth 0.9 
#set grid
set grid xtics mxtics ytics mytics front lc rgb "black" lw 2
set style fill transparent solid 0.5 noborder


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

set output "histogramm_advent.png"
plot 	"pep/pep_advent0.csv"		using 1:3 with lines lw 1, \
	"pep/pep_advent1.csv"		using 1:3 with lines lw 1, \
	"pep/pep_advent2.csv"		using 1:3 with lines lw 1, \
	"pep/pep_advent3.csv"		using 1:3 with lines lw 1, \
	"pep/pep_advent4.csv"		using 1:3 with lines lw 1
