<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>KORBO test</title>
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<link rel="stylesheet" href="public/styles.css">
	<style>
		input[type=radio]:checked + label {
			color: #1c860e;
			font-weight: 800;
		}

		form.showResults input[type=radio]:checked + label {
			color: #761c19;
			font-weight: 800;
		}

		form.showResults input[type=radio].t + label {
			color: #1c860e;
			font-weight: 800;
		}
	</style>
</head>
<body class="approved-theme">
<h1 class="text-center">Registrace Korbo 2018</h1>
<form action="" method="POST" class="card-half card<?php if (!empty($_POST)) echo(' showResults') ?>">
	<p>Na registraci na KORBO jseš tu správně, předtím je ale třeba absolvovat malý testík!</p>
	<h3>Témata akce jsou:</h3>
	<input required type="radio" name="1" id="11" <?php if ($_POST['1'] == 11) echo('checked'); ?> value="11"><label
			for="11">Svoboda</label><br/>
	<input type="radio" name="1" id="12" <?php if ($_POST['1'] == 12) echo('checked'); ?> value="12"><label for="12">Skauting</label><br/>
	<input type="radio" name="1" id="13" <?php if ($_POST['1'] == 13) echo('checked'); ?> value="13"><label for="13">Zodpovědnost</label><br/>
	<input class="t" type="radio" name="1" id="14" <?php if ($_POST['1'] == 14) echo('checked'); ?> value="14"><label
			for="14">Vše výše zmíněné</label>
	<br/>
	<br/>
	<br/>
	<h3>Kdo má zodpovědnost za úklid tábořiště a setřepání toiek?</h3>
	<input required type="radio" name="2" id="21" <?php if ($_POST['2'] == 21) echo('checked'); ?> value="21"><label
			for="21">Nikdo</label><br/>
	<input type="radio" name="2" id="22" <?php if ($_POST['2'] == 22) echo('checked'); ?> value="22"><label for="22">Organizační
		tým</label><br/>
	<input type="radio" name="2" id="23" <?php if ($_POST['2'] == 23) echo('checked'); ?> value="23"><label for="23">Servis
		tým</label><br/>
	<input class="t" type="radio" name="2" id="24" <?php if ($_POST['2'] == 24) echo('checked'); ?> value="24"><label
			for="24">Všichni</label>
	<br/>
	<br/>
	<br/>
	<h3>S čím ti může pomoci přípravný tým před akcí při přípravě aktivit:</h3>
	<input required type="radio" name="3" id="31" <?php if ($_POST['3'] == 31) echo('checked'); ?> value="31"><label
			for="31">Konzultací</label><br/>
	<input type="radio" name="3" id="32" <?php if ($_POST['3'] == 32) echo('checked'); ?> value="32"><label for="32">Penězi</label><br/>
	<input type="radio" name="3" id="33" <?php if ($_POST['3'] == 33) echo('checked'); ?> value="33"><label for="33">Sehnáním a dopravou materiálu</label><br/>
	<input class="t" type="radio" name="3" id="34" <?php if ($_POST['3'] == 34) echo('checked'); ?> value="34"><label
			for="34">Vše výše zmíněné</label><br/>
	<br/>
	<?php if (empty($_POST)) echo('<input type="submit" class="btn form-wide" value="Zkontrolovat test">');
	else echo('<p>Děkujeme za vyplnění testu! Teď můžeš pokračovat</p>
<a href="test.php/.." class="btn btn-wide">Do registrace!</a>');
	?>
</form>
</body>
</html>
