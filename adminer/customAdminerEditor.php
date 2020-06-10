<?php

function adminer_object() {

    class AdminerSoftware extends Adminer {

        function name() {
            // custom name in title and heading
            return $_ENV['APP_NAME'];
        }


        function loginForm() {
            ?>
			<table cellspacing="0">
				<tr>
					<th><?php echo lang('Username'); ?>
					<td><input type="hidden" name="auth[driver]" value="sqlite"><input name="auth[username]"
																					   id="username"
																					   value="<?php echo h($_GET["username"]); ?>"
																					   autocapitalize="off">
				<tr>
					<th><?php echo lang('Password'); ?>
					<td><input type="password" name="auth[password]">
			</table>
			<script type="text/javascript">
				focus(document.getElementById('username'));
			</script>
            <?php
            echo "<p><input type='submit' value='".lang('Login')."'>\n";
            echo checkbox("auth[permanent]", 1, $_COOKIE["adminer_permanent"], lang('Permanent login'))."\n";
        }

        function permanentLogin($mb = false) {
            // key used for permanent login
            return "401d3b281fdd4977f3ad305fac2f465a";
        }

        function credentials() {
            return array ();
        }

        function database() {
            return __DIR__.'/../src/db_dev.sqlite';
        }

        function login($login, $password) {
            // validate user submitted credentials
            global $adminerSettings;
            return ($login === $_ENV['ADMINER_LOGIN'] && $password === $_ENV['ADMINER_PASSWORD']);
        }

        function tableName($tableStatus) {
            // tables without comments would return empty string and will be ignored by Adminer
            return h($tableStatus["Name"]);
        }

        function fieldName($field, $order = 0) {
            // only columns with comments will be displayed and only the first five in select
            return $field["field"];
            // return ($order <= 5 && !preg_match('~_(md5|sha1)$~', $field["field"]) ? h($field["comment"]) : "");
        }

    }

    return new AdminerSoftware();
}

include __DIR__.'/lib/editor-4.7.1.php';
