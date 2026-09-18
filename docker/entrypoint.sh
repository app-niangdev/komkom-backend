#!/bin/sh
set -e

# Permissions (au cas où le volume monté écrase celles de l'image)
chmod -R 775 storage bootstrap/cache

# Attendre que PostgreSQL soit prêt avant de continuer
until php artisan db:show > /dev/null 2>&1; do
  echo "En attente de PostgreSQL..."
  sleep 2
done

# Migrations + cache (à exécuter ici seulement si vous voulez que ce soit
# automatique à chaque démarrage de conteneur — sinon, préférez les
# déclencher explicitement depuis le workflow GitHub Actions, section 13
# du manuel précédent, pour garder un contrôle plus fin)
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
