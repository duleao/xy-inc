<?php
	// Constantes (banco de dados)
	const HOST = 'localhost';
	const USERNAME = 'root';
	const PASSWORD = '';
	const DBNAME = 'baas';
	
	// Constantes (parâmetros GET)
	const PARAM_TRUE = 'true';
	const PARAM_FALSE = 'false';
	
	const PARAM_ROWS_INFO = 'rowsInfo';
	const PARAM_NAME = 'name';
	const PARAM_ID = 'id';
	
	// Classe que armazena mensagens de erro
	class ErrorMessage
	{
		const REQUEST_METHOD_NOT_IMPLEMENTED = 'Metodo nao implementado';
		const DB_CONNECTION = 'Nao foi possivel conectar ao banco de dados';
		const QUERY_ERROR = 'Erro ao manipular o banco de dados';
		const UNDEFINED_TYPE = 'Tipo de dados desconhecido: ';
		const UNDEFINED_ID = 'Parametro id indefinido';
		const UNDEFINED_NAME = 'Parametro name indefinido';
		const INVALID_ROWSINFO = 'Parametro rowsInfo invalido. Utilize apenas true ou false.';
	}
	
	// Classe que representa o processador de requests
	abstract class RequestProcessor
	{
		protected $dbConnection;
		
		function Execute ()
		{
			// Cria uma nova conexão com o banco de dados
			$this->dbConnection = new DBConnection();

			if (!$this->dbConnection->Connect ())
				$this->ErrorResponse (ErrorMessage::DB_CONNECTION);
			
			// Verifica o método da requisição e chama o método que o trata
			if ($_SERVER['REQUEST_METHOD'] == 'GET')
				$this->GetRequest ();
			else if ($_SERVER['REQUEST_METHOD'] == 'POST')
				$this->PostRequest ();
			else if ($_SERVER['REQUEST_METHOD'] == 'PUT')
				$this->PutRequest ();
			else if ($_SERVER['REQUEST_METHOD'] == 'DELETE')
				$this->DeleteRequest ();
			else
				$this->ErrorResponse (ErrorMessage::REQUEST_METHOD_NOT_IMPLEMENTED);
			
			// Fecha conexão com o banco de dados
			$this->dbConnection->Disconnect();
		}
		
		// Chamado em requisições GET
		protected function GetRequest () { }
		
		// Chamado em requisições POST
		protected function PostRequest () { }
		
		// Chamado em requisições PUT
		protected function PutRequest () { }
		
		// Chamado em requisições DELETE
		protected function DeleteRequest () { }
		
		// Retorna 'status: ok' para o cliente e os dados (não obrigatório)
		function SuccessResponse ($data = null)
		{
			$return = array();
			$return["status"] = "ok";
			
			if (isset ($data))
				$return["data"] = $data;
			
			echo (json_encode ($return));
			
			$this->dbConnection->Disconnect ();
			
			exit;
		}
		
		// Retorna 'status: error' para o cliente e a mensagem de erro
		function ErrorResponse ($message)
		{
			$return = array();
			$return["status"] = "error";
			$return["error_message"] = $message;
			
			echo (json_encode ($return));
			
			$this->dbConnection->Disconnect ();
			
			exit;
		}
		
		// Executa query e converte o resultado para array, caso necessário
		function ExecuteQuery ($query, $convertResultToArray = false)
		{
			$result = $this->dbConnection->ExecuteQuery ($query, $convertResultToArray);

			if ($result === false)
				$this->ErrorResponse (ErrorMessage::QUERY_ERROR);
			
			return $result;
		}
		
		// Converte o tipo de dados estipulado no JSON para o da query do banco de dados
		function PostTypeToDBType ($postType)
		{
			$result = Utils::PostTypeToDBType ($postType);
			
			if ($result === false)
				$this->ErrorResponse (ErrorMessage::UNDEFINED_TYPE . $postType);
			
			return $result;
		}
	}

	// Classe que manipula o banco de dados (conexão e queries)
	class DBConnection
	{
		private $connection;
		
		// Cria uma nova conexão com o banco de dados
		function Connect ()
		{
			$this->connection = mysqli_connect (HOST, USERNAME, PASSWORD, DBNAME);
			return $this->connection != false;
		}
		
		// Fecha a conexão com o banco de dados
		function Disconnect ()
		{
			if (isset ($this->connection))
				mysqli_close ($this->connection);
		}
		
		// Executa a query
		function ExecuteQuery ($query, $convertResultToArray)
		{
			$result = mysqli_query ($this->connection, $query);
			
			if (!$result)
				return false;
			
			// Converte o resultado para array, caso necessário
			if ($convertResultToArray)
				return Utils::QueryResultToArray ($result);
			else
				return $result;
		}
	}
	
	// Classe com métodos utilitários diversos utilizados no projeto
	class Utils
	{
		// Converte o resultado de uma query em um array associativo (chave => valor)
		static function QueryResultToArray ($result)
		{
			$array = array();
			
			while ($row = mysqli_fetch_assoc ($result))
				array_push ($array, $row);
			
			return $array;
		}
		
		// Retorna o conteúdo da requisição POST em array
		static function GetPostContent ()
		{
			$content = file_get_contents ('php://input');
			return json_decode ($content);
		}
		
		// Converte o tipo de dados estipulado no JSON para o da query do banco de dados
		static function PostTypeToDBType ($postType)
		{
			switch ($postType)
			{
				case 'varchar':
					return "VARCHAR(30)";
					
				case 'int':
					return "INT (6)";
					
				default:
					return false;
			}
		}
		
		// Converte o tipo de dados da query do banco de dados para o estipulado no JSON
		static function DBTypeToPostType ($dbType)
		{
			if (strpos ($dbType, 'varchar') === 0)
				return 'varchar';
			
			if (strpos ($dbType, 'int') === 0)
				return 'int';

			return false;
		}
		
		// Gera uma cópia do arquivo PHP para responder à novas requisições
		static function CreatePHPFile ($postContent)
		{
			// Copia arquivo modelo
			$model = file_get_contents ("model.txt", "r");
		
			// Substitui #TABLE_NAME pelo nome da tabela
			$wordsToReplace = array ("#TABLE_NAME");
			$wordsToInsert = array ($postContent->name);
			$phpContent = str_replace ($wordsToReplace, $wordsToInsert, $model);
			
			// Salva arquivo com o nome da tabela e extensão php
			file_put_contents ($postContent->name . ".php", $phpContent);
		}
		
		// Remove o arquivo PHP referente à requisição que está sendo feita
		static function RemovePHPFile ()
		{
			unlink ($_GET['name'] . '.php');
		}
	}
?>