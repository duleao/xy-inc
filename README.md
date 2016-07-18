Para executar, é necessário:
1. Iniciar um servidor PHP na raiz do projeto
2. Iniciar um servidor MySQL
3. Criar uma base de dados chamada 'baas'
4. Abrir o arquivo library.php e editar os dados para acesso ao banco de dados em seu cabeçalho (host, username e password)

Existem 2 tipos de endpoints no projeto, "index" e "tabela".
Em "index" é possível requisitar, criar e deletar tabelas.
Em "tabela" é possível requisitar, criar, alterar e remover dados de uma tabela específica. Ela é acessada adicionando seu respectivo nome no caminho da tabela.
Por exemplo, caso você tenha criado uma tabela chamada "clientes", deve-se fazer requisições em raiz/clientes.php/.

Obs.: todas as requisições retornam com o atributo "status", podendo ter como valor "ok" ou "error". Caso seja "error", o atributo "error_message"
também é adicionado, indicando o motivo do erro.

-----------------------------------------

INDEX

GET
Retorna o nome de todas as tabelas no seguinte formato:

{
	"status":"ok",
	"data":
	[
		{"name":"nome_da_tabela_1"},
		{"name":"nome_da_tabela_2"},
		{"name":"nome_da_tabela_3"}
	]
}

GET /?rowsInfo=true
Retorna o nome de todas as tabelas, bem como informações de todos seus campos no seguinte formato:

{
	"status":"ok",
	"data":
	[
		{
			"name":"nome_da_tabela",
			"rows":
			[
				{"name":"nome_do_campo_1",
				"type":"tipo_do_campo_1",
				"is_nullable":"yes / no"},
				
				{"name":"nome_do_campo_2",
				"type":"tipo_do_campo_2",
				"is_nullable":"yes / no"},
				
				{"name":"nome_do_campo_3",
				"type":"tipo_do_campo_3",
				"is_nullable":"yes / no"},
			]
		}
	]
}

POST
Cria uma nova tabela. Deve ser enviado no seguinte formato:

{
	"name":"nome_da_tabela",
	"rows":
	[
		{"name":"nome_do_campo_1",
		"type":"tipo_do_campo_1",
		"is_nullable":"yes / no"},
		
		{"name":"nome_do_campo_2",
		"type":"tipo_do_campo_2",
		"is_nullable":"yes / no"},
		
		{"name":"nome_do_campo_3",
		"type":"tipo_do_campo_3",
		"is_nullable":"yes / no"},
	]
}

DELETE /?name=nome_da_tabela
Deleta uma tabela

-----------------------------------------

TABELA

GET
Retorna todos os registros da tabela no seguinte formato:

{
	"status":"ok",
	"data":
	[
		{
			"nome_do_campo_1":"valor_do_campo_1",
			"nome_do_campo_2":"valor_do_campo_2",
			"nome_do_campo_3":"valor_do_campo_3",
		}
		
		{
			"nome_do_campo_1":"valor_do_campo_1",
			"nome_do_campo_2":"valor_do_campo_2",
			"nome_do_campo_3":"valor_do_campo_3",
		}
	]
}

GET /?id=id_do_registro
Retorna um registro específico da tabela no mesmo formato acima

POST
Insere um novo registro na tabela. Deve ser enviado no seguinte formato:

{
	"nome_do_campo_1": "valor_do_campo_1",
	"nome_do_campo_2": "valor_do_campo_2",
	"nome_do_campo_3": "valor_do_campo_3"
}

PUT /?id=id_do_registro
Edita um registro da tabela. Deve ser enviado no mesmo formato do POST

DELETE /?id=id_do_registro
Deleta um registro da tabela.
