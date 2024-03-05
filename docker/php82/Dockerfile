FROM php:8.2-fpm
WORKDIR "/application"

# more extensions, refer https://hub.docker.com/r/phpdocker/phpdocker/dockerfile

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive

# Install selected extensions and other stuff
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        libsodium-dev \
        zip \
        openssh-client \
        git \
        sshpass\
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install mysqli pdo_mysql sodium exif zip\
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# math
RUN apt-get update \
	&& apt-get install -y libgmp-dev \
	&& ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/include/gmp.h \
	&& docker-php-ext-install -j$(nproc) \
		gmp \
		bcmath
RUN apt-get -y update \
    && apt-get install -y libicu-dev \ 
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

RUN apt-get update && apt-get install -y libmagickwand-dev --no-install-recommends && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer