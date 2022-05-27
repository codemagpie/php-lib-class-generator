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

class Config
{
    /**
     * Project directory path.
     */
    protected string $basePath;

    /**
     *  Default inner, outer: Will be in the class attribute comments above statement; inner: Inside the class declaration attributes.
     */
    protected string $declareTypeModel;

    /**
     * Default small_hump, underline: Properties of keys into underline; small_hump: Properties of keys into a small hump.
     */
    protected string $propertyModel;

    /**
     * The generated object will implement this interfaces.
     */
    protected array $impInterfaces = [];

    /**
     * The generated object will extend the class.
     */
    protected string $extendsClass = '';

    /**
     * The generated object will use this traits.
     */
    protected array $traitClass = [];

    /**
     * Nested objects directory suffix.
     */
    protected string $csFixerConfigFile = '';

    /**
     * Default bash path .php-cs-fixer.php, cs-fixer config file.
     */
    protected string $nestAlias = '';

    public function __construct(string $basePath, string $declareTypeModel = DeclareTypeModel::INNER, string $propertyModel = PropertyModel::SMALL_HUMP, string $extendsClass = '', array $impInterfaces = [], array $traitClass = [], string $nestAlias = '', string $csFixerConfigFile = '')
    {
        ! defined('BASE_PATH') && define('BASE_PATH', $basePath);
        $this->declareTypeModel = $declareTypeModel;
        $this->propertyModel = $propertyModel;
        $this->basePath = $basePath;
        $this->impInterfaces = $impInterfaces;
        $this->extendsClass = $extendsClass;
        $this->traitClass = $traitClass;
        $this->nestAlias = $nestAlias;
        if (! $csFixerConfigFile) {
            $csFixerConfigFile = BASE_PATH . DIRECTORY_SEPARATOR . '.php-cs-fixer.php';
            if (file_exists($csFixerConfigFile)) {
                $this->csFixerConfigFile = $csFixerConfigFile;
            }
        }
    }

    public function getDeclareTypeModel(): string
    {
        return $this->declareTypeModel;
    }

    public function getPropertyModel(): string
    {
        return $this->propertyModel;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getImpInterfaces(): array
    {
        return $this->impInterfaces;
    }

    public function getExtendsClass(): ?string
    {
        return $this->extendsClass;
    }

    public function getTraitClass(): array
    {
        return $this->traitClass;
    }

    public function getCsFixerConfigFile(): string
    {
        return $this->csFixerConfigFile;
    }

    public function getNestAlias(): string
    {
        return $this->nestAlias;
    }
}
