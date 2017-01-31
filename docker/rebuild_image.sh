#!/bin/bash
docker stop simpleradius
docker rm simpleradius
docker rmi simpleradius
cd SimpleRadius
git pull
cd ..
docker build -t simpleradius -f ./SimpleRadius/docker/Dockerfile .