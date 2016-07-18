<?php
	require_once 'library.php';
	
	// Classe destinada à processar as requisições de uma tabela específica
	class TableRequestProcessor extends RequestProcessor
	{
		// Responde à requisição GET
		function GetRequest ()
		{
			// Cria query para selecionar todos os dados da tabela
			$query = $this->CreateSelectAllDataQuery ();
			
			// Executa query
			$result = $this->ExecuteQuery ($query, true);
			
			// Retorna os dados com sucesso
			$this->SuccessResponse ($result);
		}

		// Responde à requisição POST
		function PostRequest ()
		{
			// Armazena o conteúdo da requisição POST
			$postContent = Utils::GetPostContent ();
			
			// Cria query para inserir novo registro na tabela
			$query = $this->CreateInsertIntoQuery ($postContent);
			
			// Executa a query
			$this->ExecuteQuery ($query);
			
			// Retorna com sucesso
			$this->SuccessResponse ();
		}
		
		// Responde à requisição PUT
		function PutRequest ()
		{
			// Responde com erro caso o parâmetro id não tenha sido definido
			if (!isset ($_GET[PARAM_ID]) || $_GET[PARAM_ID] == '')
				$this->ErrorResponse (ErrorMessage::UNDEFINED_ID);
			
			// Pega o conteúdo da requisição POST
			$postContent = Utils::GetPostContent ();
			
			// Cria query para atualizar registro
			$query = $this->CreateUpdateTableQuery ($postContent);
			
			// Execute query
			$this->ExecuteQuery ($query);
			
			// Retorna com sucesso
			$this->SuccessResponse ();
		}
		
		// Responde à requisição DELETE
		function DeleteRequest ()
		{
			// Responde com erro caso o parâmetro id não tenha sido definido
			if (!isset ($_GET[PARAM_ID]) || $_GET[PARAM_ID] == '')
				$this->ErrorResponse (ErrorMessage::UNDEFINED_ID);
			
			// Cria query para remover registro
			$query = "DELETE FROM " . TABLE_NAME . " WHERE id = " . $_GET[PARAM_ID];
			
			// Executa query
			$this->ExecuteQuery ($query);
			
			// Retorna com sucesso
			$this->SuccessResponse ();
		}
		
		// Cria query para selecionar todos os dados da tabela
		function CreateSelectAllDataQuery ()
		{
			// Inicia construção da query
			$query = "SELECT * FROM " . TABLE_NAME;
			
			// Se id tiver sido inserido com parâmetro, insere uma cláusura WHERE
			if (isset ($_GET[PARAM_ID]))
				$query .= " WHERE id = " . $_GET[PARAM_ID];
			
			return $query;
		}
		
		// Cria query para inserir novo registro na tabela
		function CreateInsertIntoQuery ($postContent)
		{
			// Converte o conteúdo em array
			$postArray = get_object_vars ($postContent);
			
			// Armazena as chaves da array (nome dos campos)
			$keys = array_keys ($postArray);
			
			// Armazena os valores da array (valores dos campos)
			$values = array_values ($postArray);
			
			// Inicia construção da query
			$query = "INSERT INTO " . TABLE_NAME . " (";
			
			// Para cada chave (nome do campo)
			for ($i = 0; $i < count ($keys); $i++)
			{
				// Adiciona nome do campo
				$query .= $keys[$i];
				
				// Adiciona finalizador de valor
				$query .= $this->AddValueFinisher ($keys, $i, ',', ')');
			}
			
			// Inicia inserção de valores
			$query .= "VALUES (";
			
			// Gera uma array que informa em quais campos se deve usar aspas ao inserir seu valor na query
			$stringRows = $this->GetStringRows ();
			
			// Para cada valor
			for ($i = 0; $i < count ($values); $i++)
			{
				// Adiciona aspas, caso seja um tipo de campo que necessite
				$query .= $this->AddQuoteIfNecessary ($stringRows, $i);
				
				// Adiciona valor
				$query .= $values[$i];
				
				// Adiciona aspas, caso seja um tipo de campo que necessite
				$query .= $this->AddQuoteIfNecessary ($stringRows, $i);
				
				// Adiciona finalizador de valor
				$query .= $this->AddValueFinisher ($keys, $i, ',', ')');
			}
			
			return $query;
		}
		
		function CreateUpdateTableQuery ($postContent)
		{
			// Converte o conteúdo em array
			$postArray = get_object_vars ($postContent);
			
			// Armazena as chaves da array (nome dos campos)
			$keys = array_keys ($postArray);
			
			// Armazena os valores da array (valores dos campos)
			$values = array_values ($postArray);
			
			// Gera uma array que informa em quais campos se deve usar aspas ao inserir seu valor na query
			$stringRows = $this->GetStringRows ();
			
			// Inicia construção da query
			$query = "UPDATE " . TABLE_NAME . " SET ";
			
			// Para cada chave (nome do campo)
			for ($i = 0; $i < count ($keys); $i++)
			{
				// Insere o nome do campo e =
				$query .= $keys[$i] . " = ";
				
				// Adiciona aspas, caso seja um tipo de campo que necessite
				$query .= $this->AddQuoteIfNecessary ($stringRows, $i);
				
				// Adiciona o valor
				$query .= $values[$i];
				 
				// Adiciona aspas, caso seja um tipo de campo que necessite
				$query .= $this->AddQuoteIfNecessary ($stringRows, $i);
				
				// Adiciona finalizador de valor
				$query .= $this->AddValueFinisher ($keys, $i, ',', '');
			}
			
			// Adiciona cláusula WHERE
			$query .= " WHERE id=" . $_GET[PARAM_ID];
			
			return $query;
		}
		
		// Caso seja o último campo, fecha parênteses. Caso contrário, insere vírgula.
		function AddValueFinisher ($array, $index, $valueFinisher, $sessionFinisher)
		{
			if ($index < count ($array) - 1)
				return $valueFinisher;
			else
				return $sessionFinisher;
		}
		
		// Adiciona aspas, caso seja um tipo de campo que necessite
		function AddQuoteIfNecessary ($stringRows, $index)
		{
			if ($stringRows[$index + 1])
				return "'";
			
			return '';
		}
		
		// Retorna uma array indicando onde se deve usar aspas
		function GetStringRows ()
		{
			// Cria query para descrever tabela
			$query = "DESCRIBE " . TABLE_NAME;

			// Executa query
			$result = $this->ExecuteQuery ($query);
			
			// Cria uma array com true (onde se deve usar aspas) e false (onde não se deve usar)
			$stringRows = array();
			while ($row = mysqli_fetch_assoc ($result))
			{
				$type = $row["Type"];
				$isString = strpos ($type, 'char') !== false ||
							strpos ($type, 'binary') !== false ||
							strpos ($type, 'blob') !== false ||
							strpos ($type, 'text') !== false ||
							strpos ($type, 'enum') !== false ||
							strpos ($type, 'set') !== false;
				
				array_push ($stringRows, $isString);
			}
			
			return $stringRows;
		}
	}
	
	// Cria e executa o processador de requisições
	$processor = new TableRequestProcessor ();
	$processor->Execute ();
?>