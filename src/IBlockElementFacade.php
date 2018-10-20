<?php

namespace Um\BxTools;

class IBEFacade
{

    const METHOD_PREFIX = 'get';
    const COUNT_SUFFIX = 'Count';
    const ACTIVE_SUFFIX = 'Active';
    const COUNT_ACTIVE_SUFFIX = 'CountActive';
    const REPLACE_PATTERN = '/[A-Z]/';

    /** @var IBEAccessor[] */
    protected static $accessors;
    
    /** @var IBEAccessor */
    protected static $accessor;

    protected function __construct() {}
    protected function __sleep() {}
    protected function __wakeup() {}

    /**
     * Возможные названия методов:
     *  - `get%ENTITY_NAME%` (например, `getArticles`) - получить элементы из сущности `ENTITY_NAME`
     *  - `get%ENTITY_NAME%Active` (например, `getArticlesActive`) - получить АКТИВНЫЕ элементы из сущности `ENTITY_NAME`
     *  - `get%ENTITY_NAME%Count` (например, `getArticlesCount`) - получить число элементов сущности `ENTITY_NAME`
     *  - `get%ENTITY_NAME%CountActive` (например, `getArticlesCountActive`) - получить число АКТИВНЫХ элементов сущности `ENTITY_NAME`
     *
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        if (!self::isAllowedMethodName()) {
            throw new \RuntimeException(sprintf(
                'Method name "%s" is not allowed',
                $method
            ));
        }
        
        $accessorAliases = self::getAccessorAliases($method);
        $alias = self::getAccessorAlias($accessorAliases);

        return self::$accessors[$alias]
            ->prepareArguments()    // TODO
            ->getList();            // TODO
    }
    
    // TODO - phpdoc
    protected static function isAllowedMethodName(string $name): bool
    {
        return \strlen(static::METHOD_PREFIX) < \strlen($name) 
            && strpos($name, static::METHOD_PREFIX) === 0;
    }

    // threeVariants:   PopularArticles:
    // 0, populararticles
    // 1. popular_articles
    // 2. popular-articles
    public function getAccessorAliases(): array
    {
        $aliases = [];

        $alias = \substr($method, \strlen(static::METHOD_PREFIX));
        $alias_lower = \strtolower($alias);
        $aliases[$alias_lower] = 1;

        if ($alias !== $alias_lower) {
            $aliases[self::toSnakeCase($alias)] = 1;
            $aliases[self::toKebabCase($alias)] = 1;
        }

        return array_keys($aliases);
    }

    /**
     * Create new accessor with provided `$alias`.
     * Iblock existence with provided `$iblock_id` is NOT checked.
     * // TODO - add argument `bool $check_existence` and check whether iblock with such id exists (SITE_ID which?)
     *
     * @param $alias
     * @param $iblock_id
     */
    public function registerAccessor(string $alias, int $iblock_id): void
    {
        $this->accessors[$alias] = new IBEAccessor($iblock_id);
    }

    public function getAccessorAlias($possibleAliases)
    {
        $alias = '';

        foreach ($possibleAliases as $value) {
            if (!empty(self::$accessors[$value])) {
                $alias = $value;
                break;
            }
        }
        
        if (!$alias) {
            $alias = self::resolveAccessorFromAliases($possibleAliases);
        }

        return $alias;
    }
    
    
    protected static function resolveAccessorFromAliases($possibleAliases)
    {
        foreach ($possibleAliases as $alias) {
            $iblock = \Bitrix\Main\IblockTable::getList([
                'filter' => [
                    '=CODE' => $alias
                ],
                'select' => ['ID', 'NAME']
            ])->fetch();
            if ($iblock) {
                self::registerAccessor($alias, $iblock['ID']);
                return $alias;
            }
        }
        
        throw new \RuntimeException(sprintf(
            'No iblock found for aliases %s',
            implode(', ', $possibleAliases)
        ));
    }

    // TODO - test
    protected static function toKebabCase(string $source): string
    {
        preg_replace_callback(
            static::REPLACE_PATTERN,
            function ($m) {
                return '-' . strtolower($m[0]);
            },
            $source
        );
    }

    // TODO - test
    protected static function toSnakeCase(string $source): string
    {
        preg_replace_callback(
            static::REPLACE_PATTERN,
            function ($m) {
                return '_' . strtolower($m[0]);
            },
            $source
        );
    }
}
