Para executar, é necessário:

1. Iniciar um servidor PHP na raiz do projeto
2. Iniciar um servidor MySQL
3. Criar uma base de dados chamada 'baas'
4. Abrir o arquivo library.php e editar os dados para acesso ao banco de dados em seu cabeçalho (host, username e password)

Existem 2 tipos de endpoints no projeto, "index" e "tabela".

* Em "index" é possível requisitar, criar e deletar tabelas.
* Em "tabela" é possível requisitar, criar, alterar e remover dados de uma tabela específica. Ela é acessada adicionando seu respectivo nome no caminho da tabela.
Por exemplo, caso você tenha criado uma tabela chamada "clientes", deve-se fazer requisições em raiz/clientes.php/.

Obs.: todas as requisições retornam com o atributo "status", podendo ter como valor "ok" ou "error". Caso seja "error", o atributo "error_message"
também é adicionado, indicando o motivo do erro.

-----------------------------------------

**INDEX**

**GET**<br>
Retorna o nome de todas as tabelas no seguinte formato:

`{`<br>
`"status":"ok",`<br>
`"data":`<br>
`[`<br>
`{"name":"nome_da_tabela_1"},`<br>
`{"name":"nome_da_tabela_2"},`<br>
`{"name":"nome_da_tabela_3"}`<br>
`]`<br>
`}`<br>

**GET /?rowsInfo=true**<br>
Retorna o nome de todas as tabelas, bem como informações de todos seus campos no seguinte formato:

`{`<br>
`"status":"ok",`<br>
`"data":`<br>
`[`<br>
`{"name":"nome_da_tabela",`<br>
`"rows":`<br>
`[`<br>
`{"name":"nome_do_campo_1","type":"tipo_do_campo_1","is_nullable":"yes / no"},`<br>
`{"name":"nome_do_campo_2","type":"tipo_do_campo_2","is_nullable":"yes / no"},`<br>
`{"name":"nome_do_campo_3","type":"tipo_do_campo_3","is_nullable":"yes / no"},`<br>
`]}]}`

**POST**<br>
Cria uma nova tabela. Deve ser enviado no seguinte formato:

`{`<br>
`"name":"nome_da_tabela",`<br>
`"rows":`<br>
`[`<br>
`{"name":"nome_do_campo_1","type":"tipo_do_campo_1","is_nullable":"yes / no"},`<br>
`{"name":"nome_do_campo_2","type":"tipo_do_campo_2","is_nullable":"yes / no"},`<br>
`{"name":"nome_do_campo_3","type":"tipo_do_campo_3","is_nullable":"yes / no"},`<br>
`]}`<br>

**DELETE /?name=nome_da_tabela**<br>
Deleta uma tabela

-----------------------------------------

**TABELA**

**GET**<br>
Retorna todos os registros da tabela no seguinte formato:

`{`<br>
`"status":"ok",`<br>
`"data":`<br>
`[`<br>
`{"nome_do_campo_1":"valor_do_campo_1",`<br>
`"nome_do_campo_2":"valor_do_campo_2",`<br>
`"nome_do_campo_3":"valor_do_campo_3",`<br>
`}`<br>
`{`<br>
`"nome_do_campo_1":"valor_do_campo_1",`<br>
`"nome_do_campo_2":"valor_do_campo_2",`<br>
`"nome_do_campo_3":"valor_do_campo_3",`<br>
`}]}`<br>

**GET /?id=id_do_registro**<br>
Retorna um registro específico da tabela no mesmo formato acima

**POST**<br>
Insere um novo registro na tabela. Deve ser enviado no seguinte formato:

`{`<br>
`"nome_do_campo_1": "valor_do_campo_1",`<br>
`"nome_do_campo_2": "valor_do_campo_2",`<br>
`"nome_do_campo_3": "valor_do_campo_3"}`<br>

**PUT /?id=id_do_registro**<br>
Edita um registro da tabela. Deve ser enviado no mesmo formato do POST

**DELETE /?id=id_do_registro**<br>
Deleta um registro da tabela.
