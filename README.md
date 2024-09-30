<img src="https://github.com/marcellopato/TransferAPI/blob/main/public/images/transferAPI.png">

# Bem-vindo ao desenvolvimento do TransferAPI

## Requisitos
1.  IDE (PHPStorm, VSCode ou a de sua preferencia)
3.  [Docker](https://docs.docker.com/engine/install/ubuntu/ "https://docs.docker.com/engine/install/ubuntu/")
4.  [Git](https://git-scm.com/book/pt-br/v2/Come%C3%A7ando-Instalando-o-Git "https://git-scm.com/book/pt-br/v2/Come%C3%A7ando-Instalando-o-Git")

## Repositório

`git clone https://github.com/marcellopato/transferAPI`,

`cd transferAPI`,

`cp .env.example .env`,

```
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php81-composer:latest \
    composer install --ignore-platform-reqs
```
`./vendor/bin/sail up -d`,

## Bando de dados

`./vendor/bin/sail artisan migrate`,

`./vendor/bin/sail artisan db:seed`
