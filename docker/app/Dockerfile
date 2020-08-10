FROM akeneo/pim-php-dev:4.0

ARG UID=1001
ARG GID=1001

RUN GID=${GID:=1001}
RUN UID=${UID:=1001}

RUN groupmod -g $GID www-data \
    && usermod -d '/srv/pim' -c "PHP-FPM" -g $GID -u $UID www-data

# Supervisor
RUN requirements="supervisor" \
    && apt-get update && apt-get install -y $requirements \
    && apt-get purge \
    && rm -rf /var/lib/apt/lists/*
