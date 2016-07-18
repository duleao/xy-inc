<?php
	require_once 'library.php';
	
	// Classe destinada à processar as requisições de manipulação de tabelas
	class TablesManagerRequestProcessor extends RequestProcessor
	{
		// Responde à requisição GET
		function GetRequest ()
		{
			// Retorna um erro caso o parâmetro rowsInfo seja inválido
			if (isset ($_GET[PARAM_ROWS_INFO]) && $_GET[PARAM_ROWS_INFO] != PARAM_TRUE && $_GET[PARAM_ROWS_INFO] != PARAM_FALSE)
				$this->ErrorResponse (ErrorMessage::INVALID_ROWSINFO);
			
			// Cria query para requisitar os nomes de todas as tabelas do banco de dados
			$query = $this->CreateSelectTableNamesQuery ();
			
			// Executa a query e converte em array
			$result = $this->ExecuteQuery ($query, true);
			
			// Caso o parâmetro rowsInfo não tenha sido definido ou seja false, retorna apenas o nome das tabelas
			if (!isset ($_GET[PARAM_ROWS_INFO]) || $_GET[PARAM_ROWS_INFO] == PARAM_FALSE)
				$this->SuccessResponse ($result);

			// rowsInfo é igual a true e também é necessário retornar as informações dos campos de todas as tabelas
			for ($i = 0; $i < count ($result); $i++)
			{
				// Cria a query para requisitar as informações dos campos da tabela (nome, tipo de dados e se é nulável)
				$query = $this->CreateSelectTableInfoQuery ($result[$i]['name']);
				
				// Executa a query e converte em array
				$rowsInfo = $this->ExecuteQuery ($query, true);
				
				// Converte os tipos de dados do formato da query para formato estipulado no post
				for ($j = 0; $j < count ($rowsInfo); $j++)
					$rowsInfo[$j]['type'] = Utils::DBTypeToPostType ($rowsInfo[$j]['type']);
				
				// Adiciona as informações dos campos à array final
				$result[$i]['rows'] = $rowsInfo;
			}
			
			// Retorna todos os dados com sucesso
			$this->SuccessResponse ($result);
		}
		
		// Responde à requisição POST
		function PostRequest ()
		{
			// Pega os dados da requisição POST
			$postContent = Utils::GetPostContent ();
			
			// Cria query para gerar nova tabela
			$query = $this->CreateInsertTableQuery ($postContent);

			// Executa a query
			$this->ExecuteQuery ($query);
			
			// Gera uma cópia do arquivo PHP para responder às requisições para essa nova tabela
			Utils::CreatePHPFile ($postContent);
			
			// Retorna com sucesso
			$this->SuccessResponse ();
		}
		
		// Responde à requisição DELETE
		function DeleteRequest ()
		{
			if (!isset ($_GET[PARAM_NAME]) || $_GET[PARAM_NAME] == '')
				$this->ErrorResponse (ErrorMessage::UNDEFINED_NAME);
			
			// Cria query para remover tabela
			$query = "DROP TABLE " . $_GET[PARAM_NAME];
			
			// Executa query
			$this->ExecuteQuery ($query);
			
			// Remove arquivo PHP para não responder mais à requisições dessa tabela
			Utils::RemovePHPFile ();
			
			// Retorna com sucesso
			$this->SuccessResponse ();
		}
		
		// Cria query para requisitar os nomes de todas as tabelas do banco de dados
		function CreateSelectTableNamesQuery ()
		{
			return "SELECT TABLE_NAME as name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DBNAME . "'";
		}
		
		// Cria a query para requisitar as informações dos campos da tabela (nome, tipo de dados e se é nulável)
		function CreateSelectTableInfoQuery ($tableName)
		{
			return "SELECT COLUMN_NAME as name, COLUMN_TYPE as type, IS_NULLABLE as is_nullable
					FROM INFORMATION_SCHEMA.COLUMNS
					WHERE TABLE_SCHEMA = 'baas'
					AND TABLE_NAME = '" . $tableName . "'";
		}

		// Cria query para gerar nova tabela
		function CreateInsertTableQuery ($postContent)
		{
			// Inicia construção da query
			$query = "CREATE TABLE ";
			
			// Adiciona o nome da tabela
			$query .= $postContent->name;
			
			// Abre parênteses
			$query .= "(";
			
			// Adiciona a primary key
			$query .= "id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,";

			// Para cada coluna da tabela...
			for ($i = 0; $i < count ($postContent->rows); $i++)
			{
				$row = $postContent->rows[$i];
				
				// Adiciona o nome da coluna
				$query .= $row->name;
				
				// Adiciona o tipo de dados da coluna
				$query .= ' ' . $this->PostTypeToDBType ($row->type) . ' ';
				
				// Adiciona NOT NULL caso não seja nulável
				if (!$row->is_nullable)
					$query .= " NOT NULL ";
				
				// Insere uma vírgula caso não seja a última coluna
				if ($i < count ($postContent->rows) - 1)
					$query .= ",";
			}
			
			// Fecha parênteses
			$query .= ")";
			
			return $query;
		}
	}
	
	// Cria e executa o processador de requisições
	$processor = new TablesManagerRequestProcessor ();
	$processor->Execute ();
?>