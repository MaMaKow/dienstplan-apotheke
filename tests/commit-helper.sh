#!/bin/bash
################################################################################
## This script is meant to help with various tasks before, while or after
## commiting new code.
################################################################################

# Get the current version number:
current_version=`git describe --tags --long HEAD`;
current_version_major=`echo $current_version | cut -d. -f1 -`;
current_version_minor=`echo $current_version | cut -d. -f2 -`;
current_version_patch=`echo $current_version | cut -d. -f3 - | cut -d- -f1 -`;

# Display information about the current state:
clear;
echo "We are in the directory"
pwd
echo "We are currently on the commit $current_version.";
echo "major: $current_version_major";
echo "minor $current_version_minor";
echo "patch $current_version_patch";

# echo "Writting current state of the database structure into the src/sql/ folder";
# php "tests\get-database-structure.php";

echo "";
echo "Showing git status: "; 
git status;

# Determine the correct tag for this commit:
echo "";
read -p "Will this commit be tagged as a new MAJOR version? [y/n] " -N 1 decision_git_tag_major;
if [ "y" == "$decision_git_tag_major" ] || [ "Y" == "$decision_git_tag_major" ]
then
    # We start a new major branch.
    # This automatically means, that the minor version and the patch version are set to 0.
    new_version_major=$(($current_version_major + 1));
    new_version_minor=0;
    new_version_patch=0;
else
    # We keep the old number:
    new_version_major=$current_version_major;

    # But perhaps the minor version changed?
    echo "";
    read -p "Will this commit be tagged as a new Minor version? [y/n] " -N 1 decision_git_tag_minor;
    if [ "y" == "$decision_git_tag_minor" ] || [ "Y" == "$decision_git_tag_minor" ]
    then
        new_version_minor=$(($current_version_minor + 1));
        new_version_patch=0;
    else
        # We keep the old number
        new_version_minor=$current_version_minor;
        new_version_patch=$(($current_version_patch + 1));
    fi
fi

new_version=$new_version_major.$new_version_minor.$new_version_patch;
echo "";
echo "The new version number will be $new_version";
sed -i 's/<span id="pdrVersionSpan">[0-9.]*<\/span>/<span id="pdrVersionSpan">'$new_version'<\/span>/' src/php/pages/about.php
git add src/php/pages/about.php
sed -i '/<artifactId>selenium</,/<name>SeleniumTest</ s/\(<version>\).*\(<\/version>\)/\1'$new_version'\2/' tests/selenium/pom.xml
git add tests/selenium/pom.xml
# TODO: <p lang=de>Ich würde hier sehr gerne das script Tests\get-database-structure.php laufen lassen.
# Dabei gibt es allerdings ein Problem.
# Eine Entwicklungsumgebung hat nicht zwingend Zugriff auf eine Datenbank. Sie kann also nicht immer ihre eigene Datenbankstruktur besitzen.
# Um die Datebankstruktur als Datei zu speichern und auch den PDR_DATABASE_VERSION_HASH upzudaten, muss ich aber Zugriff auf die "aktuelle" Datanbank haben.
# Als workaround könnte man vielleicht den hash über die vorhandenen *.sql files machen.
# Funktioniert das?
# Auf jeden Fall müssten die folgenden Zeilen berücksichtigt werden:
# $table_structure_create_with_increment = preg_replace('/CREATE TABLE/', 'CREATE TABLE IF NOT EXISTS', $row['Create Table']);
# $table_structure_create = preg_replace('/AUTO_INCREMENT=[0-9]*/', '', $table_structure_create_with_increment);
# </p>
php "tests/calculate_database_version_hash.php"
echo "Created a new database version hash:"
cat ./src/php/database_version_hash.php
git add "./src/php/database_version_hash.php"

read -p "Ready to COMMIT and sign the changes? [y/n] " -N 1 decision_commit;
if [ "y" == "$decision_commit" ] || [ "Y" == "$decision_commit" ]
then
    clear
    git commit --gpg-sign && git tag "$new_version"
    git show -1
    git status
    read -p "Ready to PUSH changes and tags to remote? [y/n] " -N 1 decision_push;
    if [ "y" == "$decision_push" ] || [ "Y" == "$decision_push" ]
    then
	git push origin
	git push origin --tags
    fi
fi
