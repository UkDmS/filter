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
                        $filter = array();
                $temp = array();
            if(isset($out['collection']) && $out['collection']!=='') {
                $arProperties = CIBlockPropertyEnum::GetList(
                    Array("SORT"=>"ASC","VALUE"=>"ASC"),
                    Array("IBLOCK_ID" => 38, "PROPERTY_ID" => 222, "XML_ID" => $out['collection']));
                $collectionValue = $arProperties->Fetch() ;

                $temp['name']='Коллекция';
                $temp['value'] = $collectionValue["VALUE"];
                array_push($filter,$temp);
            }

            if(isset($out['proizvoditel']) && $out['proizvoditel']!=='') {
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

            $allValuesManufacture = $result->fetch();

            $temp['name']='Производитель';
            $temp['value'] = $allValuesManufacture['UF_NAME'];
            array_push($filter,$temp);
            }
            $string = '';
            foreach($filter as $elem) {
                $string .= $elem['name']." : ".$elem['value']." | ";
            }
            $string = substr($string,0,-2);
            if(isset($out['proizvoditel']) || isset($out['collection'])) {
                $string = " | ".$string;
            }
            $cp = $this->__component;
            if (is_object($cp)) {
                $cp->arResult["IPROPERTY_VALUES"]["SECTION_META_TITLE"] = "Купить".$string;
                $cp->arResult["IPROPERTY_VALUES"]["SECTION_META_DESCRIPTION"] = "! ".$string;
            }
        }
?>