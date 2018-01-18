php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
php bin/console assets:install --symlink --env=prod
php bin/console assetic:dump --env=prod

chown -R apache:apache .
