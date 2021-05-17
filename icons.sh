#!/bin/sh

DIRECTORY=`dirname $0`

git -C "$DIRECTORY/remotes/pancake-frontend/" pull
git -C "$DIRECTORY/remotes/valuedefi-trustwallet-assets/" pull
git -C "$DIRECTORY/remotes/trustwallet-assets/" pull
git -C "$DIRECTORY/remotes/cryptocurrency-icons/" pull