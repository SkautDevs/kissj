<?php

$dbName = 'db_dev.sqlite';

if (is_file(__DIR__.'/../.env')) {
    echo('Sorry, installation has been done before. Delete file .env to restart it');
    die;
}

if (is_file(__DIR__.'/../src/'.$dbName)) {
    echo('Sorry, installation has been done before. Delete src/'.$dbName.' to restart it');
    die;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $basename = str_replace('/install.php', '', $_SERVER['REQUEST_URI']);
    echo '
<!DOCTYPE html>
<head><title>KISSJ - installation</title></head>
<body>
    <h1>KISSJ installation</h1>
    <form method="POST">
        <h2>Administrator</h2>
        <input name="basepath" type="hidden" value="'.$basename.'" required>
        <label>Amidninistrator email: <input name="administrator_email" type="email" required></label><br>
        <h2>Adminer</h2>
        <label>Admin login: <input name="adminer_login" type="text" required></label><br>
        <label>Admin password: <input name="adminer_password" type="password" required></label><br>
        <h2>Event details</h2>
        <label>Short event name, no spaces (slug): <input name="event_slug" type="text" required></label><br>
        <label>Full event name: <input name="event_readable_name" type="text" required></label><br>
        <label>Account number with bank code (currently Fio only): <input name="event_account_number" type="text"></label><br>
        <label>Variable symbol prefix (could be empty): <input name="event_prefix_variable_symbol" type="text"></label><br>
        <!--<label>automatic_payment_pairing: <input name="event_automatic_payment_pairing" type="text"></label><br>-->
        <!--<label>bank_id: <input name="event_bank_id" type="text"></label><br>-->
        <label>Bank api key for read (currently Fio only): <input name="event_bank_api_key" type="text"></label><br>
        <label>Maximum elapsed payment days: <input name="event_max_elapsed_payment_days" type="number" min="0" required></label><br>
        <label>Scarf price: <input name="event_scarf_price" type="number" min="0" required></label><br>
        <!--<label>T-shirt price: <input name="event_tshirt_price" type="text"></label><br>-->
        <!--<label>allow_patrols: <input name="event_allow_patrols" type="text"></label><br>-->
        <!--<label>maximal_closed_patrols_count: <input name="event_maximal_closed_patrols_count" type="text"></label><br>-->
        <!--<label>minimal_patrol_participants_count: <input name="event_minimal_patrol_participants_count" type="text"></label><br>-->
        <!--<label>maximal_patrol_participants_count: <input name="event_maximal_patrol_participants_count" type="text"></label><br>-->
        <!--<label>allow_ists: <input name="event_allow_ists" type="text"></label><br>-->
        <label>Maximal closed ISTs: <input name="event_maximal_closed_ists_count" type="number" min="0" required></label><br>
        <label>Event web URL: <input name="event_web_url" type="url" value="https://" required></label><br>
        <label>URL for participant rules agreement: <input name="event_data_protection_url" type="url" value="https://" required></label><br>
        <!--<label>diet_price: <input name="event_diet_price" type="text"></label><br>-->
        <label>IST label: <input name="event_ist_label" type="text" required></label><br>
        <label>Event start: <input name="event_event_start" type="date" required></label><br>
        <label>Event contact email: <input name="event_contact_email" type="email" required></label><br>
        <br>
        <input type="submit">
    </form>
</body>
</html>';
    die;
}

// set enviromental variables

if (!copy(__DIR__.'/../.env.example', __DIR__.'/../.env')) {
    echo 'failed to copy .env.example - please check permissions';
    die;
}

if (!chmod(__DIR__.'/../.env', 0666)) {
    echo 'failed to set .env right permission - please check permissions';
    die;
}

$envFile = file(__DIR__.'/../.env');
$newEnvFile = array_map(function ($line) {
    $explodedLine = explode('=', $line);

    switch ($explodedLine[0]) {
        case 'BASEPATH':
            return 'BASEPATH="'.$_POST['basepath'].'"'.PHP_EOL;

        case 'ADMINER_LOGIN':
            return 'ADMINER_LOGIN="'.$_POST['adminer_login'].'"'.PHP_EOL;

        case 'ADMINER_PASSWORD':
            return 'ADMINER_PASSWORD="'.$_POST['adminer_password'].'"'.PHP_EOL;

        case 'PAYMENT_ACCOUNT_NUMBER':
            return 'PAYMENT_ACCOUNT_NUMBER="'.$_POST['event_account_number'].'"'.PHP_EOL;

        case 'PAYMENT_FIO_API_TOKEN':
            return 'PAYMENT_FIO_API_TOKEN="'.$_POST['event_bank_api_key'].'"'.PHP_EOL;

        default:
            return $line;
    }
}, $envFile);

if (!file_put_contents(__DIR__.'/../.env', implode('', $newEnvFile))) {
    echo 'failed to write into .env - please check permissions';
    die;
}

// init sqlite database

try {
    $pdo = new PDO('sqlite:'.__DIR__.'/../src/'.$dbName);
} catch (PDOException $e) {
    echo 'failed to create new sqlite database file - check permissions';
    die;
}

if (!chmod(__DIR__.'/../src/'.$dbName, 0666)) {
    echo 'failed to set db file '.$dbName.' right permission - please check permissions';
    die;
}

try {
    $pdo->exec(file_get_contents(__DIR__.'/../sql/init.sql'));

    $quotedNow = '"'.date(DATE_ATOM).'"';
    $queryEvent = $pdo->prepare('INSERT INTO `event` (
                     `slug`, 
                     `readable_name`,
                     `account_number`,
                     `prefix_variable_symbol`,
                     `max_elapsed_payment_days`,
                     `scarf_price`,
                     `maximal_closed_ists_count`,
                     `web_url`,
                     `data_protection_url`,
                     `ist_label`,
                     `event_start`,
                     `contact_email`,
                     `created_at`,
                     `updated_at`
         ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?, '.$quotedNow.', '.$quotedNow.');');

    if ($queryEvent === false) {
        echo 'failed to prepare event data into query';
        die;
    }

    // columns needs to be escaped by doublequotes
    $resultEvent = $queryEvent->execute([
        $_POST['event_slug'],
        $_POST['event_readable_name'],
        $_POST['event_account_number'],
        $_POST['event_prefix_variable_symbol'],
        $_POST['event_max_elapsed_payment_days'],
        $_POST['event_scarf_price'],
        $_POST['event_maximal_closed_ists_count'],
        $_POST['event_web_url'],
        $_POST['event_data_protection_url'],
        $_POST['event_ist_label'],
        $_POST['event_event_start'],
        $_POST['event_contact_email'],
    ]);

    if ($resultEvent === false) {
        echo 'failed to insert event data into database named '.$dbName;
        die;
    }

    $queryAdminstrator = $pdo->prepare('INSERT INTO `user` (
                    `email`,
                    `status`,
                    `created_at`,
                    `updated_at`,
                    `event_id`,
                    `role`
        ) VALUES (?, "open", '.$quotedNow.', '.$quotedNow.', "1", "admin")');

    if ($queryAdminstrator === false) {
        echo 'failed to prepare admininistrator data into query';
        die;
    }

    $resultAdministrator = $queryAdminstrator->execute([$_POST['administrator_email']]);

    if ($resultAdministrator === false) {
        echo 'failed to insert administrator data into database named '.$dbName;
        die;
    }

} catch (PDOException $e) {
    echo 'failed to run query on created database named '.$dbName.' with problem: '.$e->getMessage();
    die;
}

echo('Do not forget set mail settings correctly into .nev file!<br/>
<a href="'.$_POST['basepath'].'">done - continue to app</a>');
