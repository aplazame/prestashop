#!/bin/bash

set -e 

# tests
# make test

case $DRONE_BRANCH in
    master)
        # install zip package
        sudo apt-get install zip

        # create zip package
        make zip
        ;;

    *)
        echo *$DRONE_BRANCH* pull request, all checks have passed
        ;;
esac
