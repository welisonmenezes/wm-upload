<?php 
// descomentar para testar o exemplo
/*
require_once('wm-upload/WMUpload.php');

$config = array(
	'uploadDirectory' => 'assets/uploads/',
	'waterMark' => array(
		'waterMark' => true,
		'waterMarkDirectory' => 'assets/img/watermark.png',
	)
);
$wm = new WMUpload($config);

$file = (isset($_FILES['file'])) ? $_FILES['file'] : null;
$file2 = (isset($_FILES['file2'])) ? $_FILES['file2'] : null;

$wm->addFile($file, 'file_1');
$wm->addFile($file2, 'file_2');
if($wm->upload())
{
	echo 'arquivo salvo com sucesso<br>';
}
else
{
	var_dump($wm->getDataErrors());
}
//*/
?>

<!DOCTYPE html>
<html lang="pt-br">
	<head>
		<meta charset="UTF-8">
		<meta name="description" content="WM Upload - Classe PHP para upload de arquivos">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>

		<link rel="stylesheet" href="assets/css/bootstrap.min.css">
		<link rel="stylesheet" href="assets/css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="assets/css/app.css">

		<title>WM Upload - Classe PHP para upload de arquivos</title>
	</head>
	<body>

		<nav class="navbar navbar-inverse">
			<div class="container">
				<div id="navbar" class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
                        <li><a href="index.html" title="Home">Home</a></li>
                        <li><a href="init.html" title="Inicialização">Inicialização</a></li>
                        <li><a href="config.html" title="Configurações">Configurações</a></li>
                        <li class="active"><a href="exemple.html" title="Exemplo">Exemplo</a></li>
                    </ul>
				</div>
			</div>
		</nav>

		<div class="container">

			<div class="row">

			  	<div class="col-sm-12">

			  		<article class="body">
			  			<h1>
			  				WM Upload
			  			</h1>
			  			<h2>
			  				Classe PHP para upload de arquivos
			  			</h2>
                        <hr>

                        <h4>O formulário está comentado. Para ver o exemplo baixe o fonte no github. Após baixar descomente o código PHP no início do arquivo e o formulário HTML abaixo deste html.</h4>
                        <!-- 
                        <form action="#" method="post" enctype="multipart/form-data">
							<div class="box-d">
								<div class="box-header">
									<h2>Exemplo</h2>
								</div>
								<hr>
								<div class="box-body">

									<div class="form-group">
										<label>file 1</label>
										<input name="file[]" id="file_1" type="file" class="" multiple>
									</div>

									<input type="hidden" name="action" value="teste" />
									
									<div class="form-group">
										<label>file 2</label>
										<input name="file2" id="file_2" type="file" class="">
									</div>

								</div>
							</div>

							<div class="form-f right">
								<button class="btn big btn-primary wm-submit">
									Enviar
								</button>
							</div>

						</form>
						-->

						<br><br><hr><br><br>


                        <ul class="list-group">
                            <li class="list-group-item">
                                <a href="https://github.com/welisonmenezes/wm-upload" target="_blank" class="btn btn-primary btn-large btn-block" title="WM Gridfolio no Github">GITHUB</a>
                            </li>
                            <li class="list-group-item">
                                <a href="https://twitter.com/WelisonMenezes" target="_blank" class="btn btn-primary btn-large btn-block" title="Perfil no Twitter">TWITTER</a>
                            </li>
                            <li class="list-group-item">
                                <a href="http://welisonmenezes.com.br/" target="_blank" class="btn btn-primary btn-large btn-block" title="Portifólio Welison Menezes">WEBSITE</a>
                            </li>
                        </ul>

                        <hr>
                        <p>
                            Versão 1.0<br>
                            Created by Welison Menezes.
                        </p>
			  		</article>
			  		
			  	</div>

			</div>

		</div><!-- /.container --> <br>

		<br><br><br>

		<footer class="footer">
			<div class="container">
			<p class="text-muted">&copy; Todos os direitos reservados. 2014 || Welison Menezes</p>
			</div>
		</footer>

		<script type="text/javascript" src="assets/js/jquery-1.11.1.js"></script>
		<script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
	</body>
</html>