<style type="text/css">
    td {
        height: 58px !important;
    }
    .table-responsive {
        border-bottom: 1px solid #ddd;
        margin-bottom: 30px !important;
    }
</style>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-icon" data-background-color="orange">
                        <i class="material-icons">assignment</i>
                    </div>
                    <div class="card-content">
                        <h4 class="card-title">Groups Table</h4>
                        <div class="toolbar">
                            <!--        Here you can write extra buttons/actions for the toolbar              -->
                        </div>
                        <div class="material-datatables">
                            <table  class="table table-responsive" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Group</th>
                                        <th>Insurance</th>
                                        <th>Fields</th>
                                        <th class="disabled-sorting text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i=0; foreach ($group_data as $key => $data) { 
                                        $i++;
                                        $fields = $data['fields'];
                                    ?>
                                        <tr class="group-<?=$data['id']?>">
                                            <td rowspan="<?php echo count($data['insurance_ids'])?>"> <?=$i?> </td>
                                            <td class="title" rowspan="<?php echo count($data['insurance_ids'])?>"> <?=$data['group_name']?> </td>
                                            <td><?=$data['insurance_ids'][0]['title']?></td>
                                            <td><?php echo isset($fields[$data['insurance_ids'][0]['id']])? implode(', ', $fields[$data['insurance_ids'][0]['id']]) : '' ?> </td>
                                            <td class="td-actions text-center" rowspan="<?php echo count($data['insurance_ids'])?>">
                                                <a rel="tooltip" class="btn btn-success" href="<?=$data['link']?>">
                                                    <i class="material-icons">edit</i>
                                                </a>
                                                <a rel="tooltip" class="btn btn-danger del" data-id="<?=$data['id']?>">
                                                    <i class="material-icons">close</i>
                                                </a>
                                            </td>   
                                            <?php foreach ($data['insurance_ids'] as $key1 => $value) { 
                                                if ($key1 > 0) {
                                            ?>
                                                <tr class="group-<?=$data['id']?>">
                                                    <td><?php echo $value['title']; ?></td>
                                                    <td><?php echo isset($fields[$value['id']])?implode(', ', $fields[$value['id']]) : '' ?></td>
                                                </tr>
                                            <?php }} ?>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- end content-->
                </div>
                <!--  end card  -->
            </div>
            <!-- end col-md-12 -->
        </div>
        <!-- end row -->
    </div>
</div>

<script type="text/javascript">
var CSRF = '<?=$CSRF?>';
$('.del').on('click', function() {
    tr = $(this).parents('tr');
    id = $(this).data('id');
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
            $('.group-'+id).remove();
        }
    });
    return false;
});
</script>
</script>