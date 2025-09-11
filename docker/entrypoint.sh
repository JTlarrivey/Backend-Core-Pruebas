#!/usr/bin/env bash
set -e
PORT="${PORT:-8080}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s!<VirtualHost \*:.*>!<VirtualHost *:${PORT}>!" /etc/apache2/sites-available/000-default.conf
echo "Apache on port ${PORT} (DocumentRoot=${APACHE_DOCUMENT_ROOT})"
exec apache2-foreground
