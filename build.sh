#php src/BuildPhar.php
php box.phar compile

$(php -r "file_put_contents('bin/selfsign.sha256', hash_file('sha256', 'bin/selfsign.phar'));")
