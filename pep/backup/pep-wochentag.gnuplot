set terminal png size 1600, 600 enhanced
set xdata time
set timefmt "%H:%M"
set output "pep_1.png";	plot	"pep_1.csv" using ($1):2:3 with filledcu fs pattern 2, 	"pep_1.csv" using ($1):4:3 with filledcu fs pattern 2, 	"pep_1.csv" using ($1):3 with linespoints
set output "pep_2.png";	plot	"pep_2.csv" using ($1):2:3 with filledcu fs pattern 2, 	"pep_2.csv" using ($1):4:3 with filledcu fs pattern 2, 	"pep_2.csv" using ($1):3 with linespoints
set output "pep_3.png";	plot	"pep_3.csv" using ($1):2:3 with filledcu fs pattern 2, 	"pep_3.csv" using ($1):4:3 with filledcu fs pattern 2, 	"pep_3.csv" using ($1):3 with linespoints
set output "pep_4.png";	plot	"pep_4.csv" using ($1):2:3 with filledcu fs pattern 2, 	"pep_4.csv" using ($1):4:3 with filledcu fs pattern 2, 	"pep_4.csv" using ($1):3 with linespoints
set output "pep_5.png";	plot	"pep_5.csv" using ($1):2:3 with filledcu fs pattern 2, 	"pep_5.csv" using ($1):4:3 with filledcu fs pattern 2, 	"pep_5.csv" using ($1):3 with linespoints
set output "pep_6.png";	plot	"pep_6.csv" using ($1):2:3 with filledcu fs pattern 2, 	"pep_6.csv" using ($1):4:3 with filledcu fs pattern 2, 	"pep_6.csv" using ($1):3 with linespoints
set output "pep_7.png";	plot	"pep_7.csv" using ($1):2:3 with filledcu fs pattern 2, 	"pep_7.csv" using ($1):4:3 with filledcu fs pattern 2, 	"pep_7.csv" using ($1):3 with linespoints
