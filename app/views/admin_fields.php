<div class="pageBlock">
    <div class="blockTitle">Add fields<i class="fa fa-plus" aria-hidden="true"></i></div>
    <div class="blockContent">
        <div class="blockDescription">List of fields, a new line for each field. The case of letters does not matter. Between words can be any number of any whitespace characters. Key field with name or alias "MEMBER_ID" must exist</div>
        <form method="POST">
            <input type="hidden" name="token" value="<?=$CSRF?>">
            <div class="formField">
                <textarea name="fields"></textarea>
            </div>
            <div class="button">
                <button>Add</button>
            </div>
        </form>
    </div>
</div>
<? if ($rows) { ?>
<div class="dontAskConfirmation">
<input type="checkbox" id="dontAskConfirm"<?if($dontAskConfirm){?> checked<?}?>>
<label for="dontAskConfirm">Do not ask confirmation</label>
</div>
<table class="dataTable">
    <tr class="head">
        <td>Order</td>
        <td>Title</td>
        <td title="Grouping">G</td>
        <td></td>
        <td></td>
    </tr>
<? foreach ($rows as $row) { ?>    
    <tr data-id="<?=$row['id']?>">
        <td>
            <select class="position" name="position" size="1" autocomplete="off">
<? for ($i = 1; $i <= count($rows); $i++) { ?>
                <option value="<?=$i?>"<? if ($row['position'] == $i) { ?> selected<?}?>><?=$i?></option>
<? } ?>
            <select>
        </td>
        <td>
            <span class="title"><?=$row['title']?></span> 
        </td>
        <td>
            <input type="checkbox" value="1" class="grouping"<?if($row['grouping']){?> checked<?}?>>
        </td>
        <td>
            <a href="<?=$row['link']?>" title="edit" class="edit"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
        </td>
        <td>
            <a href="#" class="del" title="delete"><i class="fa fa-times" aria-hidden="true"></i></a>
        </td>
    </tr>
<? } ?>    
</table>
<script>
var CSRF = '<?=$CSRF?>';
$('.del').on('click', function() {
    tr = $(this).parents('tr');
    id = tr.data('id');
    name = tr.find('.title').text();
    if ($('#dontAskConfirm').prop('checked')) {
        dontAskConfirm = 1;
    } else {
        dontAskConfirm = 0;
    }
    if (!dontAskConfirm && !confirm('Are you sure you want to delete field "'+name+'" and all linked data?')) {
        return false;
    }
    $.ajax({
        url: '',
        dataType: 'json',
        method: 'POST',
        data: 'delete='+id+'&dontAskConfirm='+dontAskConfirm+'&token='+CSRF,
    }).done(function(page) {
        $('input[name="token"]').val(page['CSRF']);
        CSRF = page['CSRF'];
        if (page['deleted']) {
            tr.hide(400);
            tr.remove();
            location.reload();
        }
    });
    return false;
});

$('.position').on('change', function() {
    tr = $(this).parents('tr');
    id = tr.data('id');
    pos = $(this).val();
    if ($('#dontAskConfirm').prop('checked')) {
        dontAskConfirm = 1;
    } else {
        dontAskConfirm = 0;
    }
    $.ajax({
        url: '',
        dataType: 'json',
        method: 'POST',
        data: 'position='+pos+'&id='+id+'&dontAskConfirm='+dontAskConfirm+'&token='+CSRF,
    }).done(function(page) {
        $('input[name="token"]').val(page['CSRF']);
        CSRF = page['CSRF'];
        if (page['positionChanged']) {
            
        }
        location.reload();
    });
});

$('.grouping').on('change', function() {
    input = $(this);
    tr = input.parents('tr');
    id = tr.data('id');
    state = 0;
    if (input.prop('checked')) {
        state = 1;
    }
    input.prop('disabled', true);
    $.ajax({
        url: '',
        dataType: 'json',
        method: 'POST',
        data: 'grouping='+id+'&state='+state+'&token='+CSRF,
    }).done(function(page) {
        $('input[name="token"]').val(page['CSRF']);
        CSRF = page['CSRF'];
        input.prop('disabled', false);
    });
});    

</script>
<? } ?>

