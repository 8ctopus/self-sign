# self-sign

[![license](http://poser.pugx.org/8ctopus/self-sign/license)](https://packagist.org/packages/8ctopus/self-sign)
![lines of code](https://raw.githubusercontent.com/8ctopus/self-sign/image-data/lines.svg)

self-sign is a command line tool to create self-signed SSL certificates, mainly for local testing.

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

### help

    ./selfsign --help

### generate certificate authority

    ./selfsign authority demo

    [INFO] generate certificate authority private key...
    [INFO] generate certificate authority certificate...
    [INFO] success!

### generate certificate

    ./selfsign certificate demo test.io,www.test.io,api.test.io demo

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

    php src/EntryPoint.php certificate --verbose demo test.io,www.test.io,api.test.io demo