<?php

declare(strict_types=1);
/**
 * This file is part of the codemagpie/array2object package.
 *
 * (c) CodeMagpie Lyf <https://github.com/codemagpie>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace CodeMagpie\ClassGeneratorTest;

use CodeMagpie\ClassGenerator\ClassGeneratorBuilder;
use CodeMagpie\ClassGenerator\Config;
use CodeMagpie\ClassGenerator\Constants\DeclareTypeModel;
use CodeMagpie\ClassGenerator\Constants\PropertyModel;
use CodeMagpie\ClassGeneratorTest\Stubs\DanceInterface;
use CodeMagpie\ClassGeneratorTest\Stubs\Person;
use CodeMagpie\ClassGeneratorTest\Stubs\PlayTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ClassGeneratorBuilderTest extends TestCase
{
    protected array $data;

    protected function setUp(): void
    {
        $this->data = [
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
        ];
    }

    public function testGen(): void
    {
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
        ClassGeneratorBuilder::create($config)->gen($this->data, 'tests/Stubs', 'Student');
        $student = new \CodeMagpie\ClassGeneratorTest\Stubs\Student();
        $ref = new \ReflectionClass($student);
        $this->releaseFile('Student');
        self::assertObjectHasAttribute('name', $student);
        self::assertObjectHasAttribute('chooseClasses', $student);
        self::assertEquals([PlayTrait::class], $ref->getTraitNames());
        self::assertEquals([DanceInterface::class], $ref->getInterfaceNames());
        self::assertEquals(Person::class, $ref->getParentClass()->getName());
        self::assertEquals('float', $ref->getProperty('score')->getType()->getName());
        self::assertEquals('array', $ref->getProperty('hobby')->getType()->getName());
        self::assertEquals('CodeMagpie\ClassGeneratorTest\Stubs\StudentNest\Teacher', $ref->getProperty('teacher')->getType()->getName());
        self::assertEquals('/**
     * @var ChooseClasses[]
     */', $ref->getProperty('chooseClasses')->getDocComment());
    }

    public function testGenFromOuterConfig(): void
    {
        $config = new Config(
            dirname(__DIR__), // project directory path
            DeclareTypeModel::OUTER, // default inner, outer: Will be in the class attribute comments above statement; inner: Inside the class declaration attributes
            PropertyModel::UNDERLINE, // default small_hump, underline: Properties of keys into underline; small_hump: Properties of keys into a small hump
            Person::class, // The generated object will extend the class
            [DanceInterface::class], // The generated object will implement this interfaces
            [PlayTrait::class], // The generated object will use this traits
            'Nest', // Nested objects directory suffix
            '', // default bash path .php-cs-fixer.php, cs-fixer config file
        );
        ClassGeneratorBuilder::create($config)->gen($this->data, 'tests/Stubs', 'Student1');
        $student = new \CodeMagpie\ClassGeneratorTest\Stubs\Student1();
        $ref = new \ReflectionClass($student);
        $this->releaseFile('Student1');
        self::assertEquals('/**
 * @property string name
 * @property int age
 * @property float score
 * @property Teacher teacher
 * @property array hobby
 * @property ChooseClasses[] choose_classes
 */', $ref->getDocComment());
    }

    protected function releaseFile(string $name): void
    {
        $bashPath = dirname(__DIR__);
        unlink($bashPath . DIRECTORY_SEPARATOR . "tests/Stubs/{$name}Nest/chooseClasses.php");
        unlink($bashPath . DIRECTORY_SEPARATOR . "tests/Stubs/{$name}Nest/Teacher.php");
        unlink($bashPath . DIRECTORY_SEPARATOR . "tests/Stubs/{$name}.php");
        rmdir($bashPath . DIRECTORY_SEPARATOR . "tests/Stubs/{$name}Nest");
    }
}
