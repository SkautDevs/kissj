<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Korbo test</title>
	<link rel="stylesheet" href="public/styles.css">
</head>
<body class="approved-theme">
<h1 class="text-center">Registrace Korbo 2019</h1>
<form action="<?php if (!empty($_POST['wish'])): ?>test.php/..<?php endif ?>"
	  method="<?php if (!empty($_POST['wish'])): ?>GET<?php else: ?>POST<?php endif ?>"
	  class="card card-half">
	<p>Na registraci na Korbo 2019 jseš tu správně, nejdřív tě ale poprosíme o zodpovězení jedné otázky.</p>
	<h2><label for="wish">Co by sis na Korbu 2019 přál?</label></h2>
	<?php if (empty($_POST['wish'])) {
		echo('<br/>
	<input required type="text" name="wish" class="form-control form-wide" autofocus>
	<br/>
	<input type="submit" class="btn form-wide" value="Odeslat odpověď">
	');
	} else {
		echo('

	<p>'.htmlspecialchars($_POST['wish'], ENT_QUOTES).'</p>
	<h2><label for="wish">Co konkrétně pro to uděláš?</label></h2>
	<input required type="text" name="wish" class="form-control form-wide" autofocus>
	<br/>
	<input type="submit" class="btn form-wide" value="Odeslat a do registrace!">
	');
	} ?>
</form>
</body>
</html>
