<?php

namespace Um\BxTools;

class IBEFacade
{

    const METHOD_PREFIX = 'get';
    const ACTIVE_PREFIX = 'active';
    const COUNT_SUFFIX = 'count';
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
     *  - `getActive%ENTITY_NAME%` (например, `getActiveArticles`) - получить АКТИВНЫЕ элементы из сущности `ENTITY_NAME`
     *  - `get%ENTITY_NAME%Count` (например, `getArticlesCount`) - получить число элементов сущности `ENTITY_NAME`
     *  - `getActive%ENTITY_NAME%Count` (например, `getActiveArticlesCount`) - получить число АКТИВНЫХ элементов сущности `ENTITY_NAME`
     *
     * Так как названия методов нечувствительны к регистру,
     * то `getarticles` или `getactivearticles` также
     * являются обрабатываемыми названиями методов.
     * Однако, при таком вызове метода, парсер лишен возможности
     * использовать `camel_case` и `kebab-case` названия сущности.
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        $methodData = self::parseMethodName($method);

        $accessorAliases = self::getAccessorAliases($methodData['alias']);
        $alias = self::getExistingAlias($accessorAliases);

        return self::$accessors[$alias]
            ->prepareArguments($arguments, $methodData['modifiers'])
            ->getList();
    }

    /**
     * Парсим название метода, получаем алиас сущности и дополнительные модификаторы
     *
     * @param string $methodName
     * @return array
     * @internal param string $method_name
     *
     * @throws \UnexpectedValueException
     */
    protected static function parseMethodName(string $methodName): array
    {
        $result = [
            'alias' => '',
            'modifiers' => [],
        ];

        $originalMethodName = $methodName;

        $methodPrefixLen = \strlen(static::METHOD_PREFIX);
        if (\strlen($methodName) <= $methodPrefixLen) {
            throw new \UnexpectedValueException(sprintf(
                'Method name "%s" is too short',
                $methodName
            ));
        }

        if (strpos($methodName, static::METHOD_PREFIX) !== 0) {
            throw new \UnexpectedValueException(sprintf(
                'Method name should be prefixed with "%s", currently "%s"',
                static::METHOD_PREFIX,
                $methodName
            ));
        }

        $methodName = \substr($methodName, $methodPrefixLen);

        $prefixLen = \strlen(static::ACTIVE_PREFIX);
        if (0 === stripos($methodName, static::ACTIVE_PREFIX)) {
            $methodName = \substr($methodName, $prefixLen);
            $result['modifiers'][static::ACTIVE_PREFIX] = 1;
        }

        if (!$methodName) {
            throw new \UnexpectedValueException(sprintf(
                'Method name "%s" becomes empty after removing "%s" prefix',
                $originalMethodName,
                static::ACTIVE_PREFIX
            ));
        }

        $suffixLen = \strlen(static::COUNT_SUFFIX);
        if (\strtolower(\substr($methodName, -$suffixLen)) === static::COUNT_SUFFIX) {
            $methodName = \substr($methodName, 0, -$suffixLen);
            $result['modifiers'][static::COUNT_SUFFIX] = 1;
        }

        if (!$methodName) {
            throw new \UnexpectedValueException(sprintf(
                'Method name "%s" becomes empty after removing "%s" suffix',
                $originalMethodName,
                static::COUNT_SUFFIX
            ));
        }

        $result['alias'] = $methodName;

        return $result;
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
     * Из названия метода вида `getpopulararticles` выйдет
     * меньше вариантов алиасов, так что именуйте правильно.
     *
     * @param string $alias
     * @return array
     * @internal param $method_name
     *
     */
    protected static function getAccessorAliases(string $alias): array
    {
        $aliases = [];

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
    public static function registerAccessor(string $alias, int $source_id)
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
