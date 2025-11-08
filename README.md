## Doutorie API

Aplicação Laravel responsável pelo cadastro de livros, seus índices e subíndices, além da importação/exportação da estrutura em XML. Este resumo reúne os principais fluxos, rotas e processos necessários para colocar o projeto em funcionamento.

### Stack

-   PHP 8.2+
-   Laravel 12
-   MySQL 8.x

### Setup Rápido

1. **Instalar dependências**
    ```bash
    composer install
    ```
2. **Configurar variáveis de ambiente**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    Ajuste conexões de banco, filas e storage.
3. **Executar migrações**
    ```bash
    php artisan migrate
    ```
4. **Configurar storage público**
    ```bash
    php artisan storage:link
    ```
5. **Rodar servidor e filas**
    ```bash
    php artisan serve         # API
    php artisan queue:work    # processamento de jobs
    ```
6. **Rodar as Seeders**
    ```bash
    php artisan db:seed
    ```

### Autenticação

-   `POST /api/v1/auth/token`  
    Recebe credenciais e retorna token Sanctum. Envie o token no header `Authorization: Bearer {token}` para as rotas protegidas.

### Rotas Principais

-   `GET /api/v1/livros`  
    Lista livros com filtros opcionais `?titulo=` e `?titulo_do_indice=`. Retorna `titulo`, `usuario_publicador` e a árvore completa de índices/subíndices.
-   `POST /api/v1/livros`  
    Cria um livro com índices informados em JSON. Requer campos `titulo`, `pagina` e permite recursão de `subindices`.
-   `POST /api/v1/livros/{livro}/importar-indices-xml`  
    Recebe um arquivo XML com a estrutura de índices e dispara o job `ProcessBookXML`.

### Estrutura JSON de Livro

```json
{
    "titulo": "Livro Exemplo",
    "usuario_publicador": {
        "id": 1,
        "name": "Editor"
    },
    "indices": [
        {
            "titulo": "Seção 1",
            "pagina": 1,
            "subindices": [
                {
                    "titulo": "Seção 1.1",
                    "pagina": 1,
                    "subindices": [
                        { "titulo": "Seção 1.1.1", "pagina": 1 },
                        { "titulo": "Seção 1.1.2", "pagina": 1 }
                    ]
                },
                { "titulo": "Seção 1.2", "pagina": 2 }
            ]
        },
        { "titulo": "Seção 2", "pagina": 2 },
        { "titulo": "Seção 3", "pagina": 3 }
    ]
}
```

### Importação de XML

**Formato esperado**

```xml
<indice>
    <item pagina="1" titulo="Seção 1">
        <item pagina="1" titulo="Seção 1.1">
            <item pagina="1" titulo="Seção 1.1.1" />
            <item pagina="1" titulo="Seção 1.1.2" />
        </item>
        <item pagina="2" titulo="Seção 1.2" />
    </item>
    <item pagina="2" titulo="Seção 2" />
    <item pagina="3" titulo="Seção 3" />
</indice>
```

**Fluxo**

1. Envie o XML via multipart/form-data para `POST /api/v1/livros/{id}/importar-indices-xml`.
2. O controller despacha `ProcessBookXML`, que:
    - Recarrega o livro e seus índices.
    - Percorre todos os níveis de subíndices.
    - Gera um XML formatado com `<item pagina="" titulo="">`.
    - Salva o resultado em `storage/app/public/books/book_{id}.xml`.
3. Os arquivos ficam acessíveis após executar `php artisan storage:link`.

### Exportação Gerada

Exemplo de saída produzida pelo job:

```xml
<?xml version="1.0"?>
<indice>
  <item pagina="1" titulo="Seção 1">
    <item pagina="1" titulo="Seção 1.1">
      <item pagina="1" titulo="Seção 1.1.1"/>
      <item pagina="1" titulo="Seção 1.1.2"/>
    </item>
    <item pagina="2" titulo="Seção 1.2"/>
  </item>
  <item pagina="2" titulo="Seção 2"/>
  <item pagina="3" titulo="Seção 3"/>
</indice>
```

### Testes

-   Utilize `php artisan test` para executar os testes automatizados (se existentes).
-   Recomenda-se criar testes de feature para importação/exportação conforme a necessidade do projeto.

### Dúvidas

Abra uma issue ou converse com o time responsável para esclarecer dúvidas sobre índices, estrutura do XML ou integração com o job de processamento.
