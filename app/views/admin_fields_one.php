<div class="pageBlock">
    <div class="blockTitle">Add aliases<i class="fa fa-plus" aria-hidden="true"></i></div>
    <div class="blockContent">
        <div class="blockDescription">List of aliases, a new line for each alias. The case of letters does not matter. Between words can be any number of any whitespace characters.</div>
        <form method="POST">
            <input type="hidden" name="token" value="<?=$CSRF?>">
            <div class="formField">
                <textarea name="aliases"></textarea>
            </div>
            <div class="button">
                <button>Add</button>
            </div>
        </form>
    </div>
</div>
<? if ($rows) { ?>
<div class="dontAskConfirmation">
    <input type="checkbox" id="dontAskConfirm">
    <label for="dontAskConfirm">Do not ask confirmation</label>
</div>
<form method="POST" id="formMain">
<input type="hidden" name="token" value="<?=$CSRF?>">
<table class="dataTable">
    <tr class="head">
        <td>
            Title
        </td>
        <td>
            Main
        </td>
        <td>
            
        </td>
    </tr>
<? foreach ($rows as $row) { ?>    
    <tr data-id="<?=$row['id']?>">
        <td>
            <span class="title"><?=$row['title']?></span> 
        </td>
        <td>
            <input type="radio" name="main" value="<?=$row['id']?>"<?if ($row['id'] == $main) {?> checked<?}?>>
        </td>
        <td>
            <a href="#" class="del"><i class="fa fa-times" aria-hidden="true"></i></a>
        </td>
    </tr>
<? } ?>
</table>
</form>
<script>
var CSRF = '<?=$CSRF?>';
$('tr[data-id="<?=$main?>"] .del').hide();
$('#formMain input[name="main"]').on('change', function() {
    if ($(this).prop('checked')) {
        del = $(this).parents('tr').find('.del');
        data = $(this).parents('form').serialize();
        del.hide();
        $.ajax({
            url: '',
            dataType: 'json',
            method: 'POST',
            data: data
        }).done(function(page) {
            $('input[name="token"]').val(page['CSRF']);
            CSRF = page['CSRF'];
            $('.del').show();
            del.hide();
        });
    }
});
$('.del').on('click', function() {
    tr = $(this).parents('tr');
    id = tr.data('id');
    name = tr.find('.title').text();
    askconfirm = !$('#dontAskConfirm').prop('checked');
    if (askconfirm && !confirm('Are you sure you want to delete alias "'+name+'"?')) {
        return false;
    }    
    $.ajax({
        url: '',
        dataType: 'json',
        method: 'POST',
        data: 'delete='+id+'&token='+CSRF,
    }).done(function(page) {
        $('input[name="token"]').val(page['CSRF']);
        CSRF = page['CSRF'];
        if (page['deleted']) {
            tr.remove();
        }
    });
    return false;
});
</script>
<? } ?>
