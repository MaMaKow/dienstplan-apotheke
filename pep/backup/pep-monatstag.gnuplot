set terminal png size 1600, 600 enhanced

set output "pep_monatstag.png";	plot	"pep_tag.csv" using ($1):2:3 with filledcu fs pattern 2, 	"pep_tag.csv" using ($1):4:3 with filledcu fs pattern 2, 	"pep_tag.csv" using ($1):3 with linespoints
