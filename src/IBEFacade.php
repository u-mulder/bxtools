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
    protected static $accessors = [];
    
    /** @var SourceFinder */
    protected static $source_finder;

    private function __construct() {}
    private function __sleep() {}
    private function __wakeup() {}

    /**
     * Методы получения записей начинаются с префикса `get` (`getArticles`, `getNews` и т.д)
     *
     * Остальные названия методов не рассматриваются
     * и выбрасывается `UnexpectedValueException`
     *
     * Возможные названия методов:
     *  - `get%ENTITY_NAME%` (например, `getArticles`) - получить элементы из сущности `ENTITY_NAME`
     *  - `get%ENTITY_NAME%Active` (например, `getArticlesActive`) - получить АКТИВНЫЕ элементы из сущности `ENTITY_NAME`
     *  - `get%ENTITY_NAME%Count` (например, `getArticlesCount`) - получить число элементов сущности `ENTITY_NAME`
     *  - `get%ENTITY_NAME%CountActive` (например, `getArticlesCountActive`) - получить число АКТИВНЫХ элементов сущности `ENTITY_NAME`
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     *
     * @throws \UnexpectedValueException
     */
    public static function __callStatic($method, $arguments)
    {
        if (!self::isAllowedMethodName($method)) {
            throw new \UnexpectedValueException(sprintf(
                'Method name "%s" is not allowed',
                $method
            ));
        }

        $modifiers = self::extractModifiers($method);

        $accessorAliases = self::getAccessorAliases($method);
        $alias = self::getExistingAlias($accessorAliases);

        return self::$accessors[$alias]
            ->prepareArguments($arguments, $modifiers)
            ->getList();
    }

    /**
     * Проверяем что название метода содержит в
     * начале  названия строку `METHOD_PREFIX`
     * и после `METHOD_PREFIX` есть еще символы.
     *
     * @param string $method_name
     *
     * @return bool
     */
    protected static function isAllowedMethodName(string $method_name): bool
    {
        // TODO 2 - нет модификаторов active/count/countactive!
        return \strlen(static::METHOD_PREFIX) < \strlen($method_name)
            && strpos($method_name, static::METHOD_PREFIX) === 0;
    }

    protected function extractModifiers(string $method_name)
    {
        // TODO

    }


    /**
     * Получаем набор возможных алиасов, по которым можно найти аксессор.
     * Из названия метода вида `getPopularArticles` получаем исходный
     * алиас - `PopularArticles`.
     *
     * Из него получаем четыре варианта для поиска аксессора
     *  - `populararticles`
     *  - `popular_articles` (snake case)
     *  - `popular-articles` (kebab case)
     *  - `PopularArticles`
     *
     * @param $method_name
     *
     * @return array
     */
    protected static function getAccessorAliases(string $method_name): array
    {
        $aliases = [];

        $alias = \substr($method_name, \strlen(static::METHOD_PREFIX));
        $aliases[$alias] = 1;

        $alias = \strtolower($alias[0]) . \substr($alias, 1);
        $alias_lower = \strtolower($alias);
        $aliases[$alias_lower] = 1;

        if ($alias !== $alias_lower) {
            $aliases[self::toSnakeCase($alias)] = 1;
            $aliases[self::toKebabCase($alias)] = 1;
        }

        return array_keys($aliases);
    }

    /**
     * Находим алиас, под которым зарегистрирован доступный аксессор.
     *
     * Если среди уже зарегистрированных алиасов нет требуемого, то
     * пытаемся зарегистрировать новый аксессор и вернуть его алиас.
     *
     * @param array $possibleAliases
     *
     * @return string
     */
    protected static function getExistingAlias(array $possibleAliases): string
    {
        $alias = '';

        foreach ($possibleAliases as $possibleAlias) {
            if (!empty(self::$accessors[$possibleAlias])) {
                $alias = $possibleAlias;
                break;
            }
        }
        
        if (!$alias) {
            $alias = self::resolveAccessorFromAliases($possibleAliases);
        }

        return $alias;
    }

    /**
     * Регистрируем новый аксессор под указанным алиасом.
     * Проверка уществования источника данных с указанным id НЕ производится.
     * // TODO 1 - add argument `bool $check_existence` and check whether iblock with such id exists (SITE_ID which?)
     *
     * @param $alias
     * @param $source_id
     *
     * @throws \UnexpectedValueException
     */
    public static function registerAccessor(string $alias, int $source_id): void
    {
        $alias = trim($alias);
        if ($alias) {
            self::$accessors[trim($alias)] = new IBEAccessor($source_id);
        } else {
            throw new \UnexpectedValueException('Accessor alias cannot be empty');
        }
    }

    /**
     * Пытаемся найти источник данных среди набора алиасов.
     * Если источник найден - регистрируем акссессор и возвращаем его алиас.
     *
     * @param array $possibleAliases
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function resolveAccessorFromAliases(array $possibleAliases): string
    {
        if (!self::$source_finder) {
            self::$source_finder = new SourceFinder();
        }
        
        foreach ($possibleAliases as $alias) {
            $source = self::$source_finder->findByCode($alias);
            if ($source) {
                self::registerAccessor($alias, $source['ID']);
                return $alias;
            }
        }
        
        throw new \RuntimeException(sprintf(
            'No source found for aliases: "%s"',
            implode('", "', $possibleAliases)
        ));
    }

    /**
     * @param string $source
     *
     * @return string
     */
    protected static function toKebabCase(string $source): string
    {
        return preg_replace_callback(
            static::REPLACE_PATTERN,
            function ($m) {
                return '-' . strtolower($m[0]);
            },
            $source
        );
    }

    /**
     * @param string $source
     *
     * @return string
     */
    protected static function toSnakeCase(string $source): string
    {
        return preg_replace_callback(
            static::REPLACE_PATTERN,
            function ($m) {
                return '_' . strtolower($m[0]);
            },
            $source
        );
    }
}
