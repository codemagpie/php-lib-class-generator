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
namespace CodeMagpie\ClassGenerator;

use CodeMagpie\ClassGenerator\Constants\DeclareTypeModel;
use CodeMagpie\ClassGenerator\Constants\PropertyModel;
use CodeMagpie\ClassGenerator\Exception\ClassGeneratorException;
use CodeMagpie\Utils\Utils;
use PhpCsFixer\Console\Application;
use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class ClassGeneratorBuilder
{
    protected Config $config;

    public static function create(Config $config): ClassGeneratorBuilder
    {
        $than = new static();
        $than->config = $config;
        return $than;
    }

    public function gen(array $data, string $path, string $className): void
    {
        $project = new Project();
        $namespace = $project->namespace($path);
        $file = BASE_PATH . DIRECTORY_SEPARATOR . $project->path($namespace . $className);
        if (file_exists($file)) {
            throw new ClassGeneratorException(sprintf('The file %s already exists', $file));
        }
        $absolutePath = BASE_PATH . DIRECTORY_SEPARATOR . $path;
        if (! file_exists($absolutePath) && ! mkdir($absolutePath) && ! is_dir($absolutePath)) {
            throw new ClassGeneratorException(sprintf('Directory "%s" was not created', $path));
        }
        $namespace = substr($namespace, 0, -1);
        $factory = new BuilderFactory();
        $class = $factory->class($className);
        $useClasses = [];
        $document = '';
        foreach ($data as $key => $value) {
            if (is_numeric($key) || is_null($value)) {
                continue;
            }
            if ($this->config->getPropertyModel() === PropertyModel::SMALL_HUMP) {
                $propertyName = Utils::stringToHump($key);
            } else {
                $propertyName = Utils::stringToLine($key);
            }
            $type = get_debug_type($value);
            $typeDoc = '';
            if ($value && is_array($value)) {
                $nestClassName = ucfirst(Utils::stringToHump($key));
                $nestClass = "{$namespace}\\{$className}{$this->config->getNestAlias()}\\{$nestClassName}";
                $nestPath = $path . DIRECTORY_SEPARATOR . "{$className}{$this->config->getNestAlias()}";
                if (! is_numeric(array_key_first($value))) {
                    $useClasses[] = $nestClass;
                    $type = $nestClassName;
                    $this->gen($value, $nestPath, $nestClassName);
                } elseif (is_array($first = current($value)) && $first && ! is_numeric(array_key_first($first))) {
                    $useClasses[] = $nestClass;
                    $typeDoc = "{$nestClassName}[]";
                    $this->gen($first, $nestPath, $nestClassName);
                }
            }
            if ($this->config->getDeclareTypeModel() === DeclareTypeModel::INNER) {
                $property = $factory->property($propertyName);
                $typeDoc && $property->setDocComment("/**
                                                                    * @var {$typeDoc} 
                                                                    */");
                $property->setType($type);
                $class->addStmt($property);
            } else {
                ! $typeDoc && $typeDoc = $type;
                $document .= "* @property {$typeDoc} {$propertyName}\n";
            }
        }
        if ($document) {
            $class->setDocComment("/**
                                                {$document}
                                                */");
        }
        // extends
        if ($this->config->getExtendsClass()) {
            [$space, $name] = $this->splitClassName($this->config->getExtendsClass());
            if ($space !== $namespace) {
                $useClasses[] = $this->config->getExtendsClass();
            }
            $class->extend($name);
        }
        // implements
        foreach ($this->config->getImpInterfaces() as $impInterface) {
            [$space, $name] = $this->splitClassName($impInterface);
            if ($space !== $namespace) {
                $useClasses[] = $impInterface;
            }
            $class->implement($name);
        }
        // trait
        foreach ($this->config->getTraitClass() as $traitClass) {
            [$space, $name] = $this->splitClassName($traitClass);
            if ($space !== $namespace) {
                $useClasses[] = $traitClass;
            }
            $class->addStmt($factory->useTrait($name));
        }
        // namespace
        $node = $factory->namespace($namespace);
        foreach ($useClasses as $useClass) {
            $node->addStmt($factory->use($useClass));
        }
        $stmts = [$node->addStmt($class)->getNode()];
        $prettyPrinter = new Standard();
        $code = $prettyPrinter->prettyPrintFile($stmts);
        file_put_contents($file, $code);
        echo "Success,Generate file {$file} \n";
        // php-cs-fixer format
        if (class_exists(Application::class) && $this->config->getCsFixerConfigFile()) {
            $input = new StringInput("fix {$file} --config={$this->config->getCsFixerConfigFile()}");
            $app = new Application();
            $app->doRun($input, new ConsoleOutput());
        }
    }

    public function genFromJson(string $json, string $path, string $className): void
    {
        $this->gen(json_decode($json, true, 512, JSON_THROW_ON_ERROR), $path, $className);
    }

    protected function splitClassName(string $className): array
    {
        $name = strrchr($className, '\\');
        $namespace = strstr($className, $name, true);
        return [$namespace, substr($name, 1)];
    }
}
