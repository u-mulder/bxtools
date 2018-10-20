<?php

namespace Um\BxTools;

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
    
    public function prepareArguments($arguments)
    {
        $this->sort = $arguments[0];
        $this->filter = $arguments[1];// also with knowing the entity
        $this->group = $arguments[2];
        $this->navParams = $arguments[3];
        $this->select = $arguments[4];
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
