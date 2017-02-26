#This script will produce images of the website.
#It requires wkhtmltox in the the version 0.12.3 (with pached qt)

cd ..
for file in *-out.php
do
#	#filename="${file##*/}"
	filename="${file%.*}"
#	wkhtmltopdf --username Mitarbeiter \
#		--password GrosseFreude \
#		--zoom 0.9 \
#		--orientation landscape \
#		"localhost/git/dienstplan/$file?datum=2016-01-26&auswahlMitarbeiter=5" \
#		"documentation/$filename.pdf"
#	pdfcrop "documentation/$filename.pdf" "documentation/$filename.pdf"
	
	wkhtmltoimage \
	--zoom 0.8 \
	"localhost/git/dienstplan/$file?datum=2016-01-26&auswahlMitarbeiter=5" \
	documentation/$filename.png
done
	#"martin-mandelkow.de/apotheke/dienstplan/$file?datum=2016-01-26&auswahlMitarbeiter=5" \
