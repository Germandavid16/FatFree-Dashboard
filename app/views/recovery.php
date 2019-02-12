<div class="login_page">
<? if ($h1_inner) { ?>
<h1><?=$h1_inner?></h1>
<? } ?>
<? if ($h2_inner) { ?>
<h2><?=$h2_inner?></h2>
<? } ?>
<form method="post">
    <input type="hidden" name="token" value="<?=$CSRF?>">
    <table class="formTable">
        <tr>
            <td class="label">
                <label for="login">E-mail or user name:</label>
            </td>
        </tr>
        <tr>
            <td class="value">
                <input type="text" name="login" id="login">                
            </td>
        </tr>
        <tr>
            <td class="label">
                <label for="login">Code:</label>
            </td>
        </tr>
        <tr>
            <td class="value">
                <input type="text" name="code" id="code">                
            </td>
        </tr>
        <tr>
            <td class="captcha">
                <img src="/captcha">
            </td>
        </tr>
        <tr>
            <td colspan="2" class="button">
               <button>Send</button>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="links">
                <a href="/login">Login page</a>
            </td>
        </tr>
    </table>
</form>
</div>
