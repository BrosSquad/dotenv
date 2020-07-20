<h1>dotenv</h1>

Simple PHP Dot env parser

[![Latest Stable Version](https://poser.pugx.org/dusan/dotenv/v)](//packagist.org/packages/dusan/dotenv)
[![Total Downloads](https://poser.pugx.org/dusan/dotenv/downloads)](//packagist.org/packages/dusan/dotenv)
[![License](https://poser.pugx.org/dusan/dotenv/license)](//packagist.org/packages/dusan/dotenv)

## Installation 
You can install the package through Composer.
```bash
composer require dusan/dotenv
```
## Features

1. Type Casting

| ENV type with examples                        | PHP type      |
| --------------------------------------------- |:-------------:|
| string         => ""                          | string        |
| numbers        => 1,0,1.0                     | int, floats   |
| empty values   => KEY=                        | null          |
| boolean        => true, false, yes, ok, no    | bool          |

- Disclaimer:
    * Everything else is treated as a string.
    ** To convert booleans to string, put the quotes ("") around them.
        -  eg. "true", "false", "ok"...
    *** 1,0 are never treated as boolean value, **ALWAYS** as an int.
    
2. Interpolation
    - Example:  
    
    APP_NAME="TestApp"
    OTHER="123 ${APP_NAME}" // prints out "123 TestApp"
    
    - Disclaimer:
        * Interpolation works previously defined variables.
        ** Interpolations works only in quoted strings.
        *** 
    

## Setup

First you need to instantiate ```EnvParser``` class with path to  driver to ```.env```:
```php
  use BrosSquad\DotEnv\EnvParser;
  
 // Instantiation of EnvParser
 $dotenv = new EnvParser('.env');

 // Parsing .env file
 $dotenv->parse();

 // Loading
 $dotenv->loadIntoENV(); // Loads into $_ENV
 $dotenv->loadUsingPutEnv(); // Loading environment variables using putenv()
```

## Advanced Usage

```php
 use BrosSquad\DotEnv\EnvParser;
  
 // Instantiation of EnvParser
 $dotenv = new EnvParser('.env');
 
 // Gets all env keys as an array.
 $getAllEnvsIntoArray = $dotenv->getEnvs();
```
## License
The dotenv package is open source software licensed under the [Apache License 2.0](https://github.com/BrosSquad/dotenv/blob/master/LICENSE)
