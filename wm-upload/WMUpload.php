<?php 
/**
 * WMUpload - Classe PHP para uploads de arquivos, inclusive imagens. 
 *
 * 	  A classe faz diversas validações no arquivo, o que inclui: tamanho em bytes
 *    dimensões em pixels em caso de imagens, tipos válidos, nome do arquivo, existencia
 *    e permissões do diretório de upload dentre outras.
 *   
 *    Com a classe é possível redimensionar e cropar a imagem enviada, além de poder aplicar
 *    uma marca d'agua conforme configuração.
 *
 *    Você poderá controlar dimensões minímas e máximas, tipos de arquivos aceitos, posição
 *	  do crop, posição da marca d'agua, modo do redimensionamento, qualidade da imagem dentre 
 *    outras opções.
 *    
 * Author : Welison Menezes
 * Author URI: http://welisonmenezes.com.br/
 * Version: 1.0
 *
 */

Class WMUpload
{
/**
 * array - receberá valor a partir do $_FILE setado no metodo addFile
 */
	private $_file;

/**
 * array - recebe as mensagens dos erros gerados pelas validações do plugin
 */
	private $_dataErrors;

/**
 * array - receberá um merge das cofiguraçõe do usuário setados no construct da classe e/ou
 * no metodo addFile com as opções default
 */
	private $_opt;

/**
 * receberá a imagem criada no metodo _setImage. É a imagem enviada
 */
	private $_image;

/**
 * receberá a imagem criada no metodo _cropImage. É a que será salva no servidor
 */
	private $_newImage;

/**
 * int - receberá a largura original da imagem
 */
	private $_origWidth;

/**
 * int - receberá a altura original da imagem
 */
	private $_origHeight;

/**
 * int - receberá a nova largura da imagem
 */
	private $_resizeWidth;

/**
 * int - receberá a nova altura da imagem
 */
	private $_resizeHeight;

/**
 * int - a posição horizontal do crop caso haja
 */
	private $_p_horizontal;

/**
 * int - a posição vertical do crop caso haja
 */
	private $_p_vertical;

/**
 * int - a posição horizontal da marca d'agua caso haja
 */
	private $_w_horizontal;

/**
 * int - a posição vertical da marca d'agua caso haja
 */
	private $_w_vertical;

/**
 *	string - receberá o mimetype o arquivo no metodo _setImage
 */
	private $_ext;

/**
 * Define um valor para o atributo $_file
 *
 * @param array - um array $_FILES específico. Ex: $_FILES['image']
 * @param string - optional - um identificador html (id ou classe), 
 *  					para identificar o elemento em caso de erro
 * @param arrray - optional - array de configurações
 * @return void
 */
    public function addFile($value, $element='', $options=array())
    {
    	if(!empty($value))
    	{
    		if(!empty($options))
    		{
    			$this->_opt = array_replace_recursive($this->_opt,$options);
    		}
	        $tmpAr = $value;
	        $tmpAr['element'] = $element;
	        array_push($this->_file, $tmpAr);
	    }
    }

/**
 * Retorna o elemento enviado após $_FILES passar por _convertFileToArray()
 *
 * @return array
 */
    public function getElementsSended()
    {
    	return $this->_file;
    }

/**
 * Retorna valor para o atributo $_dataErrors
 *
 * @return array
 */
    public function getDataErrors()
    {
    	return $this->_dataErrors;
    }

/**
 * Construtuora
 *
 * @param array - optional - um array de configurações para o funcionamento do plugin
 * @return void
 */
	public function __construct($config=array())
	{
		$default = array(
			'uploadDirectory' => 'uploads/',
			'maxFileSize' => 1048576, // bytes 
			'extensions' => array('jpg','jpeg','gif','png', 'txt'),
			'imageQuality' => 100,
			'limitImageSize' => array(
				'maxWidth' => false, // int or false
				'maxHeight' => false, // int or false,
				'minWidth' => false, // int or false
				'minHeight' => false // int or false
			),
			'resizeTo' => array(
				'resize' => true,
				'option' => 'default', // [default, exact, maxwidth, maxheight]
				'width' => 800,
				'height' => 600
			),
			'cropImage' => array(
				'crop' => true,
				'width' => 400,
				'height' => 400,
				'ratio' => true,
				'position' => 'center-center' // [top-left, top-center, top-right, center-left, center-center, center-right, bottom-left, bottom-center, bottom-right]
			),
			'backgroundImage' => array(
				'transparent' => 0, // [0-127]
				'color' => array(0, 0, 0) // [rgb]
			),
			'waterMark' => array(
				'waterMark' => false,
				'waterMarkDirectory' => 'watermark.png',
				'opacty' => 100, // [0-100]
				'position' => 'bottom-right' // [top-left, top-center, top-right, center-left, center-center, center-right, bottom-left, bottom-center, bottom-right]
			),
			'randomFilename' => array(
				'hasRandom' => false, // true or false
				'prefix' => 'your prefix here' // string or false
			),
			'validateNameFile' => array(
				'validate' => true, // true or false
				'regex' => '/^[a-zA-Z][a-zA-Z0-9\-\_\.]*$/' // regex
			),
			'errorsMessages' => array(
				'empty' => 'O elemento %s foi enviado vazio para o servidor',
				'file_1' => 'O arquivo %s excede o limite definido na diretiva upload_max_filesize',
				'file_2' => 'O arquivo %s excede o limite definido em MAX_FILE_SIZE no formulário HTML',
				'file_3' => 'O upload dos arquivos foi feito parcialmente',
				'file_4' => 'Nenhum arquivo foi enviado',
				'file_6' => 'Pasta temporária ausênte',
				'file_7' => 'Falha em escrever o arquivo %s em disco',
				'file_8' => 'Uma extensão do PHP interrompeu o upload do arquivo %s',
				'maxWidth' => 'A largura da imagem %s é maior do que a permitida',
				'maxHeight' => 'A altura da imagem %s é maior do que a permitida',
				'minWidth' => 'A largura da imagem %s é menor do que a permitida',
				'minHeight' => 'A altura da imagem %s é menor do que a permitida',
				'unallowedExtension' => 'O tipo do arquivo %s não é permitido',
				'maxFileSize' => 'O tamanho do arquivo %s excede o limite permitido',
				'isValidName' => 'O Arquivo %s contém caracteres inválidos em seu nome',
				'uploadDirectory' => 'O Diretório %s não foi encontrado',
				'uploadDirectoryWritable' => 'O Diretório %s não tem permissão de escrita',
				'waterMark' => 'O arquivo %s não foi encontrado',
				'waterMarkType' => 'A imagem para a marca d\'água deve ser do tipo PNG',
				'imageErrorUpload' => 'A imagem %s não pôde ser salva. Por favor tente novamente',
				'fileErrorUpload' => 'O arquivo %s não pôde ser salvo. Por favor tente novamente'
			)
		);
		$this->_opt = array_merge($default,$config);
		$this->_file = array();
		$this->_dataErrors = array();
	}

/**
 * Nela é imbutido os procedimentos necessários para
 * realizar as validações e ações necessárias para o upload
 *
 * @return booleam - true para ok e false em caso de erro
 */
	public function upload()
	{
		// ajustando array $_FILES para formato interno
		$this->_convertFileToArray();

		// checando se array $_FILES foi setado
		if($this->_isEmpty()) return false;

		if(!$this->_isValidDirectories()) return false;

		// checando validações
		if($this->_isValidAll())
		{
			// para cada arquivo...
			for($cont=0;$cont<count($this->_file);$cont++)
			{
				$file = $this->_file[$cont];

				// verificando se o arquivo é imagem
				if($this->_isImage($file['tmp_name']))
				{
					// salvando a imagem
					if(!$this->_saveImage($file['name'], $file['tmp_name']))
					{
						$this->_addDataErros($file['name'], $this->_opt['errorsMessages']['imageErrorUpload']);
						return false;
					}
					else
					{
						return true;
					}
				}
				else
				{	
					// salvando o arquivo
					if(!$this->_uploadFile($file['tmp_name'], $file['name']))
					{
						$this->_addDataErros($file['name'], $this->_opt['errorsMessages']['fileErrorUpload']);
						return false;
					}
					else
					{
						return true;
					}
				}
			}
		}
		else
		{
			return false;
		}

		// resetando erros
		unset($this->_dataErrors);
		$this->_dataErrors = array();

		// resetando file
		unset($this->_file);
		$this->_file = array();	
	}

/**
 * Aplica os metodos de resize, crop e marca d'agua e salva a imagem
 *
 * @param string - o 'name' do arquivo
 * @param string - o 'tmp_name' do arquivo
 * @return booleam
 */
	private function _saveImage($filename, $image)
	{
		$this->_setImage($image);
		$this->_cropImage();

		$file = $this->_opt['uploadDirectory'].$this->_getFileName($filename).'.'.$this->_getExtensionFromName($filename);
	    $imageQuality = $this->_opt['imageQuality'];

	    $im = false;
	    switch($this->_ext)
	    {
	        case 'image/jpg':
	        case 'image/jpeg':
	            if(imagetypes() & IMG_JPG) 
	            {
	                $im = imagejpeg($this->_newImage, $file, $imageQuality);
	            }
	            break;

	        case 'image/gif':
	            if(imagetypes() & IMG_GIF) 
	            {
	                $im = imagegif($this->_newImage, $file);
	            }
	            break;

	        case 'image/png':
	            $invertScaleQuality = 9 - round(($imageQuality/100) * 9);
	            if(imagetypes() & IMG_PNG) 
	            {
	                $im = imagepng($this->_newImage, $file, $invertScaleQuality);
	            }
	            break;
	    }

	    imagedestroy($this->_newImage);

	    return $im;
	}

/**
 * Seta a imagem de acordo com seu tipo original e seta as dimensões da mesma
 *
 * @param string - o 'tmp_name' do arquivo
 * @return void
 */
	private function _setImage($image)
	{
		$size = getimagesize($image);
		$this->_ext = $size['mime'];

	    switch($this->_ext)
	    {
	    	// Image is a JPG
	        case 'image/jpg':
	        case 'image/jpeg':
	        	// create a jpeg extension
	            $this->_image = imagecreatefromjpeg($image);
	            break;

	        // Image is a GIF
	        case 'image/gif':
	            $this->_image = @imagecreatefromgif($image);
	            break;

	        // Image is a PNG
	        case 'image/png':
	            $this->_image = @imagecreatefrompng($image);
	            break;

	        // Mime type not found
	        default:
	            throw new Exception("File is not an image, please use another file type.", 1);
	    }

	    $this->_origWidth = imagesx($this->_image);
	    $this->_origHeight = imagesy($this->_image);
	}

/**
 * Aplica o metodo resize faz o crop e aplica a marca d'agua
 *
 * @return void
 */
	private function _cropImage()
	{
		$this->_resizeTo();
		
		if($this->_opt['cropImage']['crop'])
		{
			$width = $this->_opt['cropImage']['width'];
			$height = $this->_opt['cropImage']['height'];

			$this->_makeRatio($width, $height);
		}
		else
		{
			$width = $this->_resizeWidth;
			$height = $this->_resizeHeight;
		}

		$this->_newImage = imagecreatetruecolor($width, $height);

		$this->_setPositionImg($this->_resizeWidth, $this->_resizeHeight, $width, $height, 'crop');

		$this->_setBGColor();

    	imagecopyresampled($this->_newImage, $this->_image, $this->_p_horizontal, $this->_p_vertical, 0, 0, $this->_resizeWidth, $this->_resizeHeight, $this->_origWidth, $this->_origHeight);
    	
    	$this->_waterMark($width, $height);
	}

/**
 * Aplica a marca d'agua
 *
 * @param int - a largura da imagem após o crop
 * @param int - a altura da imagem após o crop
 * @return void
 */
	private function _waterMark($c_width, $c_height)
	{
		if($this->_opt['waterMark']['waterMark'])
		{
			$watermark = imagecreatefrompng($this->_opt['waterMark']['waterMarkDirectory']); 
			$watermark_width = imagesx($watermark);  
			$watermark_height = imagesy($watermark);  
			$opacty = $this->_opt['waterMark']['opacty'];

			$this->_setPositionImg($watermark_width, $watermark_height, $c_width, $c_height, 'watermark');

    		imagecopymerge($this->_newImage, $watermark, $this->_w_horizontal, $this->_w_vertical, 0, 0, $watermark_width, $watermark_height, $opacty);
		}
	}

/**
 * Seta a cor e a opacidade do background da imagem conforme configuração
 *
 * @return void
 */
	private function _setBGColor()
	{
		$opacty = $this->_opt['backgroundImage']['transparent'];
		$red = $this->_opt['backgroundImage']['color'][0];
		$green = $this->_opt['backgroundImage']['color'][1];
		$blue = $this->_opt['backgroundImage']['color'][2];

		imagesavealpha($this->_newImage, true);
		$color = imagecolorallocatealpha($this->_newImage, $red, $green, $blue, $opacty); 
		imagefill($this->_newImage, 0, 0, $color);
	}

/**
 * Defina a posição do [crop/marca d'agua]
 *
 * @param int - a largura do [crop/marca]
 * @param int - a altura do [crop/marca]
 * @param int - a largura da [image original/imagem cropada]
 * @param int - a altura da [imagem original/imagem cropada]
 * @param string - optional - [crop/watermark] indicando se posicionará o crop ou a marca d'agua
 * @return void
 */
	private function _setPositionImg($w_width, $w_height, $c_width, $c_height, $target='crop')
	{
		//echo $c_width;
		if($target=='crop')
		{
			$position = $this->_opt['cropImage']['position'];
		}
		else if($target=='watermark')
		{
			$position = $this->_opt['waterMark']['position'];
		}

		switch($position)
	    {
	    	case 'top-left':
	    		$w_horizontal = 0;
				$w_vertical = 0;
	    		break;

	    	case 'top-center':
	    		$w_horizontal = ($c_width/2)-($w_width/2);
				$w_vertical = 0;
	    		break;

	    	case 'top-right':
	    		$w_horizontal = ($c_width)-($w_width);
				$w_vertical = 0;
	    		break;

	    	case 'center-left':
	        	$w_horizontal = 0;
				$w_vertical = ($c_height/2)-($w_height/2);
	            break;

	        case 'center-center':
	        	$w_horizontal = ($c_width/2)-($w_width/2);
				$w_vertical = ($c_height/2)-($w_height/2);
	            break;

	        case 'center-right':
	        	$w_horizontal = ($c_width)-($w_width);
				$w_vertical = ($c_height/2)-($w_height/2);
	            break;

	        case 'bottom-left':
	        	$w_horizontal = 0;
				$w_vertical = ($c_height)-($w_height);
	            break;

	        case 'bottom-center':
	        	$w_horizontal = ($c_width/2)-($w_width/2);
				$w_vertical = ($c_height)-($w_height);
	            break;

	        case 'bottom-right':
	        	$w_horizontal = ($c_width)-($w_width);
				$w_vertical = ($c_height)-($w_height);
	            break;

	        default:
	            $w_horizontal = ($c_width/2)-($w_width/2);
				$w_vertical = ($c_height/2)-($w_height/2);
	    }

	    if($target=='crop')
		{
			$this->_p_horizontal = $w_horizontal;
			$this->_p_vertical = $w_vertical;
		}
		else if($target=='watermark')
		{
			$this->_w_horizontal = $w_horizontal;
			$this->_w_vertical = $w_vertical;
		}
	}

/**
 * Redimensiona a imagem conforme configurações
 *
 * @return void
 */
	private function _resizeTo()
	{
		$resizeOption = $this->_opt['resizeTo']['option'];
		$width = $this->_opt['resizeTo']['width'];
		$height = $this->_opt['resizeTo']['height'];

		if($this->_opt['resizeTo']['resize'])
		{
			switch(strtolower($resizeOption))
			{
				case 'exact':
					$this->_resizeWidth = $width;
					$this->_resizeHeight = $height;
					break;

				case 'maxwidth':
					if($this->_origWidth < $width)
					{
						$this->_resizeWidth = $this->_origWidth;
					}
					else
					{
						$this->_resizeWidth = $width;
					}
					$this->_resizeHeight = $this->_resizeHeightByWidth($this->_resizeWidth);
					break;

				case 'maxheight':
					if($this->_origHeight < $height)
					{
						$this->_resizeHeight = $this->_origHeight;
					}
					else
					{
						$this->_resizeHeight = $height;
					}
					$this->_resizeWidth  = $this->_resizeWidthByHeight($this->_resizeHeight);
					break;

				default:
					if($this->_origWidth > $width || $this->_origHeight > $height)
					{
						if($this->_origWidth > $this->_origHeight) 
						{
					    	$this->_resizeHeight = $this->_resizeHeightByWidth($width);
				  			$this->_resizeWidth  = $width;
						} 
						else if($this->_origWidth < $this->_origHeight) 
						{
							$this->_resizeWidth  = $this->_resizeWidthByHeight($height);
							$this->_resizeHeight = $height;
						}  
						else 
						{
							$this->_resizeWidth = $width;
							$this->_resizeHeight = $height;	
						}
					} 
					else 
					{
						if($this->_origWidth < $width)
						{
							$this->_resizeWidth = $this->_origWidth;
						}
						else
						{
							$this->_resizeWidth = $width;
						}

						if($this->_origHeight < $height)
						{
							$this->_resizeHeight = $this->_origHeight;
						}
						else
						{
							$this->_resizeHeight = $height;
						}
			        }
					break;
			}
		}
		else
		{
			$this->_resizeWidth = $this->_origWidth;
			$this->_resizeHeight = $this->_origHeight;	
		}
	}

/**
 * Aplica a marca d'agua
 *
 * @param int - a largura da imagem após o crop
 * @param int - a altura da imagem após o crop
 * @return void
 */
	private function _makeRatio($width, $height)
	{
		if($this->_opt['cropImage']['ratio'])
		{
			$tmpW = $tmpH = null;
			$makeRatio = false;
			if($this->_resizeWidth > $width)
			{
				$tmpW = $width;
				$tmpH = $this->_resizeHeightByWidth($tmpW);
				$makeRatio = true;
			}
			else if($this->_resizeHeight > $height)
			{
				$tmpH = $height;
				$tmpW = $this->_resizeWidthByHeight($tmpH);
				$makeRatio = true;
			}
			else
			{
				$tmpW = $this->_resizeWidth;
				$tmpH = $this->_resizeHeight;
			}

			if($makeRatio)
			{
				if($tmpW != null && $tmpW < $width)
				{
					$tmpW = $width;
					$tmpH = $this->_resizeHeightByWidth($tmpW);
				}
				else if($tmpH != null && $tmpH < $height)
				{
					$tmpH = $height;
					$tmpW = $this->_resizeWidthByHeight($tmpH);
				}
			}

			$this->_resizeWidth = $tmpW;
			$this->_resizeHeight = $tmpH;
		}
	}

/**
 * Calucula altura a partir da largura
 *
 * @param int - a largura
 * @return int - a nova altura
 */
	private function _resizeHeightByWidth($width)
	{
		return floor(($this->_origHeight/$this->_origWidth)*$width);
	}

/**
 * Calcual a largura a partir da altura
 *
 * @param int - a altura
 * @return int - a nova largura
 */
	private function _resizeWidthByHeight($height)
	{
		return floor(($this->_origWidth/$this->_origHeight)*$height);
	}

/**
 * Faz o upload do arquivo
 *
 * @param string - o 'tmp_name' do arquivo
 * @param string - o 'name' do arquivo
 * @return booleam - true pra ok e false para erro
 */
	private function _uploadFile($tmpfile, $filename)
	{
		$file = $this->_opt['uploadDirectory'].$this->_getFileName($filename).'.'.$this->_getExtensionFromName($filename);
		if(move_uploaded_file($tmpfile, $file)) 
		{
		    return true;
		} 
		else 
		{
		    return false;
		}
	}

/**
 * Valida os arquivos e executa outros métodos de validação 
 *
 * @return booleam - true para ok e false para erro
 */
	private function _isValidAll()
	{
		$err = 0;
		for($cont=0;$cont<count($this->_file);$cont++)
		{
			$file = $this->_file[$cont];

			// valida os erros do array $_FILES
			if($this->_hasErrorArFiles($file['error']))
			{
				switch ($file['error']) 
				{
					case 1: 
						$this->_addDataErros($file['name'], $this->_opt['errorsMessages']['file_1']);
						break;
					case 2: 
						$this->_addDataErros($file['name'], $this->_opt['errorsMessages']['file_2']); 
						break;
					case 3: 
						$this->_addDataErros($file['name'], $this->_opt['errorsMessages']['file_3']); 
						break;
					case 4: 
						$this->_addDataErros($file['name'], $this->_opt['errorsMessages']['file_4']);
						break;
					case 6: 
						$this->_addDataErros($file['name'], $this->_opt['errorsMessages']['file_6']);
						break;
					case 7: 
						$this->_addDataErros($file['name'], $this->_opt['errorsMessages']['file_7']); 
						break;
					case 8: 
						$this->_addDataErros($file['name'], $this->_opt['errorsMessages']['file_8']);
						break;
				}
				$err++;
			}

			if(!$this->_isValidName($file['name']))
			{
				$err++;
			}

			// check image size
			if(!$this->_checkImageSize($file['name'], $file['tmp_name']))
			{
				$err++;
			}

			// check if has exstension
			if(!$this->_hasExtensions($file['name']))
			{
				$this->_addDataErros($file['name'], $this->_opt['errorsMessages']['unallowedExtension']);
				$err++;
			}

			// check max size of file
			if($this->_maxSize($file['size']))
			{
				$this->_addDataErros($file['name'], $this->_opt['errorsMessages']['maxFileSize']);
				$err++;
			}
		}

		return ($err>0) ? false : true;
	}

/**
 * Retorna o nome com o qual o arquivo será salvo no servidor. 
 * De acordo com as cofigurações retorna um nome randômico ou baseado no 'name' do arquivo
 *
 * @param string - o 'name' do arquivo
 * @return string
 */
	private function _getFileName($filename)
	{
		if($this->_opt['randomFilename']['hasRandom']===true)
		{
			return $this->_getRandomFilename($filename);
		}
		else
		{
			return $this->_getUniqueFilename($filename);
		}
	}

/**
 * Verifica se nome do arquivo já existe no servidor e 
 * busca retornar um nome único para o arquivo baseado no 'name' do arquivo
 *
 * @param string - o 'name' do arquivo
 * @return string
 */
	private function _getUniqueFilename($filename)
	{
		$cont = 0;
		do
		{
			if($cont == 0)
			{
				$newName = $this->_removeExtension($filename);
				$fullPath = $this->_opt['uploadDirectory'].$this->_removeExtension($filename).'.'.$this->_getExtensionFromName($filename);
			}
			else
			{
				$newName = $this->_removeExtension($filename).'-'.$cont;
				$fullPath = $this->_opt['uploadDirectory'].$this->_removeExtension($filename).'-'.$cont.'.'.$this->_getExtensionFromName($filename);
			}
			$cont++;
		}
		while(file_exists($fullPath));

		return $newName;
	}

/**
 * Retorna um nome baseado em tecnicias de geração de string randômicas
 *
 * @param string - o 'name' do arquivo
 * @return strig
 */
	private function _getRandomFilename($filename)
	{
		if($this->_opt['randomFilename']['prefix'] != false)
		{
			return  md5(uniqid($this->_opt['randomFilename']['prefix'], true).uniqid(rand(4,4), true).uniqid($filename, true));
		}
		else
		{
			return  md5(uniqid(rand(4,4), true).uniqid($filename, true));
		}
	}

/**
 * Retorna o tipo do arquivo segundo seu 'type'
 *
 * @param string - o 'type' do arquivo
 * @return string or null
 */
	private function _getExtensionFromMime($mime){
		$ext = explode('/', $mime);
		return (is_array($ext) && isset($ext[1])) ? $ext[1] : null;
	}

/**
 * Retorna o tipo do arquivo segundo o seu 'name'
 *
 * @param string - o 'name' do arquivo
 * @return sting or null
 */
	private function _getExtensionFromName($filename){
		$ar = explode('.', $filename);
		if(is_array($ar))
		{
			$ext = end($ar);
			return $ext;
		}
		return null;
	}

/**
 * Verifica as dimensões da imagem
 *
 * @param string - o 'name' do arquivo
 * @param string - o 'tmp_name' do arquivo
 * @return booleam
 */
	private function _checkImageSize($filename, $image)
	{
		if(!$this->_isImage($image)) return true;
		if(!empty($image))
		{
			$size = getimagesize($image);
			$ret = true;
			if(isset($size[0]) && isset($size[1]))
			{
				if($this->_opt['limitImageSize']['maxWidth'] != false && ($size[0] > $this->_opt['limitImageSize']['maxWidth']))
				{
					$this->_addDataErros($filename, $this->_opt['errorsMessages']['maxWidth']);
					$ret = false;
				}
				if($this->_opt['limitImageSize']['maxHeight'] != false && ($size[1] > $this->_opt['limitImageSize']['maxHeight']))
				{
					$this->_addDataErros($filename, $this->_opt['errorsMessages']['maxHeight']);
					$ret = false;
				}
				if($this->_opt['limitImageSize']['minWidth'] != false && ($size[0] < $this->_opt['limitImageSize']['minWidth']))
				{
					$this->_addDataErros($filename, $this->_opt['errorsMessages']['minWidth']);
					$ret = false;
				}
				if($this->_opt['limitImageSize']['minHeight'] != false && ($size[1] < $this->_opt['limitImageSize']['minHeight']))
				{
					$this->_addDataErros($filename, $this->_opt['errorsMessages']['minHeight']);
					$ret = false;
				}
				return $ret;
			}
		}
		return true;
	}

/**
 * Verifica se a extensão do arquivo condiz com configuração
 *
 * @param string - o 'name' do arquivo
 * @return booleam
 */
	private function _hasExtensions($filename)
	{
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		return (in_array($ext,$this->_opt['extensions']));
	}

/**
 * Verifica o tamanho do arquivo em bytes
 *
 * @param string - o 'size' do arquivo
 * @return booleam
 */
	private function _maxSize($filesize)
	{
		return ($filesize > $this->_opt['maxFileSize']);
	}

/**
 * Verifica se o arquivo tem erro ou não
 *
 * @param string - o 'error' do arquivo
 * @return booleam
 */
	private function _hasErrorArFiles($fileError)
	{
		if($fileError==0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

/**
 * Verifica se o nome do arquivo é válido
 *
 * @param string - o 'name' do arquivo
 * @return booleam
 */
	private function _isValidName($filename)
	{
		if($this->_opt['validateNameFile']['validate']===false) return true;
		if(preg_match($this->_opt['validateNameFile']['regex'],$filename)) 
		{
		    return true;
		}
		else
		{
		    $this->_addDataErros($filename, $this->_opt['errorsMessages']['isValidName']);
		    return false;
		}
	}

/**
 * Covert o array $_FILES para um padrão interno
 *
 * @return array - um 'novo' array $_FILES
 */
	private function _convertFileToArray()
	{
		$cont = 0;
		$newAr = array();
		foreach ($this->_file as $key => $value) 
		{
			if(is_array($value['name']))
			{
				$cont2 = 0;
				foreach ($value['name'] as $k => $v) 
				{
					$newAr[$cont]['name'] = (isset($value['name'][$cont2])) ? $value['name'][$cont2] : null;
					$newAr[$cont]['type'] = (isset($value['type'][$cont2])) ? $value['type'][$cont2] : null;
					$newAr[$cont]['tmp_name'] = (isset($value['tmp_name'][$cont2])) ? $value['tmp_name'][$cont2] : null;
					$newAr[$cont]['error'] = (isset($value['error'][$cont2])) ? $value['error'][$cont2] : null;
					$newAr[$cont]['size'] = (isset($value['size'][$cont2])) ? $value['size'][$cont2] : null;
					$newAr[$cont]['element'] = (isset($value['element'])) ? $value['element'] : null;
					$cont2++;
					$cont++;
				}
			}
			else
			{
				$newAr[$cont] = $value;
				$cont++;
			}
		}
		$this->_file = $newAr;
	}

/**
 * Retorna o nome do arquivo sem a extensão
 *
 * @param string - o 'name' do arquivo
 * @return string
 */
	private function _removeExtension($filename) 
	{
	    return substr($filename, 0,strrpos($filename,'.'));   
	}

/**
 * Adiciona os dados de erros ao atributo $_dataErrors
 *
 * @param string - o 'name' do arquivo
 * @param string - a mensagem de error - nesse caso o caracter %s será trocado pelo 
 * 					'name' do arquivo
 * @return string
 */
	private function _addDataErros($filename, $message)
	{
		$error = str_replace('%s', $filename, $message);
		array_push($this->_dataErrors, $error);
	}

/**
 * Verifica se o arquivo é uma imagem dos tipos jpg, jpeg, gif ou png
 *
 * @param string - o 'tmp_name' do arquivo
 * @return booleam
 */
	private function _isImage($image)
	{
		if(!empty($image))
		{
			if( (mime_content_type($image)=='image/jpg') || (mime_content_type($image)=='image/jpeg') || (mime_content_type($image)=='image/gif') || (mime_content_type($image)=='image/png') )
			{
			    return true;
			}
			else
			{
				return false;
			}
		}
		return true;
	}

/**
 * Verifica se o 'name' do arquivo está vazio
 *
 * @return booleam 
 */
	private function _isEmpty()
	{
		$ret = false;
		foreach ($this->_file as $k => $v) {
			if(empty($v['name'])) 
			{
				$this->_addDataErros($v['element'], $this->_opt['errorsMessages']['empty']);
				$ret = true;
			}
		}
		return $ret;
	}

/**
 * Verifica se uploadDirectory existe e se existe permissão de escrita
 * também verifica se a imagem para a marca d'agua existe e se é do tipo png
 *
 * @return booleam
 */
	private function _isValidDirectories()
	{
		$ret = true;
		if(!is_dir($this->_opt['uploadDirectory']))
		{
			$this->_addDataErros($this->_opt['uploadDirectory'], $this->_opt['errorsMessages']['uploadDirectory']);
			$ret = false;
		}
		else
		{
			if(!is_writable($this->_opt['uploadDirectory']))
			{
				$this->_addDataErros($this->_opt['uploadDirectory'], $this->_opt['errorsMessages']['uploadDirectoryWritable']);
				$ret = false;
			}
		}

		if($this->_opt['waterMark']['waterMark'])
		{
			if(!file_exists($this->_opt['waterMark']['waterMarkDirectory']))
			{
				$this->_addDataErros($this->_opt['waterMark']['waterMarkDirectory'], $this->_opt['errorsMessages']['waterMark']);
				$ret = false;
			}
			else if(exif_imagetype($this->_opt['waterMark']['waterMarkDirectory']) != IMAGETYPE_PNG)
			{
				$this->_addDataErros($this->_opt['waterMark']['waterMarkDirectory'], $this->_opt['errorsMessages']['waterMarkType']);
				$ret = false;
			}
		}

		return $ret;
	}
}