<?php

namespace Um\BxTools;

class IBEAccessor
{

    protected $iblock_id = 0;
    protected $sort;
    protected $filter;
    protected $grouping = false;
    protected $navParams = false;
    protected $selectFields;

    /**
     * IBEAccessor constructor.
     *
     * @param $iblock_id
     */
    public function __construct($iblock_id)
    {
        $this->iblock_id = $iblock_id;
    }

    /**
     * Подготавливаем аргументы для передачи в `getList`
     *
     * @param array $arguments
     * @param array $modifiers
     *
     * @return IBEAccessor
     */
    public function prepareArguments(array $arguments, array $modifiers): self
    {
        $this->sort = \is_array($arguments[0]) ? $arguments[0] : [];

        $this->filter = \array_merge(
            ['IBLOCK_ID' => $this->iblock_id],
            $arguments[1]
        );
        if (!empty($modifiers[IBEFacade::ACTIVE_PREFIX])) {
            $this->filter['ACTIVE'] = 'Y';
        }

        if (!empty($modifiers[IBEFacade::COUNT_SUFFIX])) {
            $this->grouping = [];
        } else {
            $this->grouping = \is_array($arguments[2]) ? $arguments[2] : false;
        }

        $this->navParams = \is_array($arguments[3]) ? $arguments[3] : false;

        $this->selectFields = \is_array($arguments[4]) ? $arguments[4] : [];

        return $this;
    }

    /**
     * Родными методами битрикса возвращаем нужные данные
     *
     * @return mixed
     */
    public function getList()
    {
        return \CIBlockElement::GetList(
            $this->sort,
            $this->filter,
            $this->grouping,
            $this->navParams,
            $this->selectFields
        );
    }
}
