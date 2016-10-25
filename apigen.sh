#!/bin/sh

apigen generate -s . -d docs --exclude=examples/ --exclude=exclude/ --exclude=tests/ --template-theme=bootstrap --tree
