# Report Manager Plugin for CakePHP 1.3x  

## The report manager plugin can help users to create reports based on the application models.

## Installation  

1. Download the plugin from github or sourceforge  

http://sourceforge.net/projects/repomancakephp/  

https://github.com/luisdias/CakePHP-1.3x-Report-Manager-Plugin  

2. Extract the zip file on the app/plugins folder ( the plugin folder must be named report_manager )  

3. Go to the url http://mycakeapp/report_manager/reports to see the main page listing all models  


## Using the plugin  

The wizard interface is self explanatory.  

1. On the first tab you can select the fields and their position  

2. On the second tab you can define a filter  

3. On the third tab you can select up to two fields to sort  

4. On the last tab you can enter a name for your report and choose between 5 style options  


## Configuration:  

Some parameters could be configured in the app/plugins/report_manager/config/bootstrap.php  

* Display foreign keys  

* Ignore List for global fields ( affects all models )  

* Ignore list for models  

* Ignore list for specific model's fields  

* Reports directory path  


## Notes:  

It was inspired by the Report Creator Component by Gene Kelly from Nov 9th 2006.  

http://bakery.cakephp.org/articles/Gkelly/2006/11/09/report-creator-component  

It also uses a Jquery plugin called SmartWizard by Tech Laboratory.  

http://techlaboratory.net/products.php?product=smartwizard  

Since version 0.3 the Report Manager Plugin load the jQuery and jQuery UI libraries from Google  

Collaborators:  
Suman (USA)  
Santana (Brazil)  
Tamer Solieman (Egypt)  
jasonchua89  
Tony George (Singapore)  
Marduk netors (Mexico)  

Luis E. S. Dias  
Contact: smartbyte.systems@gmail.com