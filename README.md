# GerberWorks
Helper classes for work with the gerber files.

**Supported**

* Standart aperture templates: circle, rectangle, obround;
* Aperture parameters (diam, hole diam, etc);
* D01, D02, D03 operations;
* Linear interpolation mode;
* Other stuff.

**Installation**

Either run

`$ php composer.phar require Neconix/GerberWorks "*"`

or add

`"Neconix/GerberWorks": "*"`

to the require section of your `composer.json` file.

**Example**

```php
$gerber = new GerberFile();
$gerber->Parse('pcb.gbr');
foreach ($gerber->Commands as $command) {
    echo $command;
}
```