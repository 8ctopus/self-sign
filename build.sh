# remove development dependencies from phar
composer install --no-dev

#php src/BuildPhar.php
php box.phar compile

composer install
