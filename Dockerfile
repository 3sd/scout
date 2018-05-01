FROM michaelmcandrew/civicrm

RUN apt-get update\
  && apt-get install -y --no-install-recommends\
  openssh-client\
  rsync\
  wget\
  && rm -r /var/lib/apt/lists/*

COPY . /scout

RUN echo "PATH=/scout:\$PATH" >> /buildkit/.bashrc

USER buildkit

RUN mkdir -p /buildkit/.scout/cache

RUN mkdir -p /buildkit/.scout/dumps

COPY docker/.my.cnf /buildkit/.my.cnf

COPY docker/.gitconfig /buildkit/.gitconfig

USER root
