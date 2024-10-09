
## Documentação da API

Aqui ficará toda a documentação da API

### Endpoint 1: LOGIN

**Método:** POST  
**URL:** `/user/login`

**Descrição:** Retorna as informações de login, como usuario e token criado para sessão.

**FormData:**

  ```json
    {
        "email": "seuemail@seuenail.com",
        "password": "SuaSenha12345"
    }
  ```

**Respostas:**

- **201 OK**

  ```json
  {
    "code": 201,
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9"
    },
    "message": "Usuario logado com sucesso",
    "status": "success"
    }
  ```

### Endpoint 2: Informações de usuario logado

**Método:** POST  
**URL:** `/user/first-access`

**Descrição:** Retorna as informações de login.

**Cabeçalho:** 
Header: Authorization <BEARER_TOKEN>

**Respostas:**

- **200 OK**

  ```json
  {
    "code": 200,
    "data": {
        "status": "success",
        "user": {
            "id": 1,
            "name": "SEUNOME",
            "last_name": "SEUSOBRENOME",
            "email": "seuemail@seuemail.com",
            "roles": "ROLE_ADMIN",
            "created_at": {
                "date": "2024-09-02 10:38:29.000000",
                "timezone_type": 3,
                "timezone": "UTC"
            }
        }
    },
    "message": "Requisição feita com Sucesso",
    "status": "success"
    }
  ```

### Endpoint 3: Cadastro de Usuario para primeiro acesso

**Método:** POST  
**URL:** `/user/first-access`

**Descrição:** Retorna se a requisição deu certo.

**FormData:**

  ```json
    {
        "name":"SEUNOME",
        "last_name":"SEUSOBRENOME",
        "email":"email@email.com.br",
        "password":"SuaSenha123456"
    }
  ```

**Respostas:**

- **200 OK**

  ```json
  {
        "code": 200,
        "data": [],
        "message": "Usuario cadastro com sucesso",
        "status": "success"
    }
  ```


### Endpoint 4: Cadastro de Usuario

**Método:** POST  
**URL:** `/user/store`

**Descrição:** Retorna se a requisição deu certo.

**FormData:**

  ```json
    {
        "name":"SEUNOME",
        "last_name":"SEUSOBRENOME",
        "email":"email@email.com.br",
        "password":"SuaSenha123456"
    }
  ```

**Respostas:**

- **200 OK**

  ```json
  {
        "code": 200,
        "data": [],
        "message": "Usuario cadastro com sucesso",
        "status": "success"
    }
  ```


### Endpoint 5: Listagem de Usuario

**Método:** GET  
**URL:** `/user/list`

**Descrição:** Retorna a lista de Usuario.

**Cabeçalho:** 
Header: Authorization <BEARER_TOKEN>

**Observação:** 
Valido para autorização ROLE_ADMIN

**Respostas:**

- **200 OK**

  ```json
  {
        "data": [
            {
                "id": 1,
                "name": "SEUNOME",
                "last_name": "SEUSOBRENOME",
                "email": "seuemail@seuemail.com",
                "roles": "ROLE_ADMIN",
                "created_at": {
                    "date": "2024-09-02 10:38:29.000000",
                    "timezone_type": 3,
                    "timezone": "UTC"
                }
            }
        ],
        "currentPage": 1,
        "totalPages": 1
    }
  ```


### Endpoint 6: Listagem de Empresas

**Método:** GET  
**URL:** `/company/list`

**Descrição:** Retorna a lista de empresas.

**Cabeçalho:** 
Header: Authorization <BEARER_TOKEN>

**Respostas:**

- **200 OK**

  ```json
    {
        "data": [
            {
                "id": 2,
                "fantasy_name": "Empresa de Teste",
                "company_name": "Empresa de Teste",
                "cnpj": "21301557000132",
                "opening_date": {
                    "date": "2024-05-10 00:00:00.000000",
                    "timezone_type": 3,
                    "timezone": "UTC"
                },
                "invoicing": "2500000",
                "phone_number": "11963857945",
                "created_at": {
                    "date": "2024-09-02 10:55:22.000000",
                    "timezone_type": 3,
                    "timezone": "UTC"
                }
            }
        ],
        "currentPage": 1,
        "totalPages": 1
    }
  ```


### Endpoint 7: Consulta de Empresas

**Método:** GET  
**URL:** `/company/show/:id`

**Descrição:** Retorna a empresa de acordo com id.

**Cabeçalho:** 
Header: Authorization <BEARER_TOKEN>

**Respostas:**

- **200 OK**

  ```json
    {
        "code": 200,
        "data": {
            "id": 2,
            "fantasy_name": "Empresa de Teste",
            "company_name": "Empresa de Teste",
            "cnpj": "21301557000132",
            "opening_date": {
                "date": "2024-05-10 00:00:00.000000",
                "timezone_type": 3,
                "timezone": "UTC"
            },
            "invoicing": "2500000",
            "phone_number": "11963857945",
            "created_at": {
                "date": "2024-09-02 10:55:22.000000",
                "timezone_type": 3,
                "timezone": "UTC"
            },
            "partners": []
        },
        "message": "Empresa encontrada com sucesso",
        "status": "success"
    }
  ```


### Endpoint 8: Cadastro de Empresas

**Método:** POST  
**URL:** `/company/store`

**Descrição:** Cadastra empresa.

**Cabeçalho:** 
Header: Authorization <BEARER_TOKEN>

**FormData:**

  ```json
    {
        "fantasy_name":"Empreendimentos Correia 2",
        "company_name":"Correia Arruda Empreendimentos LTDA",
        "cnpj":"86543151000101",
        "opening_date":"2019-04-28",
        "phone_number":"11963857945",
        "invoicing":25.00
    }
  ```


**Respostas:**

- **200 OK**

  ```json
  {
        "code": 201,
        "data": [],
        "message": "Empresa cadastrada com sucesso",
        "status": "success"
    }
  ```
    


### Endpoint 9: Edição de Empresas

**Método:** PUT  
**URL:** `/company/update/:id`

**Descrição:** Edita empresa de acordo com id. 

**Cabeçalho:** 
Header: Authorization <BEARER_TOKEN>

**FormData:**

  ```json
    {
      "fantasy_name":"Empreendimentos Correia 2",
      "company_name":"Correia Arruda Empreendimentos LTDA",
      "cnpj":"86543151000101",
      "opening_date":"2019-04-28",
      "phone_number":"11963857945",
      "invoicing":25.00
    }
  ```


**Respostas:**

- **200 OK**

  ```json
    {
      "code": 200,
      "data": [],
      "message": "Empresa editada com sucesso",
      "status": "success"
    }
  ```


### Endpoint 10: Remoção de Empresas

**Método:** DELETE  
**URL:** `/company/delete/:id`

**Descrição:** Remover empresa de acordo com id. 

**Cabeçalho:** 
Header: Authorization <BEARER_TOKEN>

**Observação:** 
Valido para autorização ROLE_ADMIN

**Respostas:**

- **200 OK**

  ```json
  {
      "code": 200,
      "data": [],
      "message": "Empresa removida com sucesso",
      "status": "success"
  }
  ```


### Endpoint 11: Lista de Socios

**Método:** GET  
**URL:** `/partner/list`

**Descrição:** Retorna uma lista de sócios cadastrados no sistema 

**Cabeçalho:** 
Header: Authorization <BEARER_TOKEN>

**Respostas:**

- **200 OK**

  ```json
    {
        "data": [
            {
                "id": 2,
                "name": "SEUNOME",
                "last_name": "SEUSOBRENOME",
                "email": "email@email.com",
                "cpf": "25698745236",
                "phone_number": "11970704040",
                "created_at": {
                    "date": "2024-09-02 11:00:08.000000",
                    "timezone_type": 3,
                    "timezone": "UTC"
                }
            }
        ],
        "currentPage": 1,
        "totalPages": 1
      }
    ```


### Endpoint 12: Consulta de sócio

**Método:** GET  
**URL:** `/partner/show/:id`

**Descrição:** Retorna um socio de acordo com id

**Cabeçalho:** 
Header: Authorization <BEARER_TOKEN>

**Respostas:**

- **200 OK**

  ```json
    {
        "code": 200,
        "data": {
            "id": 2,
            "name": "SEUNOME",
            "last_name": "SEUSOBRENOME",
            "email": "email@email.com",
            "cpf": "25698745236",
            "phone_number": "11970704040",
            "created_at": {
                "date": "2024-09-02 11:00:08.000000",
                "timezone_type": 3,
                "timezone": "UTC"
            },
            "companies": [
                {
                    "fantasy_name": "Empreendimentos Correia 2",
                    "company_name": "Correia Arruda Empreendimentos LTDA",
                    "cnpj": "86543151000101",
                    "opening_date": {
                        "date": "2019-04-28 00:00:00.000000",
                        "timezone_type": 3,
                        "timezone": "UTC"
                    },
                    "phone_number": "11963857945",
                    "invoicing": "25",
                    "participation": "25"
                }
            ]
        },
        "message": "Socio encontrado com sucesso",
        "status": "success"
    }
  ```


### Endpoint 13: Remoção de Sócio

**Método:** DELETE  
**URL:** `/company/delete/:id`

**Descrição:** Remover socio de acordo com id. 

**Cabeçalho:** 
Header: Authorization <BEARER_TOKEN>

**Observação:** 
Valido para autorização ROLE_ADMIN

**Respostas:**

- **200 OK**

  ```json
    {
        "code": 200,
        "data": [],
        "message": "Sócio removido com sucesso",
        "status": "success"
    }
  ```




### Endpoint 14: Cadastro de Sócio

**Método:** POST  
**URL:** `/partner/store`

**Descrição:** Cadastra sócio.

**Cabeçalho:** 
Header: Authorization <BEARER_TOKEN>

**FormData:**

  ```json
    {
        "name":"SEUNOME",
        "last_name":"SEUSOBRENOME",
        "email":"email@email.com.br",
        "cpf":"84856232547",
        "phone_number":"11970704040"
    }
  ```


**Respostas:**

- **200 OK**

  ```json
    {
      "code": 201,
      "data": [],
      "message": "Sócio cadastrada com sucesso",
      "status": "success"
    }
  ```
    


### Endpoint 15: Edição de Sócio

**Método:** PUT  
**URL:** `/partner/update/:id`

**Descrição:** Edita sócio de acordo com id. 

**Cabeçalho:** 
Header: Authorization <BEARER_TOKEN>

**FormData:**

  ```json
    {
        "name":"SEUNOME",
        "last_name":"SEUSOBRENOME",
        "email":"email@email.com.br",
        "cpf":"84856232547",
        "phone_number":"11970704040"
    }
  ```


**Respostas:**

- **200 OK**

  ```json
    {
      "code": 201,
      "data": [],
      "message": "Sócio cadastrada com sucesso",
      "status": "success"
    }
  ```



### Endpoint 16: Cadastro de Sóciedade por CPF 

**Método:** POST  
**URL:** `/partner_company/store-by-cpf`

**Descrição:** Cadastra sócio.

**Cabeçalho:** 
Header: Authorization <BEARER_TOKEN>

**FormData:**

  ```json
    {
        "company_id":1,
        "cpf":"84856232547",
        "participation":25
    }
  ```


**Respostas:**

- **200 OK**

  ```json
    {
        "code": 201,
        "data": [],
        "message": "Sociedade cadastrada com sucesso",
        "status": "success"
    }
  ```
    

### Endpoint 17: Cadastro de Sóciedade por CNPJ 

**Método:** POST  
**URL:** `/partner_company/store-by-cnpj`

**Descrição:** Cadastra sócio.

**Cabeçalho:** 
Header: Authorization <BEARER_TOKEN>

**FormData:**

  ```json
    {
        "partner_id":1,
        "cnpj":"84856232547",
        "participation":25
    }
  ```


**Respostas:**

- **200 OK**

  ```json
    {
          "code": 201,
          "data": [],
          "message": "Sociedade cadastrada com sucesso",
          "status": "success"
      }
  ```
    

### Endpoint 18: Cadastro de Sóciedade 

**Método:** POST  
**URL:** `/partner/store`

**Descrição:** Cadastra sócio.

**Cabeçalho:** 
Header: Authorization <BEARER_TOKEN>

**FormData:**

  ```json
    {
        "partner_id":1,
        "company_id":2,
        "participation":25
    }
  ```


**Respostas:**

- **200 OK**

  ```json
    {
      "code": 201,
      "data": [],
      "message": "Sociedade cadastrada com sucesso",
      "status": "success"
    }
  ```
    


### Endpoint 19: Edição de Sóciedade 

**Método:** PUT  
**URL:** `/partner_company/update`

**Descrição:** Edita Sociedade.

**Cabeçalho:** 
Header: Authorization <BEARER_TOKEN>

**FormData:**

  ```json
    {
        "partner_id":1,
        "company_id":2,
        "participation":25
    }
  ```


**Respostas:**

- **200 OK**

  ```json
    {
      "code": 201,
      "data": [],
      "message": "Sociedade editada com sucesso",
      "status": "success"
    }
  ```
    

### Endpoint 20: Remoção de Sociedade

**Método:** GET  
**URL:** `/partner_company/delete/:id`

**Descrição:** Remover sociedade de acordo com id. 

**Cabeçalho:** 
Header: Authorization <BEARER_TOKEN>

**Respostas:**

- **200 OK**

  ```json
    {
        "code": 200,
        "data": [],
        "message": "Sociedade removida com sucesso",
        "status": "success"
    }
  ```

