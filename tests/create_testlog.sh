#!/bin/bash
#This script will create a list of tests to be done for the current commit.
root_folder=$(git rev-parse --show-toplevel);
echo "Changing directory to $root_folder"
cd "$root_folder/tests"; #will only cd if we are above tests
mkdir -p "$root_folder/tests/log";

branch_name=$(git symbolic-ref --short HEAD);
if [ "testing" == "$branch_name" ]; then
    commit_name=$(git describe);
    target_file_name="$root_folder/tests/log/tests_for_$commit_name";
    if [ ! -f $target_file_name ]; then
        source_file="$root_folder/tests/List of Tests";
        cp "$source_file" "$target_file_name";
        git add "$target_file_name";
    fi
fi
