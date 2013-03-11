<?php
/*
Copyright (c) 2012 Luis E. S. Dias - www.smartbyte.com.br

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
?>
<script type="text/javascript">
    firstLevel = "<?php echo Router::url('/'); ?>";
</script>
<?php

?>
<?php echo $this->Html->script(array('https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js')); ?>
<?php echo $this->Html->script('/ReportManager/js/index.js'); ?>
<?php echo $this->Html->css('/ReportManager/css/report_manager'); ?>
<div class="reportManager index">
    <h2><?php echo __('Report Manager',true);?></h2>
    <?php
        
        echo '<div id="repoManLeftCol">';
        echo $this->Form->create();
        echo '<fieldset>';
        echo '<legend>' . __('New report',true) . '</legend>';        
        echo $this->Form->input('model',array(
            'type'=>'select',            
            'label'=>__('Model',true),
            'options'=>$models,
            'empty'=>__('--Select--',true)
            ));
        
        echo '<div id="ReportManagerOneToManyOption">';
        echo $this->Form->input('one_to_many_option',array(
            'type'=>'select',
            'label'=>__('One to many option',true),
            'options'=>array(),
            'empty'=>__('<None>',true)
            ));
        echo '</div>';
        echo $this->Form->input('new',array(
            'type'=>'hidden',
            'value'=>'1'
            ));        
        echo '</fieldset>';
        echo $this->Form->submit(__('New',true),array('name'=>'new'));
        echo $this->Form->end();
        echo '</div>';
        
        echo '<div id="repoManMiddleCol">';
        
        echo $this->Html->tag('h2','OR');
        
        echo '</div>';
        
        echo '<div id="repoManRightCol">';
        echo $this->Form->create();
        echo '<fieldset>';
        echo '<legend>' . __('Load report',true) . '</legend>';        
        
        echo '<div id="ReportManagerSavedReportOptionContainer">';
        echo $this->Form->input('saved_report_option',array(
            'type'=>'select',
            'label'=>__('Saved reports',true),
            'options'=>$files,
            'empty'=>__('--Select--',true)
            ));
        echo '</div>';
        echo $this->Form->input('load',array(
            'type'=>'hidden',
            'value'=>'1'
            ));        
        echo '<button type="button" class="deleteReport">Delete</button>';
        echo '</fieldset>';
        echo $this->Form->submit(__('Load',true),array('name'=>'load'));
        echo $this->Form->end();
        echo '</div>';
    ?>
</div>