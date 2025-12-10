#!/bin/sh

if [ $# -eq 0 ]; then
    echo "Usage: $0 <bit.ly URL>"
    exit 1
fi

curl -sI "$1" | grep -i '^Location:' | cut -d' ' -f2
