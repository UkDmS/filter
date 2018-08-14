<?php
            if(substr_count($link,"apply")>0 && substr_count($link,"prolongatory")>0) {
            foreach($linkArray as $item) {
               if(substr_count($item,"collection-is-")>0) {
                    $out['collection'] = str_replace("collection-is-", '', $item);
               }
               if(substr_count($item,"proizvoditel-is-")>0) {
                    $out['proizvoditel'] = str_replace("proizvoditel-is-", '', $item);
               }
            }

            $arProperties = CIBlockPropertyEnum::GetList(
                Array(  "SORT"=>"ASC",
                        "VALUE"=>"ASC"),
                Array(  "IBLOCK_ID" => 38,
                        "PROPERTY_ID" => 222,
                        "XML_ID" => $out['collection']));
            while ($arProperty = $arProperties->Fetch()) {
                $collectionValue = $arProperty["VALUE"];
            }
            $filter = array();
            $temp = array();
            $temp['name']='Коллекция';
            $temp['value'] = $collectionValue;
            array_push($filter,$temp);
            if (!CModule::IncludeModule('highloadblock'))
                continue;

            $ID = 3;
            $hldata = Bitrix\Highloadblock\HighloadBlockTable::getById($ID)->fetch();
            $hlDataClass = $hldata['NAME'].'Table';
            $result = $hlDataClass::getList(array(
                 'select' => array('UF_XML_ID', 'UF_NAME'),
                 'order' => array('UF_NAME' =>'ASC'),
                 'filter' => array('UF_SORT'=>'100','UF_XML_ID'=>$out['proizvoditel']),
            ));

            $allValuesManufacture = array();

            while($res = $result->fetch()) {
                $allValuesManufacture[] = $res;
            }

            $temp['name']='Производитель';
            $temp['value'] = $allValuesManufacture[0]['UF_NAME'];
            array_push($filter,$temp);

            $string = '';
            foreach($filter as $elem) {
                $string .= $elem['name']." : ".$elem['value']." | ";
            }
            $string = substr($string,0,-2);
            $cp = $this->__component;
            if (is_object($cp)) {
                $cp->arResult["IPROPERTY_VALUES"]["SECTION_META_TITLE"] = "Купить | ".$string;
                $cp->arResult["IPROPERTY_VALUES"]["SECTION_META_DESCRIPTION"] = " ".$string;
            }
        }
?>