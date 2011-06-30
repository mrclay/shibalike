<?php
/**
 * All you need for an IdP is an authentication process and a way to get user
 * attributes. Once you trust the identity of the user, you mark them as authenticated,
 * which fetches and stores their attributes in the state manager.
 */

require '_inc.php';
$idp = new Shibalike\IdP(getStateManager(), getAttrStore(), getConfig());

// crude authentication
if (! empty($_POST)) {
    // perform auth
    
    $username = '';
    if (in_array($_POST['username'], array('jadmin', 'juser'))) {
        if ($_POST['username'] === $_POST['password']) {
            $username = $_POST['username'];
        }
    } else {
        if ($_POST['password'] == 'password1') {
            $username = $_POST['username'];
        }
    }
    $authenticatedSuccessfully = ! empty($username);
    
    // try authentication somehow (e.g. using Zend_Auth)
    if ($authenticatedSuccessfully) {
        $userAttrs = $idp->fetchAttrs($username);
        if ($userAttrs) {
            $idp->markAsAuthenticated($username);
            $idp->redirect();
        } else {
            // user is not in attr store!
            header('Content-Type: text/html;charset=utf-8');
            echo "Sorry. You're not in the attribute store. <a href='idp.php'>Try again</a>";
            die();
        }
    } else {
        // user failed authenticate!
        header('Content-Type: text/html;charset=utf-8');
        echo "Sorry. You failed to authenticate. <a href='idp.php'>Try again</a>";
        die();
    }
    
    
} else {
    // show form
    header('Content-Type: text/html;charset=utf-8');
    ?>
<form action="" method="post">
    <dl>
        <dt>Username</dt><dd><input size="20" name="username"></dd>
        <dt>Password</dt><dd><input size="20" name="password" type="password"></dd>
    </dl>
    <p><input type="submit" value="Login"></p>
</form>
<hr>
<p>Try different users, and bad passwords:</p>
<table border="1">
    <tr><th>username</th><th>password</th></tr>
    <tr><td>jadmin</td><td>jadmin</td></tr>
    <tr><td>juser</td><td>juser</td></tr>
    <tr><td>*anyuser*</td><td>password1</td></tr>
</table>
<?php
}

