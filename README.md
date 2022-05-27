# Class Generator
## Introduction
A class generator, Support array to generate class files
## Installation
```shell
composer require codemagpie/class-generator --dev
```
## Usage
To use:
```php
$config = new Config(
            dirname(__DIR__), // project directory path
            DeclareTypeModel::INNER, // default inner, outer: Will be in the class attribute comments above statement; inner: Inside the class declaration attributes
            PropertyModel::SMALL_HUMP, // default small_hump, underline: Properties of keys into underline; small_hump: Properties of keys into a small hump
            Person::class, // The generated object will extend the class
            [DanceInterface::class], // The generated object will implement this interfaces
            [PlayTrait::class], // The generated object will use this traits
            'Nest', // Nested objects directory suffix
            '', // default bash path .php-cs-fixer.php, cs-fixer config file
        );
ClassGeneratorBuilder::create($config)->gen([
    'name' => 'test',
    'age' => 12,
    'score' => 80.5,
    'teacher' => [
         'name' => 'wang',
     ],
     'hobby' => ['ping-pong'],
     'choose_classes' => [[
         'name' => 'math',
     ]],
], 'src/Model', 'Student');
```
Result:

![](https://tva1.sinaimg.cn/large/e6c9d24egy1h2n40c1nb4j20q60beq3q.jpg)
![](https://tva1.sinaimg.cn/large/e6c9d24egy1h2n41xqqw8j211u0u0q5a.jpg)