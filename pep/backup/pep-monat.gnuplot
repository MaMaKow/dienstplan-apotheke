set terminal png size 1600, 600 enhanced

set autoscale fix
set xtics 1





set output "monat.png";
plot	"pep_2009.csv" using ($0+1):1 with lines, \
 	"pep_2010.csv" using ($0+1):1 with lines, \
 	"pep_2011.csv" using ($0+1):1 with lines, \
 	"pep_2012.csv" using ($0+1):1 with lines, \
 	"pep_2013.csv" using ($0+1):1 with lines, \
 	"pep_2015.csv" using ($0+1):1 with lines
