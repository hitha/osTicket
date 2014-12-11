<?php

$tasks = Task::objects()
    ->select_related('dept', 'staff')
    ->order_by('-created');


$count = $tasks->count();
$pageNav = new Pagenate($count,1, 100000); //TODO: support ajax based pages
$showing = $pageNav->showing().' '._N('task', 'tasks', $count);

?>
<div id="tasks_content" style="display:block;">
<div style="width:700px; float:left;">
   <?php
    if ($count) {
        echo '<strong>'.$showing.'</strong>';
    } else {
        echo sprintf(__('%s does not have any tasks'), $ticket? 'This ticket' :
                'System');
    }
   ?>
</div>
<div style="float:right;text-align:right;padding-right:5px;">
    <?php
    if ($ticket) { ?>
        <a
        class="Icon newTicket task-action"
        data-url="tickets.php?id=<?php echo $ticket->getId(); ?>#tasks"
        data-dialog='{"size":"large"}'
        href="#tickets/<?php
            echo $ticket->getId(); ?>/add-task"> <?php
            print __('Add New Task'); ?></a>
    <?php
    } ?>
</div>
<br/>
<div>
<?php
if ($count) { ?>
<form action="#tickets/<?php echo $ticket->getId(); ?>/tasks" method="POST" name='tasks' style="padding-top:10px;">
<?php csrf_token(); ?>
 <input type="hidden" name="a" value="mass_process" >
 <input type="hidden" name="do" id="action" value="" >
 <table class="list" border="0" cellspacing="1" cellpadding="2" width="940">
    <thead>
        <tr>
            <?php
            //TODO: support mass actions.
            if (0) {?>
            <th width="8px">&nbsp;</th>
            <?php
            } ?>
            <th width="70"><?php echo __('Number'); ?></th>
            <th width="100"><?php echo __('Date'); ?></th>
            <th width="100"><?php echo __('Status'); ?></th>
            <th width="300"><?php echo __('Title'); ?></th>
            <th width="200"><?php echo __('Department'); ?></th>
            <th width="200"><?php echo __('Assignee'); ?></th>
        </tr>
    </thead>
    <tbody class="tasks">
    <?php
    foreach($tasks as $task) {
        $id = $task->getId();
        $assigned='';
        if ($task->staff)
            $assigned=sprintf('<span class="Icon staffAssigned">%s</span>',
                    Format::truncate($task->staff->getName(),40));

        $status = $task->isOpen() ? '<strong>open</strong>': 'closed';

        $title = Format::htmlchars(Format::truncate($task->getTitle(),40));
        $threadcount = $task->getThread() ?
            $task->getThread()->getNumEntries() : 0;
        ?>
        <tr id="<?php echo $id; ?>">
            <?php
            //Implement mass  action....if need be.
            if (0) { ?>
            <td align="center" class="nohover">
                <input class="ckb" type="checkbox" name="tids[]"
                value="<?php echo $id; ?>" <?php echo $sel?'checked="checked"':''; ?>>
            </td>
            <?php
            } ?>
            <td align="center" nowrap>
              <a class="Icon no-pjax preview"
                title="<?php echo __('Preview Task'); ?>"
                href="#tasks/<?php echo $id; ?>/view"
                data-preview="#tasks/<?php echo $id; ?>/preview"
                ><?php echo $task->getNumber(); ?></a></td>
            <td align="center" nowrap><?php echo
            Format::datetime($task->created); ?></td>
            <td><?php echo $status; ?></td>
            <td><a <?php if ($flag) { ?> class="no-pjax"
                    title="<?php echo ucfirst($flag); ?> Task" <?php } ?>
                    href="#tasks/<?php echo $id; ?>/view"><?php
                echo $title; ?></a>
                 <?php
                    if ($threadcount>1)
                        echo "<small>($threadcount)</small>&nbsp;".'<i
                            class="icon-fixed-width icon-comments-alt"></i>&nbsp;';
                    if ($row['collaborators'])
                        echo '<i class="icon-fixed-width icon-group faded"></i>&nbsp;';
                    if ($row['attachments'])
                        echo '<i class="icon-fixed-width icon-paperclip"></i>&nbsp;';
                ?>
            </td>
            <td><?php echo Format::truncate($task->dept->getName(), 40); ?></td>
            <td>&nbsp;<?php echo $assigned; ?></td>
        </tr>
   <?php
    }
    ?>
    </tbody>
</table>
</form>
<?php
 } ?>
</div>
</div>
<div id="task_content" style="display:none;">
</div>
<script type="text/javascript">
$(function() {
    $(document).on('click.tasks', 'tbody.tasks a, a#reload-task', function(e) {
        e.preventDefault();
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        var $container = $('div#task_content');
        $container.load(url, function () {
            $('.tip_box').remove();
            $('div#tasks_content').hide();
        }).show();
        return false;
     });
    $(document).off('.task-action');
    $(document).on('click.task-action', 'a.task-action', function(e) {
        e.preventDefault();
        var url = 'ajax.php/'
        +$(this).attr('href').substr(1)
        +'?_uid='+new Date().getTime();
        var $redirect = $(this).data('href');
        var $options = $(this).data('dialog');
        $.dialog(url, [201], function (xhr) {
            var tid = parseInt(xhr.responseText);
            if (tid) {
                var url = 'ajax.php/tasks/'+tid+'/view';
                var $container = $('div#task_content');
                $container.load(url, function () {
                    $('.tip_box').remove();
                    $('div#tasks_content').hide();
                }).show();
            } else {
                window.location.href = $redirect ? $redirect : window.location.href;
            }
        }, $options);
        return false;
    });


});
</script>