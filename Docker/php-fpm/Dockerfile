FROM php:8.3.0-fpm-alpine

RUN apk update \
    && apk add \
        curl \
    && rm -rf /var/lib/apt/lists/* \
    # installing install-php-extensions
    && curl -sL -o install-php-extensions https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    && chmod u+x ./install-php-extensions \
    # installing php extensions
    && ./install-php-extensions \
        intl \
        mysqli \
        opcache \
        pdo \
        pdo_mysql \
        sockets \
        zip

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

# Installer Ollama
#RUN curl -fsSL https://ollama.com/install.sh | sh \
#    # Ajouter des permissions d'exécution à l'emplacement où le binaire Ollama a été installé
#    && chmod +x /usr/local/bin/ollama \
#    # Créer un lien symbolique dans /usr/bin
#    && ln -s /usr/local/bin/ollama /usr/bin/ollama

WORKDIR /project