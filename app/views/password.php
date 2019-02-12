<form method="post">
   <input type="hidden" name="token" value="<?=$CSRF?>">
   <table class="formTable">
<? if (!isset($passwordEmpty)) { ?>
        <tr>
            <td class="label">
                <label for="password_old">Current password:</label>
            </td>
            <td class="value">
                <input type="password" name="password_old" id="password_old">                
            </td>
        </tr>
<? } ?>        
        <tr>
            <td class="label">
                <label for="password_new">New password:</label>
            </td>
            <td class="value">
                <input type="password" name="password_new" id="password_new">                
            </td>
        </tr>
        <tr>
            <td class="label">
                <label for="password_confirm">Confirm new password:</label>
            </td>
            <td class="value">
                <input type="password" name="password_confirm" id="password_confirm">                
            </td>
        </tr>
        <tr>
            <td colspan="2" class="button">
               <button>Send</button>
            </td>
        </tr>
   </table>
</form>