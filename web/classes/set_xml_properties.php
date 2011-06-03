    // this version of set_xml_properties() uses an XML parser
    function set_xml_properties($xml) {
        $XMLPARSER = xml_parser_create('UTF-8');
        xml_parser_set_option($XMLPARSER,XML_OPTION_CASE_FOLDING,false);
        xml_parse_into_struct($XMLPARSER,$xml,$vals,$index);
        foreach($vals as $arr) {
            switch($arr[level]) {
            case 1:
                if ($arr[tag] != 'properties') {
                    siteframe_abort(_ERR_BADPROPERTIES);
                }
                break;
            case 2:
                $this->_properties[strtolower($arr[tag])] =
                stripslashes($arr[value]);
                break;
            default:
                siteframe_abort(_ERR_BADPROPERTIES);
            }
        }
        xml_parser_free($XMLPARSER);
    }
