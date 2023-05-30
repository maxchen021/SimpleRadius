#docker build -t simpleradius -f ./SimpleRadius/docker/Dockerfile .

FROM debian:11
EXPOSE 443 1812 1812/udp

COPY SimpleRadius /tmp/SimpleRadius/

RUN cd /tmp/SimpleRadius \
&& bash ./installation/debian/install.sh \
&& cp /tmp/SimpleRadius/docker/docker_entrypoint.sh /docker_entrypoint.sh \
&& chmod 755 /docker_entrypoint.sh \
&& rm -rf /tmp/SimpleRadius \
&& rm /var/run/freeradius/freeradius.pid


ENTRYPOINT ["/docker_entrypoint.sh"]