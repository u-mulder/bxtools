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

    public function __construct($iblock_id)
    {
        $this->iblock_id = $iblock_id;
    }

    /**
     * Подготавливаем аргументы для передачи в `getList`
     *
     * @param $arguments
     * @param $modfifiers
     * @return IBEAccessor
     */
    public function prepareArguments(array $arguments, array $modifiers): self
    {
        // Modificators (active, count)

        $this->sort = \is_array($arguments[0]) ? $arguments[0] : [];

        $this->filter = \array_merge(
            ['IBLOCK_ID' => $this->iblock_id],
            $arguments[1]
        );
        /*if (!empty($modifiers['ACTIVE'])) {
            $this->filter['ACTIVE'] = 'Y';
        }*/

        if (!empty($modifiers['COUNT'])) {
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
