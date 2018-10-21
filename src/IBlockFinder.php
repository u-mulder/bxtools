<?php

namespace Um\BxTools;

class IBlockFinder 
{

    public function findByCode($code)
    {
        return \Bitrix\Main\IblockTable::getList([
            'filter' => [
                '=CODE' => $code,
                // SITE_ID ?
            ],
            'select' => ['ID', 'NAME']
        ])->fetch();
    }
}
