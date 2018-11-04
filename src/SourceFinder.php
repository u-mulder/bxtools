<?php

namespace Um\BxTools;

class SourceFinder
{
    /**
     * Пытаемся найти источник данных по коду.
     *
     * В данном случае это поиск битриксового инфоблока по его коду.
     * Дополнительно есть возможность передать id сайта,
     * для которого будет осуществлен поиск инфоблока
     *
     * @param string $code
     * @param string|null $siteId
     * @return mixed
     */
    public function findByCode(string $code, string $siteId = null)
    {
        $code = trim($code);
        if ($code) {
            $filter = [
                '=CODE' => $code,
            ];

            $siteId = null === $siteId ? '' : trim($siteId);
            if (!$siteId && defined('\SITE_ID')) {
                $siteId = \SITE_ID;
            }
            $siteId && $filter['LID'] = $siteId;

            return \Bitrix\Main\IblockTable::getList([
                'filter' => $filter,
                'select' => ['ID', 'NAME']
            ])->fetch();
        }

        throw new \UnexpectedValueException('Iblock code is empty');
    }
}
