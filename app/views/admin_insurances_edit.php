<form method="POST">
    <input type="hidden" name="token" value="<?=$CSRF?>">
    <table class="formTable">
        <tr>
            <td class="label">
                <label>Title:</label>
            </td>
            <td class="value">
                <input type="text" name="title" value="<?=$item_title?>">
            </td>
        </tr>
        <tr>
            <td colspan="2" class="button">
               <button>Send</button>
            </td>
        </tr>
    </table>
</form>

