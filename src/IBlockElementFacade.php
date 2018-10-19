<?php

namespace Um\Facade;

class IBlockElementFacade
{

    const METHOD_PREFIX = 'get';
    const COUNT_SUFFIX = 'Count';
    const ACTIVE_SUFFIX = 'Active';
    const COUNT_ACTIVE_SUFFIX = 'CountActive';

    /** @var IBEAccessor[] */
    protected $accessors;

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
        $accessorAlias = self::getAccessorAliases($method);
        self::resolveAccessor($accessorAlias);
        self::prepareAccessorArguments($arguments);

        //return $this->accessor->getList();
    }

    public function getAccessorAliases(): array
    {
        $entity = substr($method, \strlen(static::METHOD_PREFIX));
        // Articles
        $entity = \strtolower($entity);

        // threeVariants:   PopularArticles:
        // 0, populararticles
        // 1. popular_articles
        // 2. popular-articles



        return [];
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

    public function resolveAccessor()
    {
        if (!isset($this->entities[$entity])) {
            // find iblock by $entity (is it code or ID)?
            // for which site ID is this?
        }

        $this->entities[$entity] = [
            'IBLOCK_ID' => 42,
        ];
    }

    public function prepareArguments($arguments)
    {
        $this->sort = $arguments[0];
        $this->filter = $arguments[1];// also with knowing the entity
        $this->group = $arguments[2];
        $this->navParams = $arguments[3];
        $this->select = $arguments[4];
    }
}
