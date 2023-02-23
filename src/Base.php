<?php

namespace Clrkz;

define("GEOGRAPHIC_LEVEL", "geographic_level");
define("NAME", "name");

class Base
{
    static $url = "https://psa.gov.ph";
    static $endpoint = "/classification/psgc";

    static $file_hyperlink_text = "Publication";
    static $file_query = "//a";
    static $file_attribute = "href";

    static $file_sheet_name = "PSGC"; //blank if single sheet

    static $file_save_directory = "publicationFiles";
    static $file_save_directory_output = "output";

    static $column_names = [
        "code" => "Correspondence Code",
        NAME => "Name",
        GEOGRAPHIC_LEVEL => "Geographic Level",
        "old_names" => "Old names",
        "city_class" => "City Class",
        "income_classification" => "Income\nClassification",
        "urban_rural" => "Urban / Rural\n(based on 2020 CPH)",
        "population" => "2020 Population",
    ];


    static $alias = [
        // "City of Makati" => "Makati",
        // "Pasay City" => "Pasay",
    ];

    static $format = ["Reg", "Prov", "City,Mun,SubMun", "Bgy"];

    // Ignore current row only, may not affect the next rows
    static $ignored_geographic_level = ["Dist"];

    // Ignore current row and the next child rows
    static $exclude_geographic_level = ["SGU"];

    static $exclude = [
        "133900000", //NCR, City of Manila, First District (Not a Province) & City of Manila
    ];

    static $ncr_code = "130000000";
}
