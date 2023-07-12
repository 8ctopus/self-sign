# selfsign

[![license](http://poser.pugx.org/8ctopus/self-sign/license)](https://packagist.org/packages/8ctopus/self-sign)
![lines of code](https://raw.githubusercontent.com/8ctopus/self-sign/image-data/lines.svg)

`selfsign` is a command line tool to create self-signed SSL certificates, mainly for local testing.
It is notably used in my other project [apache php-fpm alpine](https://github.com/8ctopus/apache-php-fpm-alpine).

## how to install

You have the choice between:
- composer install `composer require 8ctopus/self-sign`
- download the phar
- or build it yourself

```sh
# download selfsign
curl -LO https://github.com/8ctopus/self-sign/raw/master/bin/selfsign.phar

# check hash against the one published under releases
sha256sum selfsign.phar

# make phar executable
chmod +x selfsign.phar

# rename phar (from here on optional)
mv selfsign.phar selfsign

# move phar to /usr/local/bin/
mv selfsign /usr/local/bin/
```

## how to use

### help

    ./selfsign --help

### generate certificate authority

    ./selfsign authority destination-dir

    [INFO] generate certificate authority private key...
    [INFO] generate certificate authority certificate...
    [INFO] success!

### generate certificate

    ./selfsign certificate destination-dir test.io,www.test.io,api.test.io authority-dir

    [INFO] generate self-signed SSL certificate for test.io...
    [INFO] generate domain private key...
    [INFO] create certificate signing request...
    [INFO] create certificate config file...
    [INFO] create signed certificate by certificate authority...
    [INFO] success!

## for development

### build phar

    ./build.sh

### debug code

    php src/EntryPoint.php authority demo
    php src/EntryPoint.php certificate demo test.io,www.test.io,api.test.io demo
