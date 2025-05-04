# Teste Técnico - UEX

Siga os procedimentos abaixo para subir o projeto:

```bash
git clone git@github.com:andrescherrer/teste-tecnico-uex.git contatos
```

O projeto foi desenvolvido usando Laravel Sail, sendo assim, é necessário ter o docker instalado e executar a linha abaixo:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

Obs.: Entendo os motivos de não carregar o .env para o repositório, porém, com intuito de facilitar a start da aplicação, o .env.example é exatamente o .env necessário para que a aplicação funcione.

Criar o .env a partir de .env.example
```bash
cp .env.example .env
```

Subir o projeto
```bash
sail up -d --build
```

Rodar o comando para criar e popular o banco de dados
```bash
sail artisan migrate
```

Se o comando acima falhar, rode o comando abaixo para pegar o IP do container do banco de dados e faça altere o DB_HOST com informado neste comando.

```bash
docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' contatos-database-1
```

Acesse pelo navegador a url: http://localhost:8888. 
Caso apareça a logo do Laravel 12, os serviços estão configurados.

O Mailpit também está disponível nessa instalação. Ele sevirá de auxílio para consultar os e-mails quando o usuário solicitar troca de senha.

Acesse: http://localhost:8025