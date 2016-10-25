#!/bin/sh

apigen generate -s . -d docs --exclude=tests --exclude=exclude --template-theme=bootstrap
