FROM selenium/standalone-firefox:latest

# Add the locale modification command
USER root
RUN sed -i '/de_DE.UTF-8/s/^# //g' /etc/locale.gen && sudo locale-gen
ENV LC_ALL de_DE.UTF-8
ENV LANG_WHICH=de
ENV LANG_WHERE=DE
ENV ENCODING=UTF-8
ENV LANGUAGE=de_DE.UTF-8
ENV LANG=de_DE.UTF-8
#ENV TZ=UTC
#RUN echo "${TZ}" > /etc/timezone && dpkg-reconfigure --frontend noninteractive tzdata

ENV FIREFOX_DOWNLOAD_URL="https://download.mozilla.org/?product=firefox-latest-ssl&os=linux64&lang=de"
RUN echo ${FIREFOX_DOWNLOAD_URL}
RUN wget --no-verbose -O /tmp/firefox.tar.bz2 ${FIREFOX_DOWNLOAD_URL}
RUN rm -rf /opt/firefox
RUN rm -rf /opt/firefox-latest
RUN tar -C /opt -xjf /tmp/firefox.tar.bz2
RUN rm /tmp/firefox.tar.bz2
RUN mv /opt/firefox /opt/firefox-latest
RUN ln -fs /opt/firefox-latest/firefox /usr/bin/firefox

# Switch back to the default user "seluser" used by the base image
USER seluser
RUN mkdir -p ~/selenium/
RUN wget https://github.com/MaMaKow/dienstplan-apotheke/raw/master/tests/selenium/PepData.asy -O ~/selenium/PepData.asy
# after changing this file call the command "docker build -t dienstplan_selenium ."!
# start container with "docker run -d -p 4444:4444 -p 7900:7900 -p 5900:5900 --shm-size="2g" -v /tmp/selenium/shared_downloads:/home/seluser/Downloads dienstplan_selenium"