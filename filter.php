<?
 if(substr_count($link,"apply")>0) {
            $re = '/\d+/m';
            $re_d = '/(\d.\d)/';
            foreach($linkArray as $item) {
                if(substr_count($item,"collection-is-")>0) {
                    $out['collection'] = str_replace("collection-is-", '', $item);
                }
                if(substr_count($item,"proizvoditel-is-")>0) {
                    $out['proizvoditel'] = str_replace("proizvoditel-is-", '', $item);
                }
                if(substr_count($item,"material-is-")>0) {
                    $out['material'] = str_replace("material-is-", '', $item);
                }
                if(substr_count($item,"batteries-is-")>0) {
                    $out['batteries'] = str_replace("batteries-is-", '', $item);
                }
                preg_match_all($re, $item, $matches, PREG_SET_ORDER, 0);
                if(substr_count($item,"dl-from-")>0) {
                    if(count($matches)==2) {
                        $length = "от ".$matches[0][0]." см. до ".$matches[1][0]." см. ";
                    } else if(count($matches)==1) {
                        $length = "от ".$matches[0][0]." см. ";
                    }
                } else if(substr_count($item,"dl-to-")>0) {
                    $length = "до ".$matches[0][0]." см. ";
                }
                preg_match_all($re, $item, $price_matches, PREG_SET_ORDER, 0);
                if(substr_count($item,"price-base-from-")>0) {
                    if(count($price_matches)==2) {
                        $price = "от ".$price_matches[0][0]." руб. до ".$price_matches[1][0]." руб. ";
                    } else if(count($price_matches)==1) {
                        $price = "от ".$price_matches[0][0]." руб. ";
                    }
                } else if(substr_count($item,"price-base-to-")>0) {
                    $price = "до ".$price_matches[0][0]." руб. ";
                }
                preg_match_all($re_d, $item, $diameter_matches, PREG_SET_ORDER, 0);
                if(substr_count($item,"diametr-from-")>0) {
                    if(count($diameter_matches)==2) {
                        $diameter = "от ".$diameter_matches[0][0]." см. до ".$diameter_matches[1][0]." см. ";
                    } else if(count($diameter_matches)==1) {
                        $diameter = "от ".$diameter_matches[0][0]." см. ";
                    }
                } else if(substr_count($item,"diametr-to-")>0) {
                    $diameter = "до ".$diameter_matches[0][0]." см. ";
                }
            }
            if(isset($out['batteries']) && $out['batteries']!=='') {
            $out['batteries'] = explode("-or-",$out['batteries']);
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

            if(isset($out['material']) && $out['material']!=='') {
                $arProperties = CIBlockPropertyEnum::GetList(
                    Array("SORT"=>"ASC","VALUE"=>"ASC"),
                    Array("IBLOCK_ID" => 38, "PROPERTY_ID" => 221, "XML_ID" => $out['material']));
                $materialValue = $arProperties->Fetch() ;

                $temp['name']='Материал';
                $temp['value'] = $materialValue["VALUE"];
                array_push($filter,$temp);
            }

            if(isset($out['batteries']) && $out['batteries']!=='') {
                $arProperties = CIBlockPropertyEnum::GetList(
                    Array("SORT"=>"ASC","VALUE"=>"ASC"),
                    Array("IBLOCK_ID" => 38, "PROPERTY_ID" => 219));

                while ($arProperty = $arProperties->Fetch()) {
                    $all[$arProperty["VALUE"]] = $arProperty["XML_ID"];
                }

                $out = array_intersect($all,$out['batteries']);
                $str = '';
                foreach($out as $key => $value){
                    $str .= $key.", ";
                }

                $out['batteries'] = substr($str, 0, -2);


                $temp['name']='Батарейка';
                $temp['value'] = $out['batteries'];
                array_push($filter,$temp);
            }

            if(isset($length)) {
                $temp['name']='Длина ';
                $temp['value'] = $length;
                array_push($filter,$temp);
            }
            if(isset($price)) {
                $temp['name']='Цена ';
                $temp['value'] = $price;
                array_push($filter,$temp);
            }
            if(isset($diameter)) {
                $temp['name']='Диаметр ';
                $temp['value'] = $diameter;
                array_push($filter,$temp);
            }

            $string = '';
            foreach($filter as $elem) {
                $string .= $elem['name']." : ".$elem['value']." | ";
            }
            $string = substr($string,0,-2);

            if(isset($out['proizvoditel']) || isset($out['collection']) || isset($out['material']) || $length || $price || isset($out['batteries']) || $diameter) {
                $string = " | ".$string;
            }

            $IBLOCK_ID = 41;
            $arSelect = Array("ID","NAME");
            $arFilter = Array(
                        "IBLOCK_ID"     =>  $IBLOCK_ID,
                        "ACTIVE_DATE"   =>  "Y",
                        "ACTIVE"        =>  "Y",
            );
            $res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
            $ind = 0;
            $k = array();
            while($ob = $res->GetNextElement()){
                $arFields = $ob->GetFields();
		        $db_props = CIBlockElement::GetProperty($IBLOCK_ID , $arFields['ID'], "sort", "asc", array());
                while($ar_props = $db_props->Fetch()){
                    $k[$ind][$ar_props['CODE']] = $ar_props['VALUE'];
                }
                $ind++;
            }
            foreach($k as $item) {
	            if(in_array($item["url"],$linkArray)) {
		            $substitution = $item["substitution"];
		            continue;
	            }
            }
            $cp = $this->__component;
            if (is_object($cp)) {
                $cp->arResult["IPROPERTY_VALUES"]["SECTION_META_TITLE"] = "Купить ".$substitution." по низкой цене с доставкой".$string;
                $cp->arResult["IPROPERTY_VALUES"]["SECTION_META_DESCRIPTION"] = $substitution." для мужчин в большом ассортименте по привлекательным ценам быстро и недорого! ".$string;
            }
        }