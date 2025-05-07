# Teste Técnico - UEX

Siga os procedimentos abaixo para subir o projeto:

```bash
git clone git@github.com:andrescherrer/teste-tecnico-uex.git contatos
```

O projeto foi desenvolvido usando Laravel Sail, sendo assim, é necessário ter o docker instalado.
Executar a linha abaixo para baixar as imagens e criar os containers.

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

Rodar o comando para criar o banco de dados e a tabelas
```bash
sail artisan migrate
```

Se o comando acima falhar, rode o comando abaixo para pegar o IP do container do banco de dados. 
Altere o DB_HOST com a saída deste comando.

```bash
docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' contatos-database-1
```

Acesse pelo navegador a url: http://localhost:8888. 
Caso apareça a logo do Laravel 12, os serviços estão configurados.

O Mailpit também está disponível nessa instalação. Ele sevirá de auxílio para consultar os e-mails quando o usuário solicitar troca de senha.

Acesse: http://localhost:8025

Na raiz do projeto contém um arquivo endpoints.json para ser utilizado em algum client http.

# Endpoints:
## Auth:

### Criar conta
- POST /api/v1/signup
- body: name, email, password, password_confirmation

### Logar na conta
- POST /api/v1/signin
- body: email, password

### Deslogar
- POST /api/v1/signout
- header [Authorization] Bearer token

### Esqueci a senha
- POST /api/v1/forgot-password
- body: email (cadastrado)

### Reset senha
- POST /api/v1/reset-password
- body: email, token, password, password_confirmation

## Account
### Remover conta
- DELETE /api/v1/user
- body: password

## Contatos
### Index
- GET /api/v1/contatos
- header [Authorization] Bearer token
- params: 
- * cpf, nome (utilizado para pesquisar) 
- * order_by (campo da tabela contatos)
- * order_direction ( asc, desc )
- * per_page (quantidade de paginas ou default: 20)

### Store
- POST /api/v1/contatos
- body: nome, cpf, telefone, cep, numero, complemento (não obrigatório)

### Show
- GET /api/v1/contatos/{id} (retorna contato somente se pertencer ao usuario logado)

### PUT
- PUT /api/v1/contatos/{id}
- body: nome, cpf, telefone, cep, numero, complemento (não obrigatório)

### Destroy
- DELETE /api/v1/contatos/{id}