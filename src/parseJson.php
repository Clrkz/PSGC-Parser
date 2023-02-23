<?php

namespace Clrkz;


class parseJson
{

    public function fetch()
    {
        $fetch = new Fetch();
        $result = $fetch->fetch();
        if (!$result['code']) {
            json($result);
        }
        return $result['path'];
    }

    public function spreadsheet2Array($file)
    {

        $file_type = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($file_type);
        $spreadsheet = $reader->load($file);
        if (!empty(Base::$file_sheet_name)) {
            $spreadsheet->setActiveSheetIndexByName(Base::$file_sheet_name);
        }
        $data = $spreadsheet->getActiveSheet()->toArray();
        return $data;
    }

    public function generate()
    {
        $file = $this->fetch();
        // $file = "publicationFiles\PSGC-3Q-2022-Publication-Datafile.xlsx";

        $data = $this->spreadsheet2Array($file);

        $file_columns = [];

        $output = [];

        $current_index = $this->initializeIndexes();

        $last_geographic_level = "";

        foreach ($data as $key => $value) {
            if (empty(array_filter($value))) {
                continue;
            }
            if (empty($file_columns)) {
                // Check if all inputted columns are present in spreadsheet file.
                $containsSearch = count(array_intersect(Base::$column_names, $value)) === count(Base::$column_names);
                if ($containsSearch) {
                    $file_columns = $value;
                    continue;
                }
            } else {

                $current_geographic_level = "";

                $current_row = [];
                foreach ($file_columns as $file_column_key => $file_column) {
                    if (in_array($file_column, Base::$column_names)) {
                        $column_name_key = array_search($file_column, Base::$column_names);
                        // $current_row[$column_name_key] = $value[$file_column_key];
                        $current_row[$column_name_key] = isset(Base::$alias[$value[$file_column_key]]) ? Base::$alias[$value[$file_column_key]] : $value[$file_column_key];

                        if ($column_name_key == GEOGRAPHIC_LEVEL) {
                            $current_geographic_level =  $current_row[$column_name_key];
                        }
                    }
                }

                if (empty($current_geographic_level) || in_array($current_geographic_level, Base::$ignored_geographic_level)) {
                    continue;
                }


                $current_gl_format_index = Helpers::getValueIndex($current_geographic_level, Base::$format);
                if (!is_numeric($current_gl_format_index)) {

                    $current_index[Helpers::key(Base::$format[1])] = -1;

                    continue;
                }

                if (!empty($current_row)) {
                    if (in_array($current_row['code'], Base::$exclude)) {
                        continue;
                    }

                    // if (in_array($current_geographic_level, Base::$exclude_geographic_level)) {
                    //     $current_index[Helpers::key($last_geographic_level)] = -1;
                    //     continue;
                    // }

                    $current_index[Helpers::key(Base::$format[$current_gl_format_index])]++;

                    if ($current_gl_format_index == 0) {
                        $current_index[Helpers::key(Base::$format[1])] = -1;
                        $output[] = array_map('trim', $current_row);

                        // Add manila as province 
                        if ($current_row['code'] == Base::$ncr_code) {
                            $current_index[Helpers::key(Base::$format[1])]++;
                            $current_index[Helpers::key(Base::$format[2])] = -1;

                            $custom_row = [];
                            foreach (Base::$column_names as $column_name_key => $column_name_value) {
                                $custom_row[$column_name_key] = "";
                            }

                            $custom_row[NAME] = "Manila";
                            $custom_row[GEOGRAPHIC_LEVEL] = "Prov";

                            $output[$current_index[Helpers::key(Base::$format[0])]][Helpers::key(Base::$format[1])][] = $custom_row;
                        }
                    } else if ($current_gl_format_index == 1) {
                        if (empty($output[$current_index[Helpers::key(Base::$format[0])]])) {
                            continue;
                        }

                        $current_index[Helpers::key(Base::$format[2])] = -1;
                        $output[$current_index[Helpers::key(Base::$format[0])]][Helpers::key(Base::$format[$current_gl_format_index])][] = array_map('trim', $current_row);
                    } else if ($current_gl_format_index == 2) {
                        if (empty($output[$current_index[Helpers::key(Base::$format[0])]][Helpers::key(Base::$format[1])])) {
                            continue;
                        }

                        $current_index[Helpers::key(Base::$format[3])] = -1;
                        $output[$current_index[Helpers::key(Base::$format[0])]][Helpers::key(Base::$format[1])][$current_index[Helpers::key(Base::$format[1])]][Helpers::key(Base::$format[$current_gl_format_index])][] = array_map('trim', $current_row);
                    } else if ($current_gl_format_index == 3) {
                        if (empty($output[$current_index[Helpers::key(Base::$format[0])]][Helpers::key(Base::$format[1])][$current_index[Helpers::key(Base::$format[1])]][Helpers::key(Base::$format[2])])) {
                            continue;
                        }
                        $output[$current_index[Helpers::key(Base::$format[0])]][Helpers::key(Base::$format[1])][$current_index[Helpers::key(Base::$format[1])]][Helpers::key(Base::$format[2])][$current_index[Helpers::key(Base::$format[2])]][Helpers::key(Base::$format[$current_gl_format_index])][] = array_map('trim', $current_row);
                    }
                }
                $last_geographic_level = $current_geographic_level;
            }
        }


        if (empty($file_columns)) {
            json(["code" => 0, "message" => "Column not found."]);
        }

        if (!is_dir(Base::$file_save_directory_output)) {
            mkdir(Base::$file_save_directory_output, 0777, true);
        }

        $basename = pathinfo($file, PATHINFO_FILENAME);
        $full_path = Base::$file_save_directory_output . DIRECTORY_SEPARATOR . sprintf("%s.%s", $basename, "json");
        file_put_contents($full_path, json_encode($output));

        // json($output);
        echo "Done parsing...";
    }


    public function initializeIndexes()
    {
        $array = [];
        foreach (Base::$format as $key => $format) {
            $array[Helpers::key($format)] = -1;
        }
        return $array;
    }

    public static function main()
    {
        $me = new \Clrkz\parseJson();
        $me->generate();
    }
}
