#!/bin/bash
################################################################################
## This script is meant to help with various tasks before, while or after
## commiting new code.
################################################################################

# Function to exit on error:
error_exit(){
    echo;
    echo "$1";
    exit 1;
}
check_staged_changes() {
    if git diff --cached --exit-code > /dev/null;
    then
        error_exit "No changes added to the staging area. Exiting.";
    fi;
}
# Get the current version number:
current_version=`git describe --tags --long HEAD`;
current_version_major=`echo "$current_version" | cut -d. -f1 -`;
current_version_minor=`echo "$current_version" | cut -d. -f2 -`;
current_version_patch=`echo "$current_version" | cut -d. -f3 - | cut -d- -f1 -`;

# Get the current branch name:
current_branch=$(git symbolic-ref --short HEAD)

# Display information about the current state:
clear;
echo "We are in the directory"
pwd
echo "We are currently on the commit $current_version.";
echo "major: $current_version_major";
echo "minor: $current_version_minor";
echo "patch: $current_version_patch";

# Zeilennummern aus po-Datei entfernen
# Pfad zur messages.po-Datei:
po_file="./locale/de_DE/LC_MESSAGES/messages.po"
# Verwende sed, um die Kommentare zu entfernen und die Datei zu überschreiben
sed -i 's/\(#:[[:space:]]*.*:\)[0-9]*$/\1/' "$po_file"
sed -i 's/\(#:[[:space:]]*[^0-9]*:\)[0-9]*/\1/' "$po_file"
echo "In Kommentaren wurden Zeilenangaben aus der $po_file-Datei entfernt."
git add "$po_file"

# echo "Writting current state of the database structure into the src/sql/ folder";
# php "tests\get-database-structure.php";

echo "";
echo "Showing git status: ";
git status;

echo "Please review your changes above!";
read -p "Ready to COMMIT? [y/n] " -N 1 decision_commit;
if [ "y" != "$decision_commit" ] && [ "Y" != "$decision_commit" ]
then
    error_exit "You are not ready to commit yet.";
fi
clear

# Check if the current branch is one of the allowed branches for tagging:
if [ "$current_branch" != "development" ] && [ "$current_branch" != "testing" ] && [ "$current_branch" != "master" ]
then
    featureBranch="true";
    echo "$current_branch probably is a feature branch: We will only tag commits on the 'development,' 'testing,' or 'master' branches."
else
    featureBranch="false";
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
fi

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


git commit --gpg-sign

if [ "false" == "$featureBranch" ]
then
    echo "Debug: Tagging commit with version $new_version"
    git tag "$new_version"
else
    echo "Debug: No tagging on branch $current_branch"
fi

git show -1
git status
read -p "Ready to PUSH changes and tags to remote? [y/n] " -N 1 decision_push;
if [ "y" != "$decision_push" ] && [ "Y" != "$decision_push" ]
then
    error_exit "You are not ready to push yet.";
fi
git push origin
git push origin --tags

read -p "Is this branch ready for TESTING branch? [y/n] " -N 1 decision_testing;
if [ "y" != "$decision_testing" ] && [ "Y" != "$decision_testing" ]
then
    error_exit "Branch is not ready for testing yet.";
fi
git push origin development:testing
