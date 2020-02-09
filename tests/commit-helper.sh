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
echo "We are currently on the commit $current_version.";
echo "major: $current_version_major";
echo "minor $current_version_minor";
echo "patch $current_version_patch";
echo "";
read -p "Show git status? [y/n] " -N 1 decision_git_status;
if [ "y" == "$decision_git_status" ] || [ "Y" == "$decision_git_status" ]
then
    clear;
    git status;
fi

# Determine the correct tag for this commit:
echo "";
read -p "Will this commit be tagged as a new major version? [y/n] " -N 1 decision_git_tag_major;
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
    read -p "Will this commit be tagged as a new minor version? [y/n] " -N 1 decision_git_tag_minor;
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