php bin/console cache:clear
php bin/console cache:warmup
php bin/console assets:install --symlink
php bin/console assetic:dump

chown -R apache:apache .
