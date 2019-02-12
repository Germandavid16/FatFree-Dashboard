<? if ($rows) { ?>
<table class="dataTable">
<? foreach ($rows as $row) { ?>    
    <tr data-id="<?=$row['id']?>">
        <td class="title">
            <?=$row['title']?>
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
    name = tr.find('.title').text().trim();
    if (!confirm('Are you sure you want to delete insurance "'+name+'" and all linked data?')) {
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
            tr.hide(400);
            tr.remove();
        }
    });
    return false;
});
</script>
<? } ?>

