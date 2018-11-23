#!/bin/bash
#This script will create a list of tests to be done for the current commit.
root_folder=$(git rev-parse --show-toplevel);
branch_name=$(git symbolic-ref --short HEAD);
commit_name=$(git describe --tags);
echo "Changing directory to $root_folder";
#cd "$root_folder/tests"; #will only cd if we are above tests
mkdir -p "$root_folder/tests/log";

if [ "testing" == "$branch_name" ]
then
    echo "We are on the testing branch. A test log file will be created.";
    source_file="$root_folder/tests/List_of_tests.md";
    target_file_name="$root_folder/tests/log/tests_for_$commit_name"".md";
    if [ ! -f $target_file_name ]
    then
        echo "Create $target_file_name, please use it to document tests as passed or failed.";
        cp "$source_file" "$target_file_name";
        git add -f "$target_file_name";
    else
        echo "$target_file_name already exists.";
        :
    fi
else
    echo "We are not on the testing branch. Nothing to do here.";
    :
fi
