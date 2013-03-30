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

$config['ReportManager.labelFieldList']=array(
    '*' => array(
        'field1'=>'my field 1 label description',
        'field2'=>'my field 2 label description',
        'field3'=>'my field 3 label description'
    ),
    'MyModel' => array(
        'field1' => 'my MyModel field 1 label description'
    )
);

?>