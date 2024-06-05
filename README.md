```markdown
# Banco Simplificado

O Banco Simplificado é uma plataforma de pagamentos simplificada onde é possível depositar e realizar transferências de
dinheiro entre usuários. Temos dois tipos de usuários: comuns e lojistas. Ambos têm carteira com dinheiro e podem
realizar transferências entre eles, com algumas restrições para lojistas.

## Funcionalidades

- Cadastro de usuários comuns e lojistas com nome completo, CPF/CNPJ, e-mail e senha.
- Transferências de dinheiro entre usuários.
- Validação de saldo antes de realizar transferências.
- Consultas a um serviço autorizador externo antes de finalizar transferências.
- Notificações de recebimento de pagamento por e-mail ou SMS.


## Instalação

### Pré-requisitos

- PHP >= 8.1
- Composer
- MySQL

### Passo a passo

1. Clone o repositório:
    ```bash
    git clone https://github.com/usuario/banco-simplificado.git
    ```

2. Navegue até o diretório do projeto:
    ```bash
    cd banco-simplificado
    ```

3. Instale as dependências do Composer:
    ```bash
    composer install
    ```

4. Copie o arquivo `.env.example` para `.env`:
    ```bash
    cp .env.example .env
    ```

5. Gere a chave da aplicação:
    ```bash
    php artisan key:generate
    ```

6. Configure o arquivo `.env` com suas credenciais de banco de dados.

7. Execute as migrações para criar as tabelas no banco de dados:
    ```bash
    php artisan migrate
    ```

8. (Opcional) Popule o banco de dados com dados fictícios:
    ```bash
    php artisan db:seed
    ```

9. Inicie o servidor de desenvolvimento:
    ```bash
    php artisan serve
    ```

## Uso

### Endpoint de Transferência

Para realizar uma transferência entre dois usuários, utilize o endpoint:

**POST /transfer**
Content-Type: application/json

```json
{
  "value": 100.0,
  "payer": 4,
  "payee": 15
}
```

### Exemplo de Testes

Testes de funcionalidade estão localizados na pasta `tests/Feature`. Você pode rodar os testes com o comando:

```bash
php artisan test
```

## Licença

Este projeto está licenciado sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

```
