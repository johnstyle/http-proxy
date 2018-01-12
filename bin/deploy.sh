#!/bin/bash

if [ "$(whoami)" != "root" ]; then
    echo -e "\e[0;31m[✗] Vous devez être en ROOT\e[0;90m"
    exit
fi

echo -e "\e[0;32m[✓] Emplacement : $(pwd)\e[0;90m"

branch="${1}"

if [[ ! "${branch}" ]]; then
    branch=$(git rev-parse --abbrev-ref HEAD)
fi

if [[ ! "${branch}" || 'HEAD' == "${branch}" ]]; then
    echo -e "\e[0;31m[✗] Veuillez spécifier une branche\e[0;90m"
    exit
fi

# Mise à jour GIT
echo -e "\e[0;32m[✓] Update git from origin/${branch}\e[0;90m"
echo -e "\e[0;90m---------------------------------------\e[0;90m"
git fetch
git reset --hard origin/${branch}
echo -e "\e[0;90m---------------------------------------\e[0;90m"

# Mise à jour Composer
echo -e "\e[0;32m[✓] Update composer.phar\e[0;90m"
echo -e "\e[0;90m---------------------------------------\e[0;90m"
php "./vendor/bin/composer" --no-dev --optimize-autoloader --classmap-authoritative --no-plugins -v install
echo -e "\e[0;90m---------------------------------------\e[0;90m"

# Redémarage du serveur web
if [[ -f "/etc/init.d/nginx" ]]; then
    echo -en "\e[0;32m[✓] "
    /etc/init.d/nginx reload
fi
echo -en "\e[0;90m"

# Mise à jour des permissions des fichiers
directories=(
    var/ \
)

files=(

)

user='www-data';
group='www-data';
chmod=775;

if [ "${1}" ]; then
    user=${1};
fi

if [ "${2}" ]; then
    group=${2};
fi

if [ "${3}" ]; then
    chmod=${2};
fi

for directory in ${directories[@]}; do
    if [[ -d "${directory}" ]]; then
        chown -R ${user}:${group} "${directory}"
        find "${directory}" ! -name '.gitignore' -exec chmod ${chmod} {} +
        echo -e "\e[0;32m[✓] Set permissions ${user}:${group} ${chmod} to ${directory}\e[0;90m"
    fi
done

for file in ${files[@]}; do
    if [[ -f "${file}" ]]; then
        chown -R ${user}:${group} "${file}"
        chmod -R ${chmod} "${file}"
        echo -e "\e[0;32m[✓] Set permissions ${user}:${group} ${chmod} to ${file}\e[0;90m"
    fi
done
