FROM php:8.2-cli

WORKDIR /app

# Install utilities
RUN apt-get update && apt-get install -y curl zip unzip nano iputils-ping wget libpq-dev

# Install PHP extensions
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install pdo_pgsql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHPUnit
RUN curl -LO https://phar.phpunit.de/phpunit-9.6.phar
RUN chmod +x phpunit-9.6.phar
RUN mv phpunit-9.6.phar /usr/local/bin/phpunit

COPY entrypoint.sh /run/entrypoint.sh
RUN chmod +x /run/entrypoint.sh

RUN ln -sf /bin/bash /bin/sh

ENTRYPOINT [ "/run/entrypoint.sh" ]
