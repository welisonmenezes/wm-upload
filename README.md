wm-upload
===========

WM Upload - Classe PHP para upload de arquivos

## Instalação

	require_once('wm-upload/WMUpload.php');

## Inicialização
	
	$config = array('uploadDirectory' => 'uploads/');
    $upload = new WMUpload($config);

## Adicionando arquivos para upload

	$file = (isset($_FILES['file'])) ? $_FILES['file'] : null;
	$file2 = (isset($_FILES['file2'])) ? $_FILES['file2'] : null;

	$upload->addFile($file);
	$upload->addFile($file2);

## Fazendo os uploads
	
	$upload->upload();

## Acessando os dados enviados

	$infos = $upload->getElementsSended();
	var_dump($infos);

## Mensagens de erro
	
	$erros = $upload->getDataErrors();
	var_dump($erros);

## Para mais detalhes visite a página do plugin

[Website do Plugin WM Upload](http://welisonmenezes.com.br/extras/plugins/php/wm-upload/)