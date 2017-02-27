#!/bin/bash
docker stop simpleradius
docker rm simpleradius
docker rmi simpleradius
cd SimpleRadius
git pull
cd ..
if [ ! -z "${1}" ]; then
    docker build -t simpleradius -f ./SimpleRadius/docker/Dockerfile-${1} .
else
    docker build -t simpleradius -f ./SimpleRadius/docker/Dockerfile .
fi
