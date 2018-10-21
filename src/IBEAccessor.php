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
     * Function that performs selecting required data from iblock
     *
     * @return mixed
     */
    protected function getList()
    {
        return \CIBlockElement::GetList(
            $this->sort,
            $this->filter,
            $this->grouping,
            $this->navParams,
            $this->selectFields
        );
    }
    
    public function prepareArguments($arguments)
    {
        $this->sort = is_array($arguments[0]) ? $arguments[0] : [];
        
        $this->filter = array_merge(
            ['IBLOCK_ID' => $this->iblock_id],
            $arguments[1]
        );

        $this->group = is_array($arguments[2]) ? $arguments[2] : false;

        $this->navParams = is_array($arguments[3]) ? $arguments[3] : false;

        $this->select = $arguments[4];
    }
}
