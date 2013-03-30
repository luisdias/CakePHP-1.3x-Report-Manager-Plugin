<?php
/*
The MIT License
Copyright (c) 2013 Luis E. S. Dias - smartbyte.systems@gmail.com

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

class ReportsController extends AppController {
    public $name = 'Reports';
    public $uses = array();
    public $helpers = array('Number');
    public $path = null;
    public $components = array('RequestHandler');            
    
    public function __construct( $request = NULL, $response = NULL ) {
        Configure::load('report_manager.bootstrap');        
        $reportPath = Configure::read('ReportManager.reportPath');
        if ( !isset($reportPath) )
            $reportPath = 'tmp'.DS.'reports'.DS;
        $this->path = $reportPath;
        if(!is_dir(APP.$this->path)) {
            $folder = new Folder();
            $folder->create(APP.$this->path);
        }
        parent::__construct($request,$response);
    }    
    
    public function index() {
        if (empty($this->data)) {
            $modelIgnoreList = Configure::read('ReportManager.modelIgnoreList'); 

            $models = App::objects('model');
            $models = array_combine($models,$models);
            
            if ( isset($modelIgnoreList) && is_array($modelIgnoreList)) {
                foreach ($modelIgnoreList as $model) {
                    if (isset($models[$model]));
                        unset($models[$model]);
                }                
            }
            $this->set('files',$this->listReports());
            $this->set('models',$models);
        } else {
            if (isset($this->data['new'])) {
                $reportButton = 'new';
                $modelClass = $this->data['model'];
                $oneToManyOption = $this->data['one_to_many_option'];
                $this->redirect(array('action'=>'wizard',$reportButton, $modelClass, $oneToManyOption));
            }
                
            if (isset($this->data['load'])) {
                $reportButton = 'load';
                $fileName = $this->data['saved_report_option'];
                $this->redirect(array('action'=>'wizard',$reportButton, urlencode($fileName)));                
            }
                
            $this->redirect(array('action'=>'index'));
        }
    }
    
    public function ajaxGetOneToManyOptions() {
        if ($this->RequestHandler->isAjax()) {

            Configure::write('debug',0);
            $this->autoRender = false;
            $this->layout = null;

            $modelClass = $_POST["model"];
            $this->loadModel($modelClass);
            $associatedModels = $this->{$modelClass}->getAssociated('hasMany');
            $associatedModels = array_combine($associatedModels, $associatedModels);

            $modelIgnoreList = Configure::read('ReportManager.modelIgnoreList');
            if ( isset($modelIgnoreList) && is_array($modelIgnoreList)) {
                foreach ($modelIgnoreList as $model) {
                    if (isset($associatedModels[$model]));
                        unset($associatedModels[$model]);
                }                
            }            
            
            $this->set('associatedModels',$associatedModels);
            $this->render('list_one_to_many_options');
        }
    }

    // calculate the html table columns width
    public function getTableColumnWidth($fieldsLength=array(),$fieldsType=array()) {
        $minWidth = 4;
        $maxWidth = 50;
        $tableColumnWidth = array();
        foreach ($fieldsLength as $field => $length): 
            if ( $length != '') {
                if ( $length < $maxWidth ) 
                    $width = $length * 9;
                else
                    $width = $maxWidth * 9;
                if ( $length < $minWidth ) 
                    $width = $length * 40;                
                $tableColumnWidth[$field] = $width;
            } else {
                $fieldType = $fieldsType[$field];
                switch ($fieldType) {
                    case "date":
                        $width = 120;
                        break;
                    case "float":
                        $width = 150;
                        break;                
                    default:
                        $width = 120;
                        break;
                }
                $tableColumnWidth[$field] = $width;
            }
        endforeach; 
        return $tableColumnWidth;
    }
    
    // calculate the html table width
    public function getTableWidth($tableColumnWidth = array()) {
        $tableWidth = array_sum($tableColumnWidth);
        return $tableWidth;
    }

    public function export2Xls(&$reportData = array(),&$fieldsList=array(), &$fieldsType=array(), &$oneToManyOption=null, &$oneToManyFieldsList=null, &$oneToManyFieldsType = null, &$showNoRelated = false ) {
        App::import('Vendor', 'ReportManager.Excel',array('file'=>'excel.php'));
        $xls = new Excel();      
        $xls->buildXls($reportData,$fieldsList, $fieldsType, $oneToManyOption, $oneToManyFieldsList, $oneToManyFieldsType, $showNoRelated );
    }
 
    public function saveReport($modelClass = null,$oneToManyOption = null) {
        $content='<?php $reportFields=';
        $content.= var_export($this->data,1);
        $content.='; ?>'; 
        
        if ($this->data[$modelClass]['ReportName'] != '') {
            $reportName = str_replace('.', '_', $this->data[$modelClass]['ReportName']);
            $reportName = str_replace(' ', '_', $this->data[$modelClass]['ReportName']);
        } else {
            $reportName = date('Ymd_His');
        }
        
        $oneToManyOption = ( $oneToManyOption == '' ? $oneToManyOption : $oneToManyOption . '.' );
        $fileName = $modelClass . '.' . $oneToManyOption . $reportName.".crp";
        $file = new File(APP.$this->path.$fileName, true, 777);
        $file->write($content,'w',true);
        $file->close();
    }

    public function loadReport($fileName) {
        require(APP.$this->path.$fileName);
        $this->data = $reportFields;
        $this->set($this->data);
    }

    public function deleteReport($fileName) {
        if ($this->RequestHandler->isAjax()) {
            Configure::write('debug',0);
            $this->autoRender = false;
            $this->layout = null;
            
            $fileName = APP.$this->path.$fileName;
            $file = new File($fileName, false, 777);
            $file->delete();
            $this->set('files',$this->listReports());
            $this->render('list_reports');
        }
    }

    public function listReports() {
        $dir = new Folder(APP.$this->path);
        $files = $dir->find('.*\.crp');
        if (count($files)>0)
            $files = array_combine($files,$files);        
        return $files;
    }


    public function wizard($param1 = null,$param2 = null, $param3 = null) {
        if (is_null($param1) || is_null($param2)) {
            $this->Session->setFlash(__('Please select a model or a saved report',true));
            $this->redirect(array('action'=>'index'));
        }
        
        $reportAction = $param1;
        $modelClass = null;
        $oneToManyOption = null;
        $fileName = null;
        
        if ( $reportAction == "new" ) {
            $modelClass = $param2;
            $oneToManyOption = $param3;
        }
        
        if ( $reportAction == "load" ) {
            $fileName = urldecode($param2);            

            if ($fileName!='') {
                $params = explode('.', $fileName);
                if (count($params)>=3) {
                    $modelClass = $params[0];
                    if (count($params)>3) {
                        $oneToManyOption = $params[1];
                    } 
                }
            }             
        }
        
        $labelFieldList = Configure::read('ReportManager.labelFieldList');
        $this->set('labelFieldList',$labelFieldList);
        
        if (empty($this->data)) {        
            $displayForeignKeys = Configure::read('ReportManager.displayForeignKeys');
            $globalFieldIgnoreList = Configure::read('ReportManager.globalFieldIgnoreList');
            $modelFieldIgnoreList = Configure::read('ReportManager.modelFieldIgnoreList');

            $this->loadModel($modelClass);
            $modelSchema = $this->{$modelClass}->schema();
            
            if (isset($globalFieldIgnoreList) && is_array($globalFieldIgnoreList)) {
                foreach ($globalFieldIgnoreList as $field) {
                    unset($modelSchema[$field]);
                }                
            }
            
            if (isset($displayForeignKeys) && !$displayForeignKeys) {               
                foreach($modelSchema as $field => $value) {
                    if ( substr($field,-3)=='_id' )
                        unset($modelSchema[$field]);
                }
            }
            
            $associatedModels = $this->{$modelClass}->getAssociated();
            $associatedModelsSchema = array();

            foreach ($associatedModels as $key => $value) {
                $className = $this->{$modelClass}->{$value}[$key]['className'];
                //$this->loadModel($key);
                $this->loadModel($className);
                //$associatedModelsSchema[$key] = $this->{$key}->schema();
                $associatedModelsSchema[$key] = $this->{$className}->schema();

                if (isset($globalFieldIgnoreList) && is_array($globalFieldIgnoreList)) {
                    foreach ($globalFieldIgnoreList as $value) {
                        unset($associatedModelsSchema[$key][$value]);
                    }
                }
                
                if (isset($displayForeignKeys) && !$displayForeignKeys) {
                    foreach($associatedModelsSchema as $model => $fields) {
                        foreach($fields as $field => $values) {
                            if ( substr($field,-3)=='_id' )
                                unset($associatedModelsSchema[$model][$field]);                            
                        }
                    }
                }
                foreach($associatedModelsSchema as $model => $fields) {
                    foreach($fields as $field => $values) {
                        if ( isset($modelFieldIgnoreList[$model][$field]) )
                            unset($associatedModelsSchema[$model][$field]);                            
                    }
                }                
            }

            $this->set('modelClass',$modelClass);
            $this->set('modelSchema',$modelSchema);
            $this->set('associatedModels',$associatedModels);
            $this->set('associatedModelsSchema',$associatedModelsSchema);
            $this->set('oneToManyOption',$oneToManyOption);
            
            if (!is_null($fileName))
                $this->loadReport($fileName);

        } else {
            $this->loadModel($modelClass);
            $associatedModels = $this->{$modelClass}->getAssociated();
            $oneToManyOption = $this->data[$modelClass]['OneToManyOption'];
            
            $fieldsList = array();
            $fieldsPosition = array();
            $fieldsType = array();
            $fieldsLength = array();
            
            $conditions = array();
            $conditionsList = array();
            
            $oneToManyFieldsList  = array();
            $oneToManyFieldsPosition  = array();
            $oneToManyFieldsType  = array();
            $oneToManyFieldsLength = array();
            
            foreach ($this->data  as $model => $fields) {
                if ( is_array($fields) ) {
                    foreach ($fields  as $field => $parameters) {
                        if ( is_array($parameters) ) {                          
                            if ( (isset($associatedModels[$model]) && 
                                    $associatedModels[$model]!='hasMany') || 
                                    ($modelClass == $model) 
                                ) {
                                if ( $parameters['Add'] ) {
                                    $fieldsPosition[$model.'.'.$field] = ( $parameters['Position']!='' ? $parameters['Position'] : 0 );
                                    $fieldsType[$model.'.'.$field] = $parameters['Type'];
                                    $fieldsLength[$model.'.'.$field] = $parameters['Length'];
                                }
                                $criteria = '';                                    
                                if ($parameters['Example'] != '' && $parameters['Filter']!='null' ) {
                                    if ( $parameters['Not'] ) {
                                        switch ($parameters['Filter']) {
                                            case '=':
                                                $criteria .= ' !'.$parameters['Filter'];
                                                break;
                                            case 'LIKE':
                                                $criteria .= ' NOT '.$parameters['Filter'];
                                                break;
                                            case '>':
                                                $criteria .= ' <=';
                                                break;
                                            case '<':
                                                $criteria .= ' >=';
                                                break;
                                            case '>=':
                                                $criteria .= ' <';
                                                break;
                                            case '<=':
                                                $criteria .= ' >';
                                                break;
                                            case 'null':
                                                $criteria = ' !=';
                                                break;
                                        }
                                    } else {
                                        if ($parameters['Filter']!='=') 
                                            $criteria .= ' '.$parameters['Filter'];
                                    }

                                    if ($parameters['Filter']=='LIKE')
                                        //$example = '%'. mysql_real_escape_string($parameters['Example']) . '%';
                                        $example = '%'.$parameters['Example'] . '%';
                                    else
                                        //$example = mysql_real_escape_string($parameters['Example']);
                                        $example = $parameters['Example'];

                                    $conditionsList[$model.'.'.$field.$criteria] = $example;
                                }
                                if ( $parameters['Filter']=='null' ) {
                                    $conditionsList[$model.'.'.$field.$criteria] = null;                                        
                                }
                            }
                            // One to many reports
                            if ( $oneToManyOption != '') {
                                if ( isset($parameters['Add']) && $model == $oneToManyOption ) {
                                    $oneToManyFieldsPosition[$model.'.'.$field] = ( $parameters['Position']!='' ? $parameters['Position'] : 0 );
                                    $oneToManyFieldsType[$model.'.'.$field] = $parameters['Type'];
                                    $oneToManyFieldsLength[$model.'.'.$field] = $parameters['Length'];
                                }                                    
                            }

                        } // is array parameters
                    } // foreach field => parameters
                    if (count($conditionsList)>0) {
                        $conditions[$this->data[$modelClass]['Logical']] = $conditionsList;
                    }
                } // is array fields
            } // foreach model => fields
            asort($fieldsPosition);
            $fieldsList = array_keys($fieldsPosition);
            $order = array();
            if ( isset($this->data[$modelClass]['OrderBy1']) )
                $order[] = $this->data[$modelClass]['OrderBy1'] . ' ' . $this->data[$modelClass]['OrderDirection'];
            if ( isset($this->data[$modelClass]['OrderBy2']) )
                $order[] = $this->data[$modelClass]['OrderBy2'] . ' ' . $this->data[$modelClass]['OrderDirection'];
            
            $tableColumnWidth = $this->getTableColumnWidth($fieldsLength,$fieldsType);
            $tableWidth = $this->getTableWidth($tableColumnWidth);
            
            if ($oneToManyOption == '') {
                $recursive = 0;
                $showNoRelated = false;
            } else {
                $oneToManyTableColumnWidth = $this->getTableColumnWidth($oneToManyFieldsLength,$oneToManyFieldsType);
                $oneToManyTableWidth = $this->getTableWidth($oneToManyTableColumnWidth);                
                asort($oneToManyFieldsPosition);
                $oneToManyFieldsList = array_keys($oneToManyFieldsPosition);
                $showNoRelated = $this->data[$modelClass]['ShowNoRelated'];
                $recursive = 1;
            }
            
            $reportData = $this->{$modelClass}->find('all',array(
                'recursive'=>$recursive,
                'fields'=>$fieldsList,
                'order'=>$order,
                'conditions'=>$conditions
            ));
            
            $this->layout = 'report';
                        
            $this->set('tableColumnWidth',$tableColumnWidth);
            $this->set('tableWidth',$tableWidth);
            
            $this->set('fieldList',$fieldsList);
            $this->set('fieldsType',$fieldsType);
            $this->set('fieldsLength',$fieldsLength);
            $this->set('reportData',$reportData);
            $this->set('reportName',$this->data[$modelClass]['ReportName']);
            $this->set('reportStyle',$this->data[$modelClass]['Style']);
            $this->set('showRecordCounter',$this->data[$modelClass]['ShowRecordCounter']);

            if ( $this->data[$modelClass]['Output'] == 'html') {
                if ($oneToManyOption == '')
                    $this->render('report_display');
                else {
                    $this->set('oneToManyOption',$oneToManyOption);
                    $this->set('oneToManyFieldsList',$oneToManyFieldsList);
                    $this->set('oneToManyFieldsType',$oneToManyFieldsType);
                    $this->set('oneToManyTableColumnWidth',$oneToManyTableColumnWidth);
                    $this->set('oneToManyTableWidth',$oneToManyTableWidth);
                    $this->set('showNoRelated',$showNoRelated);
                    $this->render('report_display_one_to_many');
                }
            } else { // Excel file
                $this->layout = null;
                $this->export2Xls(
                        $reportData, 
                        $fieldsList, 
                        $fieldsType, 
                        $oneToManyOption, 
                        $oneToManyFieldsList, 
                        $oneToManyFieldsType, 
                        $showNoRelated );
            }
            if ($this->data[$modelClass]['SaveReport'])
                $this->saveReport($modelClass,$oneToManyOption);
        }
    }
}