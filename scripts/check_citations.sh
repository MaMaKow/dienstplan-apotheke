#!/bin/bash

# Script to compare listed artwork with found images

# find artwork which is cited:
grep '\.\./img' src/php/pages/list_of_artwork.php | cut -d"/" -f 5 | cut -d\" -f 1 | sort > tmp/listed_artwork.del

# find images in the image path:
ls -lQ img/ | cut -d\" -f2 > tmp/found_artwork.del

# compare the lists to find missing citations:
diff --color tmp/found_artwork.del tmp/listed_artwork.del

# remove temporary files:
rm tmp/listed_artwork.del
rm tmp/found_artwork.del
