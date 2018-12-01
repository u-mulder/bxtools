<?php

namespace Um\BxTools;

use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\IblockSiteTable;

class SourceFinder
{
    /**
     * Пытаемся найти источник данных по коду.
     *
     * В данном случае это поиск битриксового инфоблока по его коду.
     * Дополнительно есть возможность передать id сайта, которому
     * принадлежит инфоблок. Если id сайта НЕ передается, но
     * объявлена константа `SITE_ID`, то будет использована она
     *
     * @param string $code
     * @param string|null $siteId
     * @return mixed
     */
    public function findByCode(string $code, string $siteId = null)
    {
        $code = trim($code);
        if ($code) {
            $filter = [];

            $siteId = null === $siteId ? '' : trim($siteId);
            if (!$siteId && \defined('\SITE_ID')) {
                $siteId = \SITE_ID;
            }
            $siteId && $filter['SITE_ID'] = $siteId;
            $noSiteId = empty($filter['SITE_ID']);

            if ($noSiteId) {
                $filter['=CODE'] = $code;

                $iblock = IblockTable::getList([
                    'filter' => $filter,
                    'select' => ['ID', 'NAME'],
                ])->fetch();
            } else {
                $filter['=IBLOCK.CODE'] = $code;

                $iblock = IblockSiteTable::getList([
                    'filter' => $filter,
                    'select' => ['IBLOCK.ID', 'IBLOCK.NAME'],
                ])->fetch();
            }

            return $iblock
                ? ['ID' => $iblock[$noSiteId ? 'ID' : 'IBLOCK_IBLOCK_SITE_IBLOCK_ID']]
                : false;
        }

        throw new \UnexpectedValueException('Iblock code is empty');
    }
}
