versionString=`git describe --abbrev=0 --tags`
sed -i -e 's/currentVersionString/'$versionString'/' /src/php/pages/about.php