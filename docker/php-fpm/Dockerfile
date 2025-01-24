FROM php:8.3-fpm
WORKDIR /application

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev \
        zlib1g-dev \
        libpq-dev \
        libzip-dev \
        libpcre3-dev \
        libonig-dev \
        python3 \
        python3-uno \
        ssmtp \
        vim \
        git \
        cron \
        zip \
        libreoffice \
        unoconv \
    && docker-php-ext-install \
        intl \
        mbstring \
        pdo_pgsql \
        zip \
        sockets \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Criar e garantir permissões para o diretório do LibreOffice
RUN mkdir -p /var/www/.cache/dconf && chown -R www-data:www-data /var/www/.cache \
    && mkdir -p /var/www/.config/libreoffice && chown -R www-data:www-data /var/www/.config/libreoffice \
    && chmod -R 777 /tmp /var/tmp /var/www/.cache /var/www/.config/libreoffice

# Configurar variáveis de ambiente para o LibreOffice
ENV SAL_USE_VCLPLUGIN=gtk
ENV LIBREOFFICE_USER_PROFILE=/tmp/.libreoffice

#CMD ["php", "/application/src/Infra/Services/EmailService.php"]