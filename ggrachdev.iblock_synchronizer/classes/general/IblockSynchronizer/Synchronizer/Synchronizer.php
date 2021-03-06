<?php

namespace GGrach\IblockSynchronizer\Synchronizer;

use \GGrach\IblockSynchronizer\SyncResult;
use \GGrach\IblockSynchronizer\Exceptions\SearchIblockException;
use \GGrach\IblockSynchronizer\Exceptions\BitrixRedactionException;
use \GGrach\IblockSynchronizer\Contracts\ISynchronizer;
use \GGrach\IblockSynchronizer\Contracts\IParser;
use \Bitrix\Main\Loader;
use \Bitrix\Iblock\Iblock;

class Synchronizer implements ISynchronizer {

    private $fromIblockId;
    private $toIblockId;
    private $arSyncRules;
    private $syncResult;
    private $parser;

    public function __construct(int $fromIblockId, int $toIblockId) {
        if ($fromIblockId <= 0) {
            throw new SearchIblockException('Iblock id ' . $fromIblockId . ' can be above zero ');
        }

        if ($toIblockId <= 0) {
            throw new SearchIblockException('Iblock id ' . $toIblockId . ' can be above zero ');
        }

        if (
            Loader::includeModule('iblock') &&
            Loader::includeModule('sale') &&
            Loader::includeModule('catalog')
        ) {

            $dbFromRes = \CIBlock::GetList([], ['ID' => $fromIblockId]);

            if (!$dbFromRes->GetNext()) {
                throw new SearchIblockException('Not found iblock with id ' . $fromIblockId);
            }

            $dbToRes = \CIBlock::GetList([], ['ID' => $toIblockId]);

            if (!$dbToRes->GetNext()) {
                throw new SearchIblockException('Not found iblock with id ' . $toIblockId);
            }

            $this->fromIblockId = $fromIblockId;
            $this->toIblockId = $toIblockId;
        } else {
            throw new BitrixRedactionException('Modules required for the library to work were not found');
        }
    }

    public function setSyncRules(array $arSyncRules): void {
        $this->arSyncRules = $arSyncRules;
    }

    public function getSyncRules(): array {
        return $this->arSyncRules;
    }

    public function getFromIblockId(): int {
        return $this->fromIblockId;
    }

    public function getToIblockId(): int {
        return $this->toIblockId;
    }

    protected function getArraySelectTo(): array {
        $arSelect = [];
        $arSyncRules = $this->getSyncRules();

        // ?????????????????? ?????????????????? ????????????????
        if (!empty($arSyncRules['SIMILAR_PROPERTIES']['SYSTEM_PROPERTIES'])) {
            $arSelect = \array_merge($arSelect, $arSyncRules['SIMILAR_PROPERTIES']['SYSTEM_PROPERTIES']);
        }

        if (!empty($arSyncRules['SYNC_PROPERTIES']['SYSTEM_PROPERTIES'])) {
            $arSelect = \array_merge($arSelect, $arSyncRules['SYNC_PROPERTIES']['SYSTEM_PROPERTIES']);
        }

        // ?????????????????? ???????????????????????????????? ????????????????
        if (!empty($arSyncRules['SIMILAR_PROPERTIES']['USER_PROPERTIES'])) {
            foreach ($arSyncRules['SIMILAR_PROPERTIES']['USER_PROPERTIES'] as $code) {
                $arSelect[$code . '_'] = $code;
            }
        }
        if (!empty($arSyncRules['SYNC_PROPERTIES']['USER_PROPERTIES'])) {
            foreach ($arSyncRules['SYNC_PROPERTIES']['USER_PROPERTIES'] as $code) {
                $arSelect[$code . '_'] = $code;
            }
        }

        if (!empty($arSelect)) {
            $arSelect[] = 'ID';
            $arSelect = \array_unique($arSelect);
        }

        return $arSelect;
    }

    protected function getArrayFilterTo(): array {
        $arFilter = [];
        $arSyncRules = $this->getSyncRules();

        // ?????????????????? ?????????????????? ????????????????
        if (!empty($arSyncRules['SIMILAR_PROPERTIES']['SYSTEM_PROPERTIES'])) {
            foreach ($arSyncRules['SIMILAR_PROPERTIES']['SYSTEM_PROPERTIES'] as $code) {
                $arFilter['!=' . $code] = false;
            }
        }

        // ?????????????????? ???????????????????????????????? ????????????????
        if (!empty($arSyncRules['SIMILAR_PROPERTIES']['USER_PROPERTIES'])) {
            foreach ($arSyncRules['SIMILAR_PROPERTIES']['USER_PROPERTIES'] as $code) {
                $arFilter['!=' . $code . '.VALUE'] = false;
            }
        }

        return $arFilter;
    }

    protected function getArraySelectFrom(): array {
        $arSelect = [];
        $arSyncRules = $this->getSyncRules();

        // ?????????????????? ?????????????????? ????????????????
        if (!empty($arSyncRules['SIMILAR_PROPERTIES']['SYSTEM_PROPERTIES'])) {
            $arSelect = \array_merge($arSelect, $arSyncRules['SIMILAR_PROPERTIES']['SYSTEM_PROPERTIES']);
        }

        if (!empty($arSyncRules['SYNC_PROPERTIES']['SYSTEM_PROPERTIES'])) {
            $arSelect = \array_merge($arSelect, $arSyncRules['SYNC_PROPERTIES']['SYSTEM_PROPERTIES']);
        }

        // ?????????????????? ???????????????????????????????? ????????????????
        if (!empty($arSyncRules['SIMILAR_PROPERTIES']['USER_PROPERTIES'])) {
            foreach ($arSyncRules['SIMILAR_PROPERTIES']['USER_PROPERTIES'] as $code) {
                $arSelect[$code . '_'] = $code;
            }
        }
        if (!empty($arSyncRules['SYNC_PROPERTIES']['USER_PROPERTIES'])) {
            foreach ($arSyncRules['SYNC_PROPERTIES']['USER_PROPERTIES'] as $code) {
                $arSelect[$code . '_'] = $code;
            }
        }

        if (!empty($arSelect)) {
            $arSelect[] = 'ID';
            $arSelect = \array_unique($arSelect);
        }

        return $arSelect;
    }

    protected function getArrayFilterFrom(): array {
        $arFilter = [];
        $arSyncRules = $this->getSyncRules();

        // ?????????????????? ?????????????????? ????????????????
        if (!empty($arSyncRules['SIMILAR_PROPERTIES']['SYSTEM_PROPERTIES'])) {
            foreach ($arSyncRules['SIMILAR_PROPERTIES']['SYSTEM_PROPERTIES'] as $code) {
                $arFilter['!=' . $code] = false;
            }
        }

        if (!empty($arSyncRules['SYNC_PROPERTIES']['SYSTEM_PROPERTIES'])) {
            foreach ($arSyncRules['SYNC_PROPERTIES']['SYSTEM_PROPERTIES'] as $code) {
                $arFilter['!=' . $code] = false;
            }
        }

        // ?????????????????? ???????????????????????????????? ????????????????
        if (!empty($arSyncRules['SIMILAR_PROPERTIES']['USER_PROPERTIES'])) {
            foreach ($arSyncRules['SIMILAR_PROPERTIES']['USER_PROPERTIES'] as $code) {
                $arFilter['!=' . $code . '.VALUE'] = false;
            }
        }
        if (!empty($arSyncRules['SYNC_PROPERTIES']['USER_PROPERTIES'])) {
            foreach ($arSyncRules['SYNC_PROPERTIES']['USER_PROPERTIES'] as $code) {
                $arFilter['!=' . $code . '.VALUE'] = false;
            }
        }

        return $arFilter;
    }

    /**
     * ?????????? ?????????????? ???????????????? ?? ???????????????? ?????? ??????????????????????????
     * 
     * ???? ???????????? ????????????????
     * key string - id ?????????????? to
     * value array - ?????????? ???????????????? ???????? ???????????????? (????????????, ???????????? ?????? ?????????? ???????? ?????????????? ?????????????????? ????????????????????????)
     * 
     * @param array $elementsFrom
     * @param array $elementsTo
     * @param array $arSyncRules
     * @return array
     */
    protected function getSimilarArrayElements(array $elementsFrom, array $elementsTo): array {

        $arSimilar = [];
        $syncResult = $this->getSyncResult();
        $arSyncRules = $this->getSyncRules();

        if (!empty($elementsFrom) && !empty($elementsTo) && !empty($arSyncRules)) {

            $systemSimilarProperties = !empty($arSyncRules['SIMILAR_PROPERTIES']['SYSTEM_PROPERTIES']) ? $arSyncRules['SIMILAR_PROPERTIES']['SYSTEM_PROPERTIES'] : null;
            $userSimilarProperties = !empty($arSyncRules['SIMILAR_PROPERTIES']['USER_PROPERTIES']) ? $arSyncRules['SIMILAR_PROPERTIES']['USER_PROPERTIES'] : null;

            $systemSyncProperties = !empty($arSyncRules['SYNC_PROPERTIES']['SYSTEM_PROPERTIES']) ? $arSyncRules['SYNC_PROPERTIES']['SYSTEM_PROPERTIES'] : null;
            $userSyncProperties = !empty($arSyncRules['SYNC_PROPERTIES']['USER_PROPERTIES']) ? $arSyncRules['SYNC_PROPERTIES']['USER_PROPERTIES'] : null;
            $otherSyncProperties = !empty($arSyncRules['SYNC_PROPERTIES']['OTHER_PROPERTIES']) ? $arSyncRules['SYNC_PROPERTIES']['OTHER_PROPERTIES'] : null;

            /**
             * @var array<int> id'?? ?????????????????? ?????????????????? from 
             */
            $arIdsSimilarFrom = [];

            /**
             * ?????????????????? ???????????? $arSimilar, ???????? ?????????????? ????????????????
             */
            foreach ($elementsTo as $elementTo) {
                foreach ($elementsFrom as $elementFrom) {
                    $isSimilar = true;

                    // ?????????????????? ?????????????????? ???????????????? ???? ????????????????
                    if ($systemSimilarProperties) {
                        foreach ($systemSimilarProperties as $codeSystemProperty) {
                            if (!empty($elementTo[$codeSystemProperty]) && !empty($elementFrom[$codeSystemProperty])) {
                                if (trim($elementTo[$codeSystemProperty]) != trim($elementFrom[$codeSystemProperty])) {
                                    $isSimilar = false;
                                    break;
                                }
                            } else {
                                $isSimilar = false;
                                break;
                            }
                        }
                    }

                    // ?????????????????? ???????????????????????????????? ???????????????? ???? ????????????????
                    if ($userSimilarProperties && $isSimilar) {
                        foreach ($userSimilarProperties as $codeUserPropertyFrom) {
                            $codeUserPropertyTo = $this->getCodeTo($codeUserPropertyFrom, $arSyncRules);

                            if (
                                !empty($elementFrom[$codeUserPropertyFrom . '_VALUE']) &&
                                !empty($elementTo[$codeUserPropertyTo . '_VALUE'])
                            ) {
                                if (
                                    trim($elementFrom[$codeUserPropertyFrom . '_VALUE']) != trim($elementTo[$codeUserPropertyTo . '_VALUE'])
                                ) {
                                    $isSimilar = false;
                                    break;
                                }
                            } else {
                                $isSimilar = false;
                                break;
                            }
                        }
                    }

                    // ???????? ???????????????? ???????????????? ????????????????, ???? ?????????????????? ?????????????? ?? ???????????? ?????????????? ?? element to
                    if ($isSimilar) {
                        if (!isset($arSimilar[$elementTo['ID']])) {
                            $arSimilar[$elementTo['ID']] = [];
                        }

                        /**
                         * @var array ???????????? ?? ?????????????? ?????? ???????????????????? element to
                         */
                        $arUpdate = [];

                        /**
                         * ?????????????????? ?????????????????? ???????????????? ?? ???????????? ????????????????????
                         */
                        if ($systemSyncProperties) {
                            foreach ($systemSyncProperties as $codeSystemProperty) {
                                $arUpdate[$codeSystemProperty] = $elementFrom[$codeSystemProperty];
                            }
                        }

                        /**
                         * ?????????????????? ???????????????????????????????? ???????????????? ?? ???????????? ????????????????????
                         */
                        if ($userSyncProperties) {
                            foreach ($userSyncProperties as $codeUserPropertyFrom) {
                                $codeUserPropertyTo = $this->getCodeTo($codeUserPropertyFrom, $arSyncRules);

                                $arUpdate[$codeUserPropertyTo] = $elementFrom[$codeUserPropertyFrom . '_VALUE'];
                            }
                        }

                        /**
                         * ?????????????????? ???????????? ???????????????? ?? ???????????? ????????????????????
                         */
                        if ($otherSyncProperties) {
                            if (\in_array('PRICE', $otherSyncProperties)) {
                                $arUpdate['PRICES'] = [];
                            }
                        }

                        $arIdsSimilarFrom[] = $elementFrom['ID'];
                        $syncResult->addSimilarId($elementFrom['ID']);
                        $arSimilar[$elementTo['ID']][$elementFrom['ID']] = $arUpdate;
                    }
                }
            }

            /**
             * ???????????????????? ???????? ???? ?????????????? ???????? ???????????????? ???????? ?????????????????? to
             */
            if (\in_array('PRICE', $otherSyncProperties) && !empty($arIdsSimilarFrom)) {
                $prices = \Bitrix\Catalog\PriceTable::getList(
                        [
                            'filter' => [
                                '=PRODUCT_ID' => \array_unique($arIdsSimilarFrom)
                            ]
                        ]
                    )->fetchAll();

                $arAdaptedPrices = [];

                if (!empty($prices)) {
                    foreach ($prices as $arPrice) {
                        if (!isset($arAdaptedPrices[$arPrice['PRODUCT_ID']])) {
                            $arAdaptedPrices[$arPrice['PRODUCT_ID']] = [];
                        }

                        $arAdaptedPrices[$arPrice['PRODUCT_ID']][$arPrice['CATALOG_GROUP_ID']] = $arPrice;
                    }

                    foreach ($arSimilar as $idTo => &$arDataTo) {
                        foreach ($arDataTo as $idFrom => &$arDataFrom) {
                            $arDataFrom['PRICES'] = \array_key_exists($idFrom, $arAdaptedPrices) ? $arAdaptedPrices[$idFrom] : [];
                        }
                    }
                }
            }
        }

        return $arSimilar;
    }

    /**
     * ???????????????? ???????????????????????? ???????? ???????????????? ???? ?????????????????? from ?? ???????????????? to
     * 
     * @param string $codeFrom
     * @param array $arSyncRules
     * @return string
     */
    protected function getCodeTo(string $codeFrom, array $arSyncRules): string {
        if (!empty($arSyncRules['CONFORMITY'])) {
            $codePropertyTo = str_replace(array_keys($arSyncRules['CONFORMITY']), \array_values($arSyncRules['CONFORMITY']), $codeFrom);
        } else {
            $codePropertyTo = $codeFrom;
        }

        return $codePropertyTo;
    }

    /**
     * ???????????? ????????????????????????????
     * 
     * 1) ???????????????? ?????? ???????????????? ?????????????????? from
     * 2) ???????? ???????????????????????? ?? ?????????????????? to
     * 3) ?????????????? ???????????? ?????? ?????????????????????????? ???? ?????????????????? from
     * 4) ???????????????????????????? ???? from ?? to
     * 
     * @todo ???????????????????? ?????????????? ?????????????????? ??????????
     * 
     * @param SyncResult $syncResult
     * @param array $arSyncRules
     * @return SyncResult
     */
    public function run(): SyncResult {

        $arSyncRules = $this->getSyncRules();
        $syncResult = $this->getSyncResult();
        $syncResult->setFromIblockId($this->getFromIblockId());
        $syncResult->setToIblockId($this->getToIblockId());

        if (!empty($arSyncRules) && $arSyncRules['ERRORS'] === 0) {

            $entityIblockFrom = Iblock::wakeUp($this->getFromIblockId())->getEntityDataClass();
            $entityIblockTo = Iblock::wakeUp($this->getToIblockId())->getEntityDataClass();

            if ($entityIblockFrom && $entityIblockTo) {

                // 1)
                $arSelectFrom = $this->getArraySelectFrom();
                $arFilterFrom = $this->getArrayFilterFrom();

                if (!empty($arSelectFrom) && !empty($arFilterFrom)) {

                    $elementsFrom = $entityIblockFrom::getList([
                            'select' => $arSelectFrom,
                            'filter' => $arFilterFrom
                        ])->fetchAll();

                    if (!empty($elementsFrom)) {

                        //2 
                        $arSelectTo = $this->getArraySelectTo();
                        $arFilterTo = $this->getArrayFilterTo();

                        foreach ($elementsFrom as $element) {

                            if (!empty($arSyncRules['SIMILAR_PROPERTIES']['SYSTEM_PROPERTIES'])) {
                                foreach ($arSyncRules['SIMILAR_PROPERTIES']['SYSTEM_PROPERTIES'] as $code) {
                                    if (!empty($element[$code])) {
                                        if (!isset($arFilterTo['=' . $code])) {
                                            $arFilterTo['=' . $code] = [];
                                        }

                                        $arFilterTo['=' . $code][] = $element[$code];
                                    }
                                }
                            }
                        }

                        if (!empty($arSyncRules['CONFORMITY']) && !empty($arFilterTo)) {

                            $arNewFilterTo = [];

                            foreach ($arFilterTo as $keyFilterTo => $valueFilterTo) {
                                $newKeyFilterTo = $this->getCodeTo($keyFilterTo, $arSyncRules);
                                $arNewFilterTo[$newKeyFilterTo] = $valueFilterTo;
                            }

                            $arFilterTo = $arNewFilterTo;
                        }

                        if (!empty($arSyncRules['CONFORMITY']) && !empty($arSelectTo)) {
                            $arNewSelectTo = [];

                            foreach ($arSelectTo as $keySelectTo => $valueSelectTo) {
                                $newValueSelectTo = $this->getCodeTo($valueSelectTo, $arSyncRules);
                                $arNewSelectTo[$this->getCodeTo($keySelectTo, $arSyncRules)] = $newValueSelectTo;
                            }

                            $arSelectTo = $arNewSelectTo;
                        }

                        $elementsTo = $entityIblockTo::getList([
                                'select' => $arSelectTo,
                                'filter' => $arFilterTo
                            ])->fetchAll();

                        // 2) 3)
                        $arSimilar = $this->getSimilarArrayElements($elementsFrom, $elementsTo);

                        $syncResult->setSynchronizedData($arSimilar);

                        // 4)
                        $this->sync();
                    }
                }
            }
        }

        return $syncResult;
    }

    /**
     * ?????????????????????????? ??????????????
     */
    public function sync() {
        $syncResult = $this->getSyncResult();

        $arSimilar = $this->getSyncResult()->getSynchronizedData();
        if (!empty($arSimilar)) {
            foreach ($arSimilar as $idTo => $arDataFrom) {

                $isSuccessSync = true;

                if (!empty($arDataFrom)) {
                    $arKeys = \array_keys($arDataFrom);

                    $idFrom = $arKeys[0];

                    // ???????????????????????????? ????????
                    if (\array_key_exists('PRICES', $arDataFrom[$idFrom])) {

                        // ???????????? ?????? ???????? ??????, ?????????? ???????? ???? ?????? - ??????????????
                        $arPriceIds = [];

                        $dbPriceType = \CCatalogGroup::GetList();
                        while ($arPriceType = $dbPriceType->Fetch()) {
                            $arPriceIds[] = $arPriceType['ID'];
                        }
                        
                        // ?????????????? ?????? ???????? ?????? ?????????????? ??????
                        foreach ($arPriceIds as $idPrice) {

                            if (!\array_key_exists($idPrice, $arDataFrom[$idFrom]['PRICES'])) {
                                
                                $dbPrice = \Bitrix\Catalog\Model\Price::getList([
                                        "filter" => [
                                            "PRODUCT_ID" => $idTo,
                                            "CATALOG_GROUP_ID" => $idPrice
                                        ]
                                ]);
                                
                                if ($arPriceItem = $dbPrice->fetch()) {
                                    \Bitrix\Catalog\Model\Price::delete($arPriceItem['ID']);
                                }
                            }
                        }

                        foreach ($arDataFrom[$idFrom]['PRICES'] as $priceCode => $priceData) {
                            $arFieldsPrice = [
                                "PRODUCT_ID" => $idTo,
                                "CATALOG_GROUP_ID" => $priceCode,
                                "PRICE" => $priceData['PRICE'],
                                "CURRENCY" => $priceData['CURRENCY'] ? $priceData['CURRENCY'] : 'RUB',
                            ];

                            $dbPrice = \Bitrix\Catalog\Model\Price::getList([
                                    "filter" => [
                                        "PRODUCT_ID" => $idTo,
                                        "CATALOG_GROUP_ID" => $priceCode
                                    ]
                            ]);

                            if ($arPriceItem = $dbPrice->fetch()) {
                                $result = \Bitrix\Catalog\Model\Price::update($arPriceItem["ID"], $arFieldsPrice);

                                if (!$result->isSuccess()) {
                                    $isSuccessSync = false;
                                }
                            } else {
                                $result = \Bitrix\Catalog\Model\Price::add($arFieldsPrice);

                                if (!$result->isSuccess()) {
                                    $isSuccessSync = false;
                                }
                            }
                        }
                    }
                    
                    // @todo ???????????????????????????????? ?????????????????? ?? ???????????????????????????????? ????????????????
                    foreach ($arDataFrom as $idFrom => $values) {

                        foreach ($values as $codePropertyUpdate => $valueProperty) {
                            if ($codePropertyUpdate !== 'PRICES') {
                                if (\is_string($valueProperty)) {
                                    if ($this->getParser->isUserProperty($codePropertyUpdate)) {
                                        \CIBlockElement::SetPropertyValuesEx($idTo, $this->getToIblockId(), [
                                            $codePropertyUpdate => $valueProperty
                                        ]);
                                    } else if ($this->getParser->isSystemProperty($codePropertyUpdate)) {
                                        \CIBlockElement::SetPropertyValuesEx($idTo, $this->getToIblockId(), [
                                            $codePropertyUpdate => $valueProperty
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }

                if ($isSuccessSync) {
                    $syncResult->addSynchronizedId($idTo);
                }
            }
        }
    }

    public function getSyncResult(): SyncResult {
        return $this->syncResult;
    }

    public function setSyncResult(SyncResult $syncResult): void {
        $this->syncResult = $syncResult;
    }

    public function setParser(IParser $parser): void {
        $this->parser = $parser;
    }

    public function getParser(): IParser {
        return $this->parser;
    }

}
