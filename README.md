# PHP CLI/Terminal library #

## PHP CLI/Terminal for color, blink and format output messages ##

Installation and Usage
```
require srknzcn/terminal package in your composer.json file
```

#### Usage: ####
```php
<?php

include "./vendor/autoload.php";

use CLI\Terminal;

// simple output
Terminal::writeln("Hello World");

// colorize the output
Terminal::writeln("Hello World", "red");

// blink parameter flashes the output
Terminal::writeln("Hello World", "yellow", "blink");

// bold, underline and color 
Terminal::writeln("Hello World", "cyan", "bold", "underline");

// changing of parameeters order does't matter
Terminal::writeln("Hello World", "bold", "blink" "cyan", "underline");

// only print message to output. don't add new line
Terminal::write("Hello World");

// kills the script and prints message to output
Terminal::dieln("I'm died :(", "yellow", "underline);

```

#### Formatter attributes: #####
* bold
* dark
* faint
* underline
* underscore
* blink
* reverse


#### Color attributes: ####
* black
* red
* green
* yellow
* blue
* magenta
* cyan
* white
* brightblack
* brightred
* brightgreen
* brightyellow
* brightblue
* brightmagenta
* brightcyan
* brightwhite

#### Background color attributes: ####
* onblack
* onred
* ongreen
* onyellow
* onblue
* onmagenta
* oncyan
* onwhite
* onbrightblack
* onbrightred
* onbrightgreen
* onbrightyellow
* onbrightblue
* onbrightmagenta
* onbrightcyan
* onbrightwhite