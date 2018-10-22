<?php

namespace Um\BxTools;

class SourceFinder
{
    /**
     * Пытаемся найти источник данных по коду.
     *
     * В данном случае это поиск битриксового инфоблока по его коду.
     *
     * @param $code
     * @return mixed
     */
    public function findByCode($code)
    {
        return \Bitrix\Main\IblockTable::getList([
            'filter' => [
                '=CODE' => $code,
                // SITE_ID ?    // TODO 1
            ],
            'select' => ['ID', 'NAME']
        ])->fetch();
    }
}
