cd ../img/
for file in *.svg
do 
    filename=$(basename "$file") 
    inkscape -D -z --file=$file --export-pdf=${filename%.svg}.pdf
done

