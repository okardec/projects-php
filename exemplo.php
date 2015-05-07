<?php
error_reporting(E_ALL);
ini_set('display_errors','1');
  
	require_once "youtubeapi3.php";
	 	 
	//recuperar os dados do vídeo especifico
	$id_video = "OBl4pp0Sfko";
	
	$api = new YoutubeAPI3('videos',$id_video,array('snippet','contentDetails','statistics','status'));
	if (!$api->isValidResponse()){
	    throw new Exception($api->getErrorDescription());
	}
	///para retornar os dados
	$json = $api->getJSON(); ///retorna o json conforme esta na API do Youtube
	///ou 
	$json = $api->fetchVideoData(); ///retorna um json personalizado mais limpo
	
	print_r($json);
   
?>
