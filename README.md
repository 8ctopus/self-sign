# self-sign

[![license](http://poser.pugx.org/8ctopus/self-sign/license)](https://packagist.org/packages/8ctopus/self-sign)
![lines of code](https://raw.githubusercontent.com/8ctopus/self-sign/image-data/lines.svg)

self-sign is a command line tool to create self-signed SSL certificates

## how to install

```sh
# download selfsign
curl -LO https://github.com/8ctopus/self-sign/releases/download/0.1.0/selfsign.phar

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

   selfsign generate domain.com,www.domain.com

## build phar

    php src/BuildPhar.php

## debug code

    php src/EntryPoint.php generate test test.com --verbose
