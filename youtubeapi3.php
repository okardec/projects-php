<?php
/**
 * API do Youtube v3
 * por Allan Kardec  (07/05/2015)
 */
class YoutubeAPI3 implements iAPI {


	/**
	 * A API KEY agora deve ser criada no console de desenvolvedor do Google
	 * https://console.developers.google.com/
	 * Primeiro cria-se o projeto, e confirma se a permissão do usuário da conta esta OK
	 * vá em "APIs e Autenticações > APIs" localize a do Youtube e ative
	 * apos va em "Credenciais" e crie uma "chave de acesso público"
	 * confirme que ela esteja com a opção "Qualquer IP permitido" ativo 
	 * o valor retornado e a API_KEY	 *
	 */
	const API_KEY = "xxxxxxxxxxxxxxxxxxx";
	
	//url para requisição montada >> https://www.googleapis.com/youtube/v3/[METHOD]?key=[API_KEY]&id=[ID_VIDEO]&part=[PARAMS] 	
	const PATH = "https://www.googleapis.com/youtube/v3/[METHOD]?key=[API_KEY]"; 
	
	/**
	 * Indica se a url foi carregada com sucesso
	 *
	 * @var bool
	 */
	protected $lodaed;
	
	/**
	 * Objeto formado pelo JSON retornado pela API.
	 *
	 * @var JSON
	 */
	protected $json;
	
	/**
	 * Metodo construtor
	 *
	 * @param string[optional] $method >> método que deve carregar, ex: videos 
	 * @param string[optional] $idVideo >> id do vídeo a ser carregado
	 * @param array[optional] $param >> parametros necessarios para recuperar os dados do vídeo, ex: snippet,contentDetails,statistics,status
	 */
	public function __construct($method=null,$idVideo=null,array $param=null){
		$this->setLodead(false);
		if (isset($method)){
			$this->load($method,$idVideo,$param);						 
		}
	}
	
	private static function isValid($response){
		return strpos($response,'"totalResults": 0,')==false;
	}
	 	
 	/**
 	 * como o resultado vem por um json, com a requisição via url, poderia pegar via file_get_content
 	 * mas prefiro usar o CURL por ser mais confiavel é maleavel se for necessario em algum momento passar dados via post/cookie
 	 */
 	private static function getData($URL){				
		$header = array();	 	
		$header[0]= "Accept: text/xml,application/xml,application/xhtml+xml,";
		$header[0].= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$header[] = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:15.0) Gecko/20100101 Firefox/15.0.1';
		$header[] = "Cache-Control: max-age=0";
		$header[] = "Connection: keep-alive";
		$header[] = "Keep-Alive: 300";
		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$header[] = "Accept-Language: pt-br,pt;q=0.8,en-us;q=0.5,en;q=0.3";
		$header[] = "Pragma: "; // browsers keep this blank.
		$header[] = 'Content-type: text/html; charset=utf-8';
				
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$URL);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);		
				
		/*$cookie='';	
		curl_setopt($ch, CURLOPT_COOKIE,$cookie);	*/
				
	 	$result=curl_exec ($ch);
		curl_close ($ch);
				
		return $result; 
	}
	
	/**
	 * Define se a url foi carregada
	 *
	 * @param bool $loaded
	 */
	protected function setLodead($loaded){
		$this->lodaed = $loaded;
	}
	/**
	 * Indica se a url foi carregada com sucesso.
	 *
	 * @return bool
	 */
	protected function isLoaded(){
		return $this->lodaed;
	}
	/**
	 * Indica se o Xml foi carregado com sucesso
	 *
	 * @return bool
	 */
	public function isValidResponse(){
		return $this->isLoaded(); 
	}
	/**
	 * Caso exista um erro no carregamento, retorna a descrição
	 *
	 * @return string
	 */
	public function getErrorDescription(){
		if (!$this->isLoaded()){
			return "A requisição não foi realizada corretamente";
		}else{
			return "Nenhum erro encontrado";
		}
	}
	/**
	 * Caso existe um erro no carregmaento, retorna um número indicando ou 0 se estiver certo.
	 *
	 * @return int
	 */
	public function getErrorCode(){
		if (!$this->isLoaded()){
			return 666;
		}
		return 0;		
	}
	
		
	/**
	 * Define o objeto JSON
	 *
	 * @param obj JSON 
	 */
	protected function setJSON($json){
		$this->json = $json;
	}
	/**
	 * Retorna o objeto JSON
	 *
	 * @return obj JSON 
	 */
	public function getJSON(){
		return $this->json;
	}
	
	/**
	 * Carrega a API do Youtube
	 *
	 * @param string $method
	 * @param array[optional] $param
	 * @return bool
	 */
	public function load($method,$idVideo=null,array $param){
		//monta a url da requisição
		//exemplo: https://www.googleapis.com/youtube/v3/[METHOD]?key=[API_KEY]&id=[ID_VIDEO]&part=[PARAMS]
		
		$path = self::PATH;
		$path = str_replace('[METHOD]',$method,$path);
		$path = str_replace('[API_KEY]',self::API_KEY,$path);
		 				
		$url_parts = array();		 
		if(isset($idVideo)){
			$url_parts[] = 'id='.$idVideo;
		}
		if(isset($param)){
			$url_parts[] = 'part='.implode(',',$param);
		}
		if(count($url_parts)>0){
			$path.='&'.implode('&',$url_parts);
		}
		
		$this->setLodead(false);
		$json = $this->getData($path);
		if(!$this->isValid($json)){
			die($this->getErrorDescription());
		}
		$this->setLodead(true);
		
		try {
			@$this->setJSON(json_decode($json));
		}catch (Exception $e){
			$this->setLodead(false);
			$e->getCode();
		}
			
		return $this->isLoaded();
	}
	
	/**
	 * este metodo é responsavel por retornar os segundos corretamente
	 * passa uma string no formato 1:00:00 para segundos
	 * é meio "gato" mas funciona
	 *
	 * @param strin $time >>deve receber a string normalizada 1:00
	 * @return int $seconds;
	 */
	private function adjustSeconds($time){
		$seconds = 0;
		
		//1:16:51
		$i = explode(':',trim($time));
		//somente segundos
		if(count($i)==1){
			$seconds = (int)$i[0];
		}
		//minutos + segundos
		if(count($i)==2){
			$seconds+= (int)$i[0]*60;
			$seconds+= (int)$i[1];
		}
		//hora + minutos + segundos
		if(count($i)==3){
			$seconds+= (int)$i[0]*60*60;
			$seconds+= (int)$i[1]*60;
			$seconds+= (int)$i[2];
		}	
		
		return $seconds;
	}
	/**
	 * metodo para retornar os dados dos vídeos mais amigavelmente
	 * só irá funcionar se o retorno for a lista de vídeos   
	 * 
	 * @return json >>retorna um array com os dados simplificados dos vídeos
	 */
	public function fetchVideoData(){
	
		$json = $this->getJSON();
		
		$videos = array();
		
		$total = $json->pageInfo->resultsPerPage;
		if($total<=0){
			return $videos;
		}		
		for($n=0;$n!=$total;$n++){
			//monto o array do video com os elementos vazios, para preencher logo a baixo 
			$videoObj = array('id'=>'','title'=>'','description'=>'','time'=>'','time_friendly'=>'','seconds'=>0,'dimension'=>'','definition'=>'','caption'=>'','thumbs'=>array());
			
			$videoObj['id'] = $json->items[0]->id;
			
			if(isset($json->items[0]->snippet)){			
				$videoObj['title'] = $json->items[0]->snippet->title;
				$videoObj['description'] = $json->items[0]->snippet->description;			
			}
			if(isset($json->items[0]->contentDetails)){
				$videoObj['time'] = $json->items[0]->contentDetails->duration;
				$videoObj['time_friendly'] = str_replace(array('PT','S'),'',str_replace(array('H','M'),':',$json->items[0]->contentDetails->duration));
				
				$videoObj['seconds'] = $this->adjustSeconds($videoObj['time_friendly']);
				
				$videoObj['dimension'] = $json->items[0]->contentDetails->dimension;
				$videoObj['definition'] = $json->items[0]->contentDetails->definition;
				$videoObj['caption'] = $json->items[0]->contentDetails->caption;
			}
			
			if(isset($json->items[0]->snippet->thumbnails)){		
				$thumbs = array('default'=>array(),'medium'=>array(),'high'=>array(),'standard'=>array(),'maxres'=>array());	
				
				if(isset($json->items[0]->snippet->thumbnails->default)){		
					$thumbItem = array();
					$thumbItem['url'] = $json->items[0]->snippet->thumbnails->default->url;
					$thumbItem['width'] = $json->items[0]->snippet->thumbnails->default->width;
					$thumbItem['height'] = $json->items[0]->snippet->thumbnails->default->height;
					$thumbs['default'] = $thumbItem;
				}
				if(isset($json->items[0]->snippet->thumbnails->medium)){		
					$thumbItem = array();
					$thumbItem['url'] = $json->items[0]->snippet->thumbnails->medium->url;
					$thumbItem['width'] = $json->items[0]->snippet->thumbnails->medium->width;
					$thumbItem['height'] = $json->items[0]->snippet->thumbnails->medium->height;
					$thumbs['medium'] = $thumbItem;
				}
				if(isset($json->items[0]->snippet->thumbnails->high)){		
					$thumbItem = array();
					$thumbItem['url'] = $json->items[0]->snippet->thumbnails->high->url;
					$thumbItem['width'] = $json->items[0]->snippet->thumbnails->high->width;
					$thumbItem['height'] = $json->items[0]->snippet->thumbnails->high->height;
					$thumbs['high'] = $thumbItem;
				}
				if(isset($json->items[0]->snippet->thumbnails->standard)){		
					$thumbItem = array();
					$thumbItem['url'] = $json->items[0]->snippet->thumbnails->standard->url;
					$thumbItem['width'] = $json->items[0]->snippet->thumbnails->standard->width;
					$thumbItem['height'] = $json->items[0]->snippet->thumbnails->standard->height;
					$thumbs['standard'] = $thumbItem;
				}
				if(isset($json->items[0]->snippet->thumbnails->maxres)){		
					$thumbItem = array();
					$thumbItem['url'] = $json->items[0]->snippet->thumbnails->maxres->url;
					$thumbItem['width'] = $json->items[0]->snippet->thumbnails->maxres->width;
					$thumbItem['height'] = $json->items[0]->snippet->thumbnails->maxres->height;
					$thumbs['maxres'] = $thumbItem;
				}
				
				$videoObj['thumbs'] = $thumbs; 			
			} 
			
			
			$videos[] = $videoObj;
		
		}
				 
		///normaliza o array, transformando o em json
		///parece bobagem, mas facilita a utilização dos nodos
		return json_decode(json_encode(array('total'=>count($videos),'items'=>$videos)));	
	}
	

}

?>
