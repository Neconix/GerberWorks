# GerberWorks
Helper classes for work with the gerber files.

**Supported**

* Standart aperture templates: circle, rectangle, obround;
* Aperture parameters (diam, hole diam, etc);
* D01, D02, D03 operations;
* Linear interpolation mode;
* Other stuff.

**Installation**

Add to your `composer.json` file:

```
  "repositories": [
     {
       "type": "vcs",
       "url": "https://github.com/Neconix/GerberWorks.git"
     }
   ],
   
   "require": {
     "neconix/gerberworks": "@dev"
   }
```

**Example**

```php
$gerber = new GerberEngine();
$gerber->Parse('pcb.gbr');
foreach ($gerber->Commands as $command) {
    echo $command;
}
```