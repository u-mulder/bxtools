<?php

namespace Um\Facade;

class IBEAccessor
{

    protected $iblock_id = 0;
    protected $sort;
    protected $filter;
    protected $grouping;
    protected $navParams;
    protected $selectFields;

    public function __construct($iblock_id)
    {
        $this->iblock_id = $iblock_id;
    }

    /**
     * Function that performs selecting required data from iblock
     *
     * @return mixed
     */
    protected function getList()
    {
        /*return \CIblockElement::GetList(
            $this->getSort(),
            $this->getFilter(),
            $this->getGroup(),
            $this->getNavParams(),
            $this->getSelectFields()
        );*/


        return 'zzz';
    }

    /*protected function getSort(): array
    {
        return [];
    }

    protected function getFilter(): array
    {
        return [];
    }

    protected function getGroup()
    {
        return [];
    }

    protected function getNavParams()
    {
        return [];
    }

    protected function getSelectFields()
    {
        return [];
    }*/
}
