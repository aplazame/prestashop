#!/bin/bash

set -e 

# Dependencies
sudo apt-get update
sudo apt-get install php5-cli

# Tests and syntax checker
# make test
make syntax.checker


case $DRONE_BRANCH in
    master)
        # Install zip package
        sudo apt-get install zip

        # Create zip package
        make zip
        ;;

    *)
        echo *$DRONE_BRANCH* pull request, all checks have passed
        ;;
esac
