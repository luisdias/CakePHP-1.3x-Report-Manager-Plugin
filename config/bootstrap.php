<?php
$config['ReportManager.displayForeignKeys'] = 0;
$config['ReportManager.globalFieldIgnoreList'] = array(
    'id',
    'created',
    'modified',
    'modified_by'
);
$config['ReportManager.modelIgnoreList'] = array(
    'AppModel',
    'MyModel2'
);
$config['ReportManager.modelFieldIgnoreList']=array(
    'MyModel' => array(
        'field1'=>'field1',
        'field2'=>'field2',
        'field3'=>'field3'
    )
);
$config['ReportManager.reportPath'] = 'tmp'.DS.'reports'.DS;
?>