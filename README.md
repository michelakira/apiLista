
# API Restful

Desenvolvimento de uma API Restful em PHP com autenticação JWT.

## Autores

- [@michelakira](https://github.com/michelakira)


## Instalação

Instale os arquivo dentro da pasta htdocs e faça a importação do arquivo api.sql no banco de dados.




## Documentação

Na pasta POSTMAN podemos encontrar a collection e environment dos endpoints que contém todas as funcionalidades da API.

Na pasta Documentação existe um arquvivo index.html que contém visualmente uma documentação dos endpoints como Curl, retornos em Json e os códigos do erro.



## Documentação da API

#### Retorna token de acesso no formato JWT

```http
  POST /api/oauth/token
```

| Parâmetro(Header)   | Tipo       | Descrição                           |
| :---------- | :--------- | :---------------------------------- |
| `Content-Type` | `application/x-www-form-urlencoded` | **Obrigatório**.|

| Parâmetro(Query)   | Tipo       | Descrição                           |
| :---------- | :--------- | :---------------------------------- |
| `login` | `String` | **Obrigatório**. Login do usuário do sistema.|
| `senha` | `String` | **Obrigatório**. Senha do usuário do sistema.|


#### Adicionar uma nova lista

```http
  POST /api/lista/adicionar
```

| Parâmetro(Header)   | Tipo       | Descrição                           |
| :---------- | :--------- | :---------------------------------- |
| `Authorization` | `Bearer {access_token}` | **Obrigatório**. Token gerado pela autenticação do usuário.|

| Parâmetro(Query)   | Tipo       | Descrição                           |
| :---------- | :--------- | :---------------------------------- |
| `titulo ` | `String(150)` | **Obrigatório**. Título da Lista|
| `descricao ` | `Text` | **Obrigatório**. Descrição da Lista|


#### Consultar todas as listas

```http
  GET /api/lista/consultar
```

| Parâmetro(Header)   | Tipo       | Descrição                           |
| :---------- | :--------- | :---------------------------------- |
| `Authorization` | `Bearer {access_token}` | **Obrigatório**. Token gerado pela autenticação do usuário.|


#### Consultar uma lista específica

```http
  GET /api/lista/consultar/{codigo_lista}
```

| Parâmetro(Header)   | Tipo       | Descrição                           |
| :---------- | :--------- | :---------------------------------- |
| `Authorization` | `Bearer {access_token}` | **Obrigatório**. Token gerado pela autenticação do usuário.|

| Parâmetro(Query)   | Tipo       | Descrição                           |
| :---------- | :--------- | :---------------------------------- |
| `codigo_lista ` | `INT` | **Obrigatório**. Código da lista no sistema|



```http
  PUT,PATCH /api/lista/atualizar/{codigo_lista}
```

| Parâmetro(Header)   | Tipo       | Descrição                           |
| :---------- | :--------- | :---------------------------------- |
| `Authorization` | `Bearer {access_token}` | **Obrigatório**. Token gerado pela autenticação do usuário.|
| `Content-Type` | `application/x-www-form-urlencoded` | **Obrigatório**.|

| Parâmetro(Query)   | Tipo       | Descrição                           |
| :---------- | :--------- | :---------------------------------- |
| `codigo_lista ` | `INT` | **Obrigatório**. Código da lista no sistema|
| `titulo ` | `String(150)` | **Obrigatório**. Título da Lista|
| `descricao ` | `Text` | **Obrigatório**. Descrição da Lista|


```http
  DELETE /api/lista/excluir/{codigo_lista}
```

| Parâmetro(Header)   | Tipo       | Descrição                           |
| :---------- | :--------- | :---------------------------------- |
| `Authorization` | `Bearer {access_token}` | **Obrigatório**. Token gerado pela autenticação do usuário.|

| Parâmetro(Query)   | Tipo       | Descrição                           |
| :---------- | :--------- | :---------------------------------- |
| `codigo_lista ` | `INT` | **Obrigatório**. Código da lista no sistema|






