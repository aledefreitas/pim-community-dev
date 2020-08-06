FROM akeneo/pim-php-dev:4.0

ARG UID=1000
ARG GID=1000

RUN GID=${GID:=1000}
RUN UID=${UID:=1000}

RUN groupmod -g $GID www-data \
    && usermod -d '/srv/pim' -c "PHP-FPM" -g $GID -u $UID www-data

# Supervisor
RUN requirements="supervisor" \
    && apt-get update && apt-get install -y $requirements \
    && apt-get purge \
    && rm -rf /var/lib/apt/lists/*
