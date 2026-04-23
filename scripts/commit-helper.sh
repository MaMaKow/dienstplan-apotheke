#!/bin/bash
set -o pipefail
################################################################################
## This script is meant to help with various tasks before, while or after
## committing new code.
################################################################################

# Function to exit on error:
error_exit(){
    echo
    echo "$1"
    exit 1
}

# Cross-platform sed -i wrapper
sedi() {
  if sed --version >/dev/null 2>&1; then
    # GNU sed
    sed -i "$@"
  else
    # BSD sed (macOS)
    sed -i '' "$@"
  fi
}

check_staged_changes() {
    if git diff --cached --exit-code > /dev/null; then
        error_exit "No changes added to the staging area. Exiting."
    fi
}

get_highest_version() {
    {
        git tag
        git ls-remote --tags origin | awk '{print $2}' | sed 's#refs/tags/##' | sed 's/\^{}//'
    } \
    | grep -E '^[0-9]+\.[0-9]+\.[0-9]+$' \
    | sort -V \
    | tail -n 1
}

check_git_safety_state() {
    # Rebase aktiv?
    if [ -d .git/rebase-merge ] || [ -d .git/rebase-apply ]; then
        echo "ERROR: Rebase ist aktiv."
        echo "commit-helper.sh darf während eines Rebase nicht ausgeführt werden."
        exit 1
    fi

    # Merge aktiv?
    if [ -f .git/MERGE_HEAD ]; then
        echo "ERROR: Merge-Konflikt erkannt."
        echo "Bitte zuerst Merge abschließen."
        exit 1
    fi

    # Cherry-pick aktiv?
    if [ -f .git/CHERRY_PICK_HEAD ]; then
        echo "ERROR: Cherry-pick ist aktiv."
        echo "Bitte zuerst Cherry-pick abschließen."
        exit 1
    fi
}

check_git_safety_state
# Prüfe, ob Änderungen vorliegen:
check_staged_changes

# Fetch from origin and get the current version number:
git fetch --all --tags || error_exit "git fetch fehlgeschlagen"
highest_version=$(get_highest_version)
if [ -z "$highest_version" ]; then
    error_exit "Keine Versionstags gefunden (x.y.z)."
fi

if [[ ! "$highest_version" =~ ^([0-9]+)\.([0-9]+)\.([0-9]+)$ ]]; then
    error_exit "Ungültiges Versionsformat: $highest_version"
fi

base_major="${BASH_REMATCH[1]}"
base_minor="${BASH_REMATCH[2]}"
base_patch="${BASH_REMATCH[3]}"

# Get the current branch name:
current_branch=$(git symbolic-ref --short HEAD)

if ! git merge-base --is-ancestor origin/"$current_branch" HEAD; then
    error_exit "Lokaler Branch enthält nicht die neuesten Remote-Änderungen."
fi

# Display information about the current state:
echo "We are in the directory"
pwd

# Zeilennummern aus po-Datei entfernen
po_file="./locale/de_DE/LC_MESSAGES/messages.po"
mo_file="./locale/de_DE/LC_MESSAGES/messages.mo"
sedi 's/#:[[:space:]]*.*:.*$//' "$po_file"
echo "In Kommentaren wurden Zeilenangaben aus der $po_file-Datei entfernt."
git add "$po_file"
git add "$mo_file"

echo
echo "Showing git status:"
git status

echo "Please review your changes above!"
while true; do
    read -p "Ready to COMMIT? [y/n] " -n 1 decision_commit
    echo
    if [[ "$decision_commit" == "y" || "$decision_commit" == "Y" ]]; then
        break
    elif [[ "$decision_commit" == "n" || "$decision_commit" == "N" ]]; then
        error_exit "You are not ready to commit yet."
    else
        echo "Invalid input. Please enter 'y' or 'n'."
    fi
done

# Check if the current branch is one of the allowed branches for tagging:
if [ "$current_branch" != "development" ] && [ "$current_branch" != "testing" ] && [ "$current_branch" != "master" ]; then
    featureBranch=true
    echo "$current_branch probably is a feature branch: We will only tag commits on the 'development,' 'testing,' or 'master' branches."
else
    featureBranch=false
    echo
    read -p "New MAJOR version? [y/n] " -n 1 decision_major
    echo

    if [[ "$decision_major" =~ [yY] ]]; then
        new_version_major=$((base_major + 1))
        new_version_minor=0
        new_version_patch=0
    else
        read -p "New MINOR version? [y/n] " -n 1 decision_minor
        echo

        if [[ "$decision_minor" =~ [yY] ]]; then
            new_version_major=$base_major
            new_version_minor=$((base_minor + 1))
            new_version_patch=0
        else
            new_version_major=$base_major
            new_version_minor=$base_minor
            new_version_patch=$((base_patch + 1))
        fi
    fi


    new_version="${new_version_major}.${new_version_minor}.${new_version_patch}"
    echo
    echo "Highest existing version: $highest_version"
    echo "New version will be:      $new_version"

    grep -q 'id="pdrVersionSpan"' src/php/pages/about.php || error_exit "Versionsstring in about.php nicht gefunden."
    sedi 's#<span id="pdrVersionSpan">[0-9.]*</span>#<span id="pdrVersionSpan">'$new_version'</span>#' src/php/pages/about.php
    git add src/php/pages/about.php

    grep -q '<artifactId>selenium' tests/selenium/pom.xml || error_exit "Versionsstring in pom.xml nicht gefunden."
    sedi '/<artifactId>selenium</,/<name>SeleniumTest/ s#\(<version>\).*\(</version>\)#\1'$new_version'\2#' tests/selenium/pom.xml
    git add tests/selenium/pom.xml
fi

php "tests/calculate_database_version_hash.php"
echo "Created a new database version hash:"
cat ./src/php/database_version_hash.php
git add "./src/php/database_version_hash.php"

echo "Starte GPG-signierten Commit..."
git commit --gpg-sign
#git pull --rebase origin "$current_branch" || error_exit "Rebase fehlgeschlagen" # Wir haben mit 'git merge-base --is-ancestor' bereits geprüft, dass kein rebase notwendig ist.

if [ false == "$featureBranch" ]; then
    echo "Commit wird mit Version $new_version getaggt."
    [ -z "$new_version" ] && error_exit "Keine neue Version gesetzt."
    git tag "$new_version"
else
    echo "Kein Tagging auf Feature-Branch '$current_branch'."
fi

git show -1
git status
while true; do
    read -p "Ready to PUSH changes and tags to remote? [y/n] " -n 1 decision_push
    echo
    if [[ "$decision_push" == "y" || "$decision_push" == "Y" ]]; then
        break
    elif [[ "$decision_push" == "n" || "$decision_push" == "N" ]]; then
        error_exit "You are not ready to push yet."
    else
        echo "Invalid input. Please enter 'y' or 'n'."
    fi
done

git push origin "$current_branch"
if [ false == "$featureBranch" ]; then
    git push origin "$new_version" # Tag veröffentlichen
    while true; do
        read -p "Is this branch ready for TESTING branch? [y/n] " -n 1 decision_testing
        echo
        if [[ "$decision_testing" == "y" || "$decision_testing" == "Y" ]]; then
            break
        elif [[ "$decision_testing" == "n" || "$decision_testing" == "N" ]]; then
            error_exit "Branch is not ready for testing yet."
        else
            echo "Invalid input. Please enter 'y' or 'n'."
        fi
    done
    git push origin "$current_branch:testing"
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
